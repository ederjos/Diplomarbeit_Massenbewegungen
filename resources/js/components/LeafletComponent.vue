<script setup lang="ts">
import { Measurement, Point } from '@/@types/measurement';
import L from 'leaflet';
import { computed, onMounted, ref, watch } from 'vue';

/**
 * import { usePage } from '@inertiajs/vue3';
 * const spatial = usePage().props.spatial as any;
 * If we ever need the srid from the backend
 * Requires "'spatial' => config('spatial')," in Middleware/HandleInertiaRequests.php
 * Source: https://inertiajs.com/docs/v2/data-props/shared-data
 */


/**
 * Gemini 3 Flash, 2026-02-04
 * "think about whether it might make sense to return only the geom values to the vue file for calculating coordinate differences so that it doesn't have to worry about what srid is used in the background."
 */

const props = defineProps<{
    points: Point[];
    pointColors: Record<number, string>; // like hash map
    measurements: Measurement[];
}>();

/**
 * ChatGPT GPT-5, 2025-11-25
 * "I created this code using my api to retrieve the data. now make it work with typescript. [old code in js]"
 */

/**
 * Gemini 3 Pro, 2025-12-02
 * "Add a select box at the top where the user can select two reference measurements (based on name and dates). then, the markers are changed to the two measurement values of the measurements."
 */

/**
 * Gemini 3 Pro, 2025-12-02
 * "this component works greatly. now, we want to rearrange the design though and add a new table. in this table there should be a col for point name, one for delta pos (x and y) and one for delta height (z). keep in mind that the values in the Measurement interface of x,y,z are in the epsg 31254"
 */

/**
 * Gemini 3 Pro, 2025-12-16
 * "i have this file where i get coordinates data in lat, lon and a special epsg 31254. now, i want to scale up the distances so that a distance of 1 cm looks like 1 m. for that I created the vectorScale variable. Now, you should update the lat, lon (leaflet only knows those coords) based on the epsg x,y upscaled based on the initial difference (p1->p2) but the value of the vector is into the same direction the vectorScale times as high. i urge you to use proj4 to work with the exact coordinates!"
 */

const mapContainer = ref<HTMLDivElement | null>(null);
const map = ref<L.Map | null>(null);
// const points = ref<Point[]>([]) // Removed
const selectedReference = ref<number | null>(null);
const selectedMeasurement = ref<number | null>(null);
const vectorScale = ref<number>(100);
const isGaitLine = ref<boolean>(false);
const markersLayer = new L.LayerGroup();
// https://leafletjs.com/examples/layers-control/

// Point deltas for the table (sidebar)
const pointDeltas = computed(() => {
    if (!selectedReference.value || !selectedMeasurement.value) return [];

    return props.points
        .map((p) => {
            // Find the measurement values for the selected reference and comparison epochs
            const ref = p.measurementValues.find((m) => m.measurementId === selectedReference.value);
            const m = p.measurementValues.find((m) => m.measurementId === selectedMeasurement.value);

            // If either is missing for this point, we can't calculate a delta
            if (!ref || !m) return null;

            // Calculate differences in coordinates and convert to cm
            const p1 = L.latLng(ref.lat, ref.lon);
            const p2 = L.latLng(m.lat, m.lon);
            const distance2d = p1.distanceTo(p2) * 100; // in cm
            const deltaHeight = (m.height - ref.height) * 100;

            return {
                id: p.id,
                name: p.name,
                deltaHeight: deltaHeight,
                distance2d: distance2d,
                lat: m.lat,
                lon: m.lon,
            };
            // filters all null out (points w/o data for this epoch) & guarantees that there are no nulls
        })
        .filter((p): p is NonNullable<typeof p> => p !== null);
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
    // zoom to arrowhead of selected point
    const point = props.points.find((p) => p.id === pointId);
    if (point && map.value) {
        const m =
            point.measurementValues.find((m) => m.measurementId === selectedReference.value) ||
            point.measurementValues[0];
        if (m) {
            map.value.setView([m.lat, m.lon], 17);
        }
        // Also highlight the row in the table with a css animation (transform: scale, background: yellow fade out)
        const row = document.querySelector(`tr[data-point-id="${pointId}"]`) as HTMLElement | null;
        if (row) {
            //row.scrollIntoView({ behavior: 'smooth', block: 'center' })

            // Apply temporary styles for the highlight effect
            row.style.transition = 'transform 0.2s, background-color 1s';
            row.style.transform = 'scale(1.02)'; // zoom out a bit
            row.style.backgroundColor = '#fef08a'; // yellow-200

            // Remove styles 1s after animation
            setTimeout(() => {
                row.style.transform = '';
                row.style.backgroundColor = '';
            }, 1000);
        }
    }
}

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
        if (selectedReference.value && selectedMeasurement.value && !isGaitLine.value) {
            const refM = point.measurementValues.find((m) => m.measurementId === selectedReference.value);
            const compM = point.measurementValues.find((m) => m.measurementId === selectedMeasurement.value);

            if (refM && compM) {
                // vectors
                const p1 = L.latLng(refM.lat, refM.lon);
                const p2 = L.latLng(compM.lat, compM.lon);
                const dLon = p2.lng - p1.lng; // Note: Leaflet's latLng uses (lat, lng) but dx is in x direction (longitude)
                const dLat = p2.lat - p1.lat; // and dy is in y direction (latitude)

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
            // Gait line (show all measurements connected chronologically)

            // don't mutate original
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

        // Draw polyline connecting measurements
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

        const textMarker = L.marker(latlngs[latlngs.length - 1], {
            icon: L.divIcon({
                className: 'text-labels text-black text-xs font-bold', // Set class for CSS styling
                html: `<span style="text-shadow: 1px 1px 0px white, -1px 1px 0px white, 1px -1px 0px white, -1px -1px 0px white;font-size:110%;">${point.name}</span>`,
                iconSize: [0, 0],
                iconAnchor: [10, -10],
            }),
        });
        textMarker.on('click', () => zoomToPoint(point.id));
        markersLayer.addLayer(textMarker);
    });
}

watch([selectedReference, selectedMeasurement, vectorScale, isGaitLine], () => {
    // As soon as selection changes, redraw the map
    drawMap();
});

onMounted(() => {
    if (!mapContainer.value) return;

    const leafletMap = L.map(mapContainer.value).setView([47.5, 9.75], 13);
    map.value = leafletMap;
    markersLayer.addTo(leafletMap);

    leafletMap.on('zoom', () => {
        console.log('Zoom level changed to:', leafletMap.getZoom());
    });

    const mainMap = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 23,
        minZoom: 4, // default tile size (256)
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(leafletMap); // show only this initially

    // WMS Layers by vogis
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
            'Echtfarbenbild_2023_10cm_technisch': aerial_2023,
        })
        .addTo(leafletMap);

    // https://api.maptiler.com/tiles/contours-v2/{z}/{x}/{y}.pbf?key=DGwAtMAEBbbrxqSn9k9p

    // Initial setup
    if (props.points.length > 0) {
        if (!selectedReference.value && props.measurements.length) {
            selectedReference.value = props.measurements[0].id;
        }
        if (!selectedMeasurement.value && props.measurements.length > 1) {
            selectedMeasurement.value = props.measurements[props.measurements.length - 1].id;
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
    <div class="flex h-screen w-screen flex-col overflow-hidden">
        <div class="z-10 flex shrink-0 items-center gap-4 bg-white p-4 shadow">
            <div>
                <label class="mb-1 block text-sm font-bold">Referenzepoche</label>
                <select v-model.number="selectedReference" class="rounded border p-1 disabled:text-gray-400"
                    :disabled="isGaitLine">
                    <option v-for="m in props.measurements" :key="m.id" :value="m.id">
                        {{ m.name }} ({{ new Date(m.datetime).toLocaleDateString('de-AT') }})
                    </option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">Vergleichsepoche</label>
                <select v-model.number="selectedMeasurement" class="rounded border p-1 disabled:text-gray-400"
                    :disabled="isGaitLine">
                    <option v-for="m in props.measurements" :key="m.id" :value="m.id">
                        {{ m.name }} ({{ new Date(m.datetime).toLocaleDateString('de-AT') }})
                    </option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">Vektormaßstab M 1&nbsp;:&nbsp;</label>
                <input v-model.number="vectorScale" type="number" class="w-28 rounded border p-1" min="1"
                    max="100000" />
            </div>
            <div class="me-4 flex items-center">
                <input type="checkbox" v-model="isGaitLine" id="checkboxIsGaitLine"
                    class="border-default-medium bg-neutral-secondary-medium h-4 w-4 rounded-xs border" />
                <label class="text-heading ml-2 block text-sm font-bold select-none"
                    for="checkboxIsGaitLine">Ganglinie</label>
            </div>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <div ref="mapContainer" class="relative z-0 h-full flex-1"></div>

            <div v-if="!isGaitLine" class="z-10 w-96 shrink-0 overflow-y-auto border-l bg-gray-50 p-4 shadow-lg"
                @vue:mounted="invalidateMap" @vue:unmounted="invalidateMap">
                <h2 class="mb-3 text-lg font-bold">Verschiebungen</h2>
                <table class="relative w-full border-collapse text-left text-sm">
                    <thead class="top-0 z-10 border-b bg-gray-100 text-xs uppercase shadow-sm">
                        <tr>
                            <th class="px-3 py-2 font-semibold text-gray-800">Punkt</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-800">Δ Lage [cm]</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-800">Δ Höhe [cm]</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in pointDeltas" :key="p.id"
                            class="cursor-pointer border-b bg-white odd:bg-gray-50 hover:bg-gray-100"
                            @click="zoomToPoint(p.id)" :data-point-id="p.id">
                            <td class="px-3 py-2 font-medium text-gray-900">{{ p.name }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ p.distance2d.toFixed(4) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">
                                {{ p.deltaHeight > 0 ? '+' : '' }}{{ p.deltaHeight.toFixed(4) }}
                            </td>
                        </tr>
                        <tr v-if="pointDeltas.length === 0">
                            <td colspan="3" class="px-3 py-4 text-center text-gray-500">Keine Daten für die Auswahl</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
