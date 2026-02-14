<script setup lang="ts">
import { Measurement, Point, PointDisplacement } from '@/@types/measurement';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import DisplacementTable from './DisplacementTable.vue';
import MapToolbar from './MapToolbar.vue';

/** -- Use PHP Configs in Vue --
 * import { usePage } from '@inertiajs/vue3';
 * const spatial = usePage().props.spatial as any;
 * If we ever need the srid from the backend
 * Requires "'spatial' => config('spatial')," in Middleware/HandleInertiaRequests.php
 * Source: https://inertiajs.com/docs/v2/data-props/shared-data
 */

const props = defineProps<{
    points: Point[];
    // like hash map
    pointColors: Record<number, string>;
    measurements: Measurement[];
    /** Fixed reference measurement ID (Bezugsepoche, set per project by Admin/Editor) */
    referenceId: number | null;
    /** Selected comparison measurement ID */
    comparisonId: number | null;
    /** Backend-computed displacement values. Key: point id */
    displacements: Record<number, PointDisplacement>;
}>();

/**
 * Gemini 3 Pro, 2025-12-02
 * "this component works greatly. now, we want to rearrange the design though and add a new table. in this table there should be a col for point name, one for delta pos (x and y) and one for delta height (z). keep in mind that the values in the Measurement interface of x,y,z are in the epsg 31254"
 */

const mapContainer = ref<HTMLDivElement | null>(null);
const map = ref<L.Map | null>(null);
// former selectedMeasurement
const selectedComparison = ref<number | null>(props.comparisonId);
const vectorScale = ref<number>(100);
const isGaitLine = ref<boolean>(false);
const markersLayer = new L.LayerGroup();
const selectedPointId = ref<number | null>(null);

// If two points are clicked quickly after one another, the highlight animation should restart
let highlightTimeout: number | null = null;

/**
 * Claude Opus 4.6, 2026-02-11
 * "[...] Then, apply the projection changes to the LeafletComponent file, so that the user can select the display mode for the displacements (2D, projection, 3D) and the map updates accordingly."
 */

// Trigger Inertia visit when comparison epoch changes
// Use inertia to ensure SPA experience and preserve scroll/state, but still update the URL for shareability and back button support
watch(selectedComparison, (newComp) => {
    if (newComp) {
        router.get(window.location.pathname, { comparison: newComp }, { preserveScroll: true, preserveState: true });
    }
});

function invalidateMap() {
    /**
     * Gemini 3 Pro, 2025-12-30
     * "If the table is hidden, the leaflet map doesn't render correctly. fix this by telling leaflet when the table is mounted or unmounted"
     */

    /**
     * Forces the Leaflet map to recalculate its container size and redraw after a 100ms delay.
     *
     * - **invalidateSize**: A Leaflet method that checks if the map container's dimensions have changed and updates the map accordingly. This is necessary because Leaflet caches the container size for performance; if the container resizes (or becomes visible) without this call, the map may render incorrectly (e.g., missing tiles or wrong center).
     * - **Overall Logic**: The `setTimeout` is used as a workaround to ensure the DOM has fully rendered or that CSS transitions (like opening a modal or tab) have completed before the map attempts to measure its container.
     */
    // Browser rendering can be asynchronous, and sometimes the map container might not have the correct dimensions immediately after a change (like showing/hiding a sidebar). By using `setTimeout`, we allow the browser to finish any pending layout calculations before Leaflet checks the container size.
    setTimeout(() => {
        map.value?.invalidateSize();
    }, 100);
}

function zoomToPoint(pointId: number) {
    // zoom to selected point
    const point = props.points.find((p) => p.id === pointId);
    if (point && map.value) {
        const m =
            point.measurementValues.find((m) => m.measurementId === props.referenceId) || point.measurementValues[0];
        if (m) {
            map.value.setView([m.lat, m.lon], 17);
        }
        // Set selected point id so the table can highlight the row.
        // Clear any pending highlight timeout so successive clicks don't race.
        if (highlightTimeout) {
            clearTimeout(highlightTimeout);
            highlightTimeout = null;
        }
        selectedPointId.value = pointId;
        highlightTimeout = window.setTimeout(() => {
            // Only clear if the timeout is still the latest
            selectedPointId.value = null;
            highlightTimeout = null;
        }, 1100);
    }
}

onUnmounted(() => {
    if (highlightTimeout) {
        clearTimeout(highlightTimeout);
        highlightTimeout = null;
    }
});

function drawMap() {
    if (!map.value) return;

    // Clear existing layers before redrawing
    markersLayer.clearLayers();

    props.points.forEach((point) => {
        let latlngs: [number, number][] = [];

        // Validate scale globally for both modes
        if (vectorScale.value <= 0 || typeof vectorScale.value !== 'number') {
            vectorScale.value = 1;
        }
        if (vectorScale.value > 100000) {
            vectorScale.value = 100000;
        }
        const scale = vectorScale.value;

        // If both reference and comparison are selected, calculate scaled vector
        if (props.referenceId && selectedComparison.value && !isGaitLine.value) {
            const refM = point.measurementValues.find((m) => m.measurementId === props.referenceId);
            const compM = point.measurementValues.find((m) => m.measurementId === selectedComparison.value);

            if (refM && compM) {
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

                latlngs = [
                    [refM.lat, refM.lon],
                    [newLat, newLon],
                ];
            }
        } else {
            // Gait line mode (Ganglinie): all measurements connected chronologically
            const measurements = [...point.measurementValues];

            if (measurements.length > 0) {
                const origin = measurements[0];

                latlngs = measurements.map((m) => {
                    const dLon = m.lon - origin.lon;
                    const dLat = m.lat - origin.lat;

                    const dLonScaled = dLon * scale;
                    const dLatScaled = dLat * scale;

                    const newLon = origin.lon + dLonScaled;
                    const newLat = origin.lat + dLatScaled;

                    return [newLat, newLon];
                });
            }
        }

        if (!latlngs.length) return;

        // Polyline with white outline for contrast
        const framePolyline = L.polyline(latlngs, { color: 'white', weight: 4 });
        const currPolyline = L.polyline(latlngs, { color: 'black', weight: 2 });
        currPolyline.on('click', () => zoomToPoint(point.id));
        framePolyline.on('click', () => zoomToPoint(point.id));
        markersLayer.addLayer(framePolyline);
        markersLayer.addLayer(currPolyline);

        // Draw small circle markers for the LAST measurement
        const lastCoord = latlngs[latlngs.length - 1];
        const marker = L.circleMarker(lastCoord, {
            radius: 4,
            fillColor: props.pointColors[point.id] || 'gray',
            color: 'gray',
            weight: 1,
            opacity: 0.5,
            fillOpacity: 0.8,
        });

        marker.on('click', () => zoomToPoint(point.id));
        markersLayer.addLayer(marker);

        // Text label next to the "arrowhead"
        const textMarker = L.marker(latlngs[latlngs.length - 1], {
            icon: L.divIcon({
                // Set class for CSS styling
                className: 'text-labels text-black text-xs font-bold',
                html: `<span style="text-shadow: 1px 1px 0px white, -1px 1px 0px white, 1px -1px 0px white, -1px -1px 0px white;font-size:110%;">${point.name}</span>`,
                iconSize: [0, 0],
                iconAnchor: [10, -10],
            }),
        });
        textMarker.on('click', () => zoomToPoint(point.id));
        markersLayer.addLayer(textMarker);
    });
}

watch([selectedComparison, vectorScale, isGaitLine], () => {
    drawMap();
});

onMounted(() => {
    if (!mapContainer.value) return;

    const leafletMap = L.map(mapContainer.value).setView([47.5, 9.75], 13);
    map.value = leafletMap;
    markersLayer.addTo(leafletMap);

    const mainMap = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 23,
        // default tile size (256)
        minZoom: 4,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        // show only this initially
    }).addTo(leafletMap);

    // WMS Layers by VOGIS
    const schummerung_surface = L.tileLayer.wms(
        'https://vogis.cnv.at/mapserver/mapserv?map=i_schummerung_2023_r_wms.map',
        {
            layers: 'schummerung_2023_oberflaeche_25cm',
            format: 'image/png',
            transparent: true,
            maxZoom: 23,
            minZoom: 4,
            attribution: '&copy; VOGIS CNV',
        },
    );

    const schummerung_terrain = L.tileLayer.wms(
        'https://vogis.cnv.at/mapserver/mapserv?map=i_schummerung_2023_r_wms.map',
        {
            layers: 'schummerung_2023_gelaende_25cm',
            format: 'image/png',
            transparent: true,
            maxZoom: 23,
            minZoom: 4,
            attribution: '&copy; VOGIS CNV',
        },
    );

    const aerial_2024 = L.tileLayer.wms('https://vogis.cnv.at/mapserver/mapserv?map=i_luftbilder_r_wms.map', {
        layers: 'wi2024-25_20cm',
        format: 'image/png',
        transparent: true,
        maxZoom: 23,
        minZoom: 4,
        attribution: '&copy; VOGIS CNV',
    });

    const aerial_2018 = L.tileLayer.wms('https://vogis.cnv.at/mapserver/mapserv?map=i_luftbilder_r_wms.map', {
        layers: 'ef2018_10cm',
        format: 'image/png',
        transparent: true,
        maxZoom: 23,
        minZoom: 4,
        attribution: '&copy; VOGIS CNV',
    });

    const aerial_2023 = L.tileLayer.wms('https://vogis.cnv.at/mapserver/mapserv?map=i_luftbilder_r_wms.map', {
        layers: 'ef2023_10cm_t',
        format: 'image/png',
        transparent: true,
        maxZoom: 23,
        minZoom: 4,
        attribution: '&copy; VOGIS CNV',
    });

    // Layer control: Change base layer and toggle hillshade
    L.control
        .layers({
            'Standard Karte': mainMap,
            'Schummerung Oberfläche': schummerung_surface,
            'Schummerung Gelände': schummerung_terrain,
            'Luftbild: Echtfarben Winter Mosaik 2024-25': aerial_2024,
            'Luftbild: 2018': aerial_2018,
            Echtfarbenbild_2023_10cm_technisch: aerial_2023,
        })
        .addTo(leafletMap);

    L.control.scale().addTo(leafletMap);

    // https://api.maptiler.com/tiles/contours-v2/{z}/{x}/{y}.pbf?key=DGwAtMAEBbbrxqSn9k9p

    // Initial setup
    if (props.points.length > 0) {
        if (!selectedComparison.value && props.measurements.length > 1) {
            selectedComparison.value = props.measurements[props.measurements.length - 1].id;
        }
        drawMap();

        const allCoords: [number, number][] = [];
        props.points.forEach((p) => p.measurementValues.forEach((m) => allCoords.push([m.lat, m.lon])));
        if (allCoords.length) {
            map.value.fitBounds(allCoords, { padding: [50, 50] });
        }
    }
});
</script>

<template>
    <div class="flex h-full w-full flex-col overflow-hidden">
        <MapToolbar
            :measurements="props.measurements"
            :reference-id="props.referenceId"
            v-model:selected-comparison="selectedComparison"
            v-model:vector-scale="vectorScale"
            v-model:is-gait-line="isGaitLine"
        />

        <!-- The map is always 80% high, no matter the table size -->
        <div class="flex h-[80vh] overflow-hidden">
            <div ref="mapContainer" class="relative z-0 h-full flex-1"></div>

            <!-- Table is only shown if there is a selected comparison epoch -->
            <DisplacementTable
                v-if="!isGaitLine"
                :points="props.points"
                :reference-id="props.referenceId"
                :comparison-id="selectedComparison"
                :highlighted-point-id="selectedPointId"
                :displacements="props.displacements"
                @select-point="zoomToPoint"
                @vue:mounted="invalidateMap"
                @vue:unmounted="invalidateMap"
            />
        </div>
    </div>
</template>
