import L from 'leaflet';
import { onUnmounted, ref } from 'vue';
import type { Ref } from 'vue';
import type { Point } from '@/@types/measurement';
import {
    DEFAULT_MAP_CENTER,
    DEFAULT_ZOOM_LEVEL,
    MAP_BOUNDS_PADDING,
    MARKER_BORDER_COLOR,
    MARKER_BORDER_OPACITY,
    MARKER_BORDER_WEIGHT,
    MARKER_CIRCLE_RADIUS,
    MARKER_FALLBACK_COLOR,
    MARKER_FILL_OPACITY,
    MAX_MAP_ZOOM,
    MIN_MAP_ZOOM,
    POINT_FOCUS_ZOOM_LEVEL,
    POLYLINE_FRAME_COLOR,
    POLYLINE_FRAME_WEIGHT,
    POLYLINE_MAIN_COLOR,
    POLYLINE_MAIN_WEIGHT,
    PROJECTION_LINE_COLOR,
} from '@/config/mapConstants';
import { DEFAULT_WMS_OPTIONS, WMS_LAYERS } from '@/config/mapLayers';

/**
 * Claude Opus 4.6, 2026-02-14
 * "[...] Please strcture a possible composable to extract the leaflet logic from the vue component. [...]"
 */

export function useLeafletMap(
    points: Ref<Point[]>,
    pointColors: Ref<Record<number, string>>,
    referenceId: Ref<number | null>,
    selectedComparison: Ref<number | null>,
    vectorScale: Ref<number>,
    isGaitLine: Ref<boolean>,
    onPointClick: (pointId: number) => void,
) {
    const map = ref<L.Map | null>(null);
    // Make it initially null, to be able to rerender the map; contains the "arrows" and markers
    let markersLayer: L.LayerGroup | null = null;

    function initMap(container: HTMLElement) {
        const leafletMap = L.map(container).setView(DEFAULT_MAP_CENTER, DEFAULT_ZOOM_LEVEL);
        map.value = leafletMap;
        markersLayer = new L.LayerGroup();
        markersLayer.addTo(leafletMap);

        const mainMap = L.tileLayer(
            'https://mapsneu.wien.gv.at/basemap/geolandbasemap/normal/google3857/{z}/{y}/{x}.png',
            {
                maxZoom: MAX_MAP_ZOOM,
                // default tile size (256)
                minZoom: MIN_MAP_ZOOM,
                attribution: '&copy; Grundkarte: <a href="https://basemap.at">basemap.at</a>',
                // show only this initially
            },
        ).addTo(leafletMap);

        const baseLayers: Record<string, L.TileLayer> = { 'Standard Karte': mainMap };

        // Add VOGIS layers
        for (const wmsLayerData of WMS_LAYERS) {
            // Create WMS layer
            const layer = L.tileLayer.wms(wmsLayerData.url, {
                ...DEFAULT_WMS_OPTIONS,
                layers: wmsLayerData.layers,
            });
            baseLayers[wmsLayerData.label] = layer;
        }

        L.control.layers(baseLayers).addTo(leafletMap);
        // Add scale control
        L.control.scale().addTo(leafletMap);
    }

    function fitBounds() {
        if (!map.value) {
            return;
        }

        const allCoords: [number, number][] = [];
        points.value.forEach((p) => p.measurementValues.forEach((m) => allCoords.push([m.lat, m.lon])));

        if (allCoords.length) {
            map.value.fitBounds(allCoords, { padding: MAP_BOUNDS_PADDING });
        }
    }

    function zoomToPoint(pointId: number) {
        // only zoom to selected point
        const point = points.value.find((p) => p.id === pointId);

        if (!point || !map.value) {
            return;
        }

        // First measurement value
        const m =
            point.measurementValues.find((m) => m.measurementId === referenceId.value) || point.measurementValues[0];

        if (m) {
            map.value.setView([m.lat, m.lon], POINT_FOCUS_ZOOM_LEVEL);
        }
    }

    function invalidateMap() {
        /**
         * Gemini 3 Pro, 2025-12-30
         * "If the table is hidden, the leaflet map doesn't render correctly. fix this by telling leaflet when the table is mounted or unmounted"
         */

        /**
         * Forces the Leaflet map to recalculate its container size and redraw after a 100ms delay.
         * **invalidateSize**: A Leaflet method that checks if the map container's dimensions have changed and updates the map accordingly. This is necessary because Leaflet caches the container size for performance; if the container resizes (or becomes visible) without this call, the map may render incorrectly (e.g., missing tiles or wrong center).
         */
        map.value?.invalidateSize();
    }

    function drawMap() {
        if (!map.value || !markersLayer) {
            return;
        }

        // Clear existing layers before redrawing
        markersLayer.clearLayers();

        // Get a scale from 1 to 100000, set to 1 if invalid
        const scale = vectorScale.value;

        points.value.forEach((point) => {
            const latlngs = computeLatLngs(point, scale);

            if (!latlngs.length) {
                return;
            }

            const firstCoord = latlngs[0];

            // First, draw projection line if requested
            if (isGaitLine.value && point.axis) {
                addProjectionLine(point);
            }

            addPolylines(latlngs, point.id);
            addCircleMarker(firstCoord, point.id);
            addTextMarker(firstCoord, point.id, point.name);
        });
    }

    function scaleDisplacement(
        originLat: number,
        originLon: number,
        targetLat: number,
        targetLon: number,
        scale: number,
    ): [number, number] {
        /**
         * Based on the sinusoidal projection.
         * Longitudinal distances are scaled by cos(latitude)
         * to compensate for converging meridians.
         */
        /**
         * GPT-5.3, 2026-03-16
         * "I applied the formula of the sinusoidal projection to make the scaled positions more precise. However, there is still a bug. Please fix it."
         */
        const targetLatRad = (targetLat * Math.PI) / 180;

        // Convert longitudinal delta to a local east-west component before scaling.
        const dEast = (targetLon - originLon) * Math.cos(targetLatRad);
        const dNorth = targetLat - originLat;

        // Scale the vector in projected space
        const newEast = dEast * scale;
        const newNorth = dNorth * scale;

        // New latitude after scaling
        const newLat = originLat + newNorth;
        const newLatRad = (newLat * Math.PI) / 180;

        // Inverse projection (avoid division by 0)
        const newLon = originLon + newEast / Math.max(Math.cos(newLatRad), Number.MIN_VALUE);

        return [newLat, newLon];
    }

    function computeLatLngs(point: Point, scale: number): [number, number][] {
        // If both reference and comparison are selected, calculate scaled vector
        if (referenceId.value && selectedComparison.value && !isGaitLine.value) {
            // Vector mode
            const refM = point.measurementValues.find((m) => m.measurementId === referenceId.value);
            const compM = point.measurementValues.find((m) => m.measurementId === selectedComparison.value);

            if (!refM || !compM) {
                return [];
            }

            const [newLat, newLon] = scaleDisplacement(refM.lat, refM.lon, compM.lat, compM.lon, scale);

            return [
                [refM.lat, refM.lon],
                [newLat, newLon],
            ];
        }

        // Gait line mode (Ganglinie): all measurements connected chronologically
        const measurements = [...point.measurementValues];

        if (measurements.length === 0) {
            return [];
        }

        const origin = measurements[0];

        return measurements.map((m) => {
            const [newLat, newLon] = scaleDisplacement(origin.lat, origin.lon, m.lat, m.lon, scale);

            return [newLat, newLon];
        });
    }

    function addProjectionLine(point: Point) {
        if (!point.axis || !markersLayer) {
            return;
        }

        const scale = vectorScale.value;
        const axis = point.axis;
        const targetLat = axis.startLat + axis.vectorLat;
        const targetLon = axis.startLon + axis.vectorLon;
        const [endLat, endLon] = scaleDisplacement(axis.startLat, axis.startLon, targetLat, targetLon, scale);

        // Draw projection axis line
        const projectionLine = L.polyline(
            [
                // from
                [axis.startLat, axis.startLon],
                // to
                [endLat, endLon],
            ],
            {
                color: PROJECTION_LINE_COLOR,
                weight: 2,
                opacity: 0.7,
            },
        );

        projectionLine.on('click', () => onPointClick(point.id));
        markersLayer.addLayer(projectionLine);
    }

    function addPolylines(latlngs: [number, number][], pointId: number) {
        if (!markersLayer) {
            return;
        }

        // Polyline with white outline for contrast
        const framePolyline = L.polyline(latlngs, { color: POLYLINE_FRAME_COLOR, weight: POLYLINE_FRAME_WEIGHT });
        const currPolyline = L.polyline(latlngs, { color: POLYLINE_MAIN_COLOR, weight: POLYLINE_MAIN_WEIGHT });
        currPolyline.on('click', () => onPointClick(pointId));
        framePolyline.on('click', () => onPointClick(pointId));
        markersLayer.addLayer(framePolyline);
        markersLayer.addLayer(currPolyline);
    }

    function addCircleMarker(lastCoord: [number, number], pointId: number) {
        if (!markersLayer) {
            return;
        }

        // Draw small circle markers for the LAST measurement
        const marker = L.circleMarker(lastCoord, {
            radius: MARKER_CIRCLE_RADIUS,
            fillColor: pointColors.value[pointId] || MARKER_FALLBACK_COLOR,
            color: MARKER_BORDER_COLOR,
            weight: MARKER_BORDER_WEIGHT,
            opacity: MARKER_BORDER_OPACITY,
            fillOpacity: MARKER_FILL_OPACITY,
        });

        marker.on('click', () => onPointClick(pointId));
        markersLayer.addLayer(marker);
    }

    function addTextMarker(lastCoord: [number, number], pointId: number, pointName: string) {
        if (!markersLayer) {
            return;
        }

        // Text label next to the "arrowhead"
        const textMarker = L.marker(lastCoord, {
            icon: L.divIcon({
                // Set class for CSS styling
                className: 'text-labels text-black text-xs font-bold',
                html: `<span style="text-shadow: 1px 1px 0px white, -1px 1px 0px white, 1px -1px 0px white, -1px -1px 0px white;font-size:110%;">${pointName}</span>`,
                iconSize: [0, 0],
                iconAnchor: [10, -10],
            }),
        });
        textMarker.on('click', () => onPointClick(pointId));
        markersLayer.addLayer(textMarker);
    }

    onUnmounted(() => {
        /**
         * GPT-5.1, 2026-02-14
         * "Rate this composable for my bachelor work. Can you find any issues?"
         */
        if (map.value) {
            markersLayer?.clearLayers();
            // Remove event listeners
            map.value.off();
            // Destroy the map
            map.value.remove();
        }
    });

    return { initMap, fitBounds, zoomToPoint, invalidateMap, drawMap };
}
