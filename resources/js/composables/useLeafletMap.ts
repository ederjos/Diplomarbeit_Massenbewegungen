import type { Point } from '@/@types/measurement';
import { DEFAULT_WMS_OPTIONS, WMS_LAYERS } from '@/config/mapLayers';
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
} from '@/config/mapConstants';
import L from 'leaflet';
import { onUnmounted, ref, type Ref } from 'vue';

/**
 * Clause Opus 4.6, 2026-02-14
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
        // Set to Bregenz as default view, will be changed to actual points later
        const leafletMap = L.map(container).setView(DEFAULT_MAP_CENTER, DEFAULT_ZOOM_LEVEL);
        map.value = leafletMap;
        markersLayer = new L.LayerGroup();
        markersLayer.addTo(leafletMap);

        const mainMap = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: MAX_MAP_ZOOM,
            // default tile size (256)
            minZoom: MIN_MAP_ZOOM,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            // show only this initially
        }).addTo(leafletMap);

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
        if (!map.value || !markersLayer) return;

        // Clear existing layers before redrawing
        markersLayer.clearLayers();

        // Get a scale from 1 to 100000, set it to 1 if invalid
        const scale = vectorScale.value;

        points.value.forEach((point) => {
            const latlngs = computeLatLngs(point, scale);
            if (!latlngs.length) return;

            const lastCoord = latlngs[latlngs.length - 1];
            addPolylines(latlngs, point.id);
            addCircleMarker(lastCoord, point.id);
            addTextMarker(lastCoord, point.id, point.name);
        });
    }

    function computeLatLngs(point: Point, scale: number): [number, number][] {
        // If both reference and comparison are selected, calculate scaled vector
        if (referenceId.value && selectedComparison.value && !isGaitLine.value) {
            // Vector mode
            const refM = point.measurementValues.find((m) => m.measurementId === referenceId.value);
            const compM = point.measurementValues.find((m) => m.measurementId === selectedComparison.value);

            /**
             * GEODETIC NOTE: Vectors are calculated via linear interpolation in WGS84 space.
             * While this introduces a slight scale distortion (Mercator) and ignores
             * the convergence of meridians, it is numerically stable and visually
             * consistent for local-scale monitoring (e.g., within 10-20km).
             */
            if (!refM || !compM) {
                return [];
            }

            // vectors
            const p1 = L.latLng(refM.lat, refM.lon);
            const p2 = L.latLng(compM.lat, compM.lon);
            // Note: Leaflet's latLng uses (lat, lng) but dx is in x direction (longitude)
            const dLon = p2.lng - p1.lng;
            // and dy is in y direction (latitude)
            const dLat = p2.lat - p1.lat;

            const dLonScaled = dLon * scale;
            const dLatScaled = dLat * scale;

            // Calculate new position
            const newLon = refM.lon + dLonScaled;
            const newLat = refM.lat + dLatScaled;

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
            const dLon = m.lon - origin.lon;
            const dLat = m.lat - origin.lat;

            const dLonScaled = dLon * scale;
            const dLatScaled = dLat * scale;

            const newLon = origin.lon + dLonScaled;
            const newLat = origin.lat + dLatScaled;

            return [newLat, newLon];
        });
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
