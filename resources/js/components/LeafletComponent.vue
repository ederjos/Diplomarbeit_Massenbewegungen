<template>
    <div class="flex flex-col h-screen w-screen overflow-hidden">
        <div class="bg-white p-4 shadow z-10 flex gap-4 shrink-0 items-center">
            <div>
            <label class="block text-sm font-bold mb-1">Referenzepoche</label>
            <select v-model.number="selectedReference" class="border rounded p-1">
                <option
                v-for="(m, idx) in availableMeasurements"
                :key="m.id"
                :value="m.id"
                >
                {{ m.name }} ({{ new Date(m.date).toLocaleDateString('de-AT')}})
                </option>
            </select>
            </div>
            <div>
            <label class="block text-sm font-bold mb-1">Vergleichsepoche</label>
            <select v-model.number="selectedMeasurement" class="border rounded p-1">
                <option
                v-for="(m, idx) in availableMeasurements"
                :key="m.id"
                :value="m.id"
                >
                {{ m.name }} ({{ new Date(m.date).toLocaleDateString('de-AT') }})
                </option>
            </select>
            </div>
        </div>
        
        <div class="flex flex-1 overflow-hidden">
            <div ref="mapContainer" class="flex-1 h-full relative z-0"></div>
            
            <div class="w-96 bg-gray-50 border-l overflow-y-auto p-4 shrink-0 shadow-lg z-10">
                <h2 class="font-bold text-lg mb-3">Verschiebungen</h2>
                <table class="w-full text-sm text-left border-collapse relative">
                    <thead class="top-0 z-10 bg-gray-100 text-xs uppercase border-b shadow-sm">
                        <tr>
                            <th class="px-3 py-2 font-semibold text-gray-800">Punkt</th>
                            <th class="px-3 py-2 font-semibold text-gray-800 text-right">Δ Lage [m]</th>
                            <th class="px-3 py-2 font-semibold text-gray-800 text-right">Δ Höhe [m]</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in pointDeltas" :key="p.id" class="bg-white odd:bg-gray-50 border-b hover:bg-gray-100 cursor-pointer" @click="zoomToPoint(p.id)">
                            <td class="px-3 py-2 font-medium text-gray-900">{{ p.name }}</td>
                            <td class="px-3 py-2 tabular-nums text-right">{{ p.d2d.toFixed(4) }}</td>
                            <td class="px-3 py-2 tabular-nums text-right">
                                {{ p.dz > 0 ? '+' : '' }}{{ p.dz.toFixed(4) }}
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
<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import L from 'leaflet'
import 'leaflet-polylinedecorator' // for arrows
import axios from 'axios'

interface Measurement {
    measurement_id: number
    x: number
    y: number
    z: number
    lat: number
    lon: number
    datetime: string
    measurement_name: string
}

interface Point {
    id: number
    name: string
    projection_id: number | null
    measurement_values: Measurement[]
}

/* Prompt (ChatGPT GPT-5)
 * "I created this code using my api to retrieve the data. now make it work with typescript. [old code in js]"
 */
/* Prompt (Gemini 3 Pro)
 * "Add a select box at the top where the user can select two reference measurements (based on name and dates). then, the markers are changed to the two measurement values of the measurements."
 */

/* Prompt (Gemini 3 Pro)
 * "this component works greatly. now, we want to rearrange the design though and add a new table. in this table there should be a col for point name, one for delta pos (x and y) and one for delta height (z). keep in mind that the values in the Measurement interface of x,y,z are in the epsg 31254"
 */

const mapContainer = ref<HTMLDivElement | null>(null)
const map = ref<L.Map | null>(null)
const points = ref<Point[]>([])
const availableMeasurements = ref<{id: number, name: string, date: string}[]>([])
const selectedReference = ref<number | null>(null)
const selectedMeasurement = ref<number | null>(null)
const markersLayer = new L.LayerGroup()
// https://leafletjs.com/examples/layers-control/

const pointDeltas = computed(() => {
    if (!selectedReference.value || !selectedMeasurement.value) return []
    
    return points.value.map(p => {
        // Find the measurement values for the selected reference and comparison epochs
        const ref = p.measurement_values.find(m => m.measurement_id === selectedReference.value)
        const m = p.measurement_values.find(m => m.measurement_id === selectedMeasurement.value)
        
        // If either is missing for this point, we can't calculate a delta
        if (!ref || !m) return null
        
        // Calculate differences in coordinates
        const dx = m.x - ref.x
        const dy = m.y - ref.y
        const dz = m.z - ref.z
        
        // Calculate 2D Euclidean distance (horizontal displacement)
        // d = sqrt(dx^2 + dy^2) - distance 2d
        const d2d = Math.sqrt(dx*dx + dy*dy)
        
        return {
            id: p.id,
            name: p.name,
            dx,
            dy,
            dz,
            d2d,
            lat: m.lat,
            lon: m.lon
        }
        // filters all null out (points w/o data for this epoch) & guarantees that there are no nulls
    }).filter((p): p is NonNullable<typeof p> => p !== null)
})

function zoomToPoint(pointId: number) {
    // zoom to arrowhead of selected point
    const point = points.value.find(p => p.id === pointId)
    if (point && map.value) {
        const m = point.measurement_values.find(m => m.measurement_id === selectedMeasurement.value) || point.measurement_values[point.measurement_values.length - 1]
        if (m) {
            map.value.setView([m.lat, m.lon], 19)
        }
    }
}

const colors = [
    '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
    '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe'
]

function displayPointDetails(point: Point): void {
    console.log(point)
    alert(point.name + " geklickt. Jetzt zeige Details an. (Z.B. zoom ohne Karte, Änderung pro Jahr, ...)")
    return
}

function drawMap() {
    if (!map.value) return
    
    // Clear existing layers before redrawing
    markersLayer.clearLayers()

    points.value.forEach(point => {
        // Create a shallow copy to avoid mutating the original array when sorting
        let measurements = [...point.measurement_values]

        // Filter if both are selected to show only the connection between reference and comparison
        if (selectedReference.value && selectedMeasurement.value) {
            measurements = measurements.filter(m => 
                m.measurement_id === selectedReference.value || 
                m.measurement_id === selectedMeasurement.value
            )
        }

        // Sort chronologically to ensure the line is drawn in the correct order (from old to new)
        measurements.sort((a, b) => new Date(a.datetime).getTime() - new Date(b.datetime).getTime())

        if (!measurements.length) return

        // Only need lat & lon for leaflet (maybe edit api)
        const latlngs: [number, number][] = measurements.map(m => [m.lat, m.lon])

        // Draw polyline connecting measurements
        const currPolyline = L.polyline(latlngs, { color: 'white', weight: 2 })
        currPolyline.on('click', () => zoomToPoint(point.id))
        markersLayer.addLayer(currPolyline)

        // Add arrow head if we have a line (more than 1 point)
        // This uses the leaflet-polylinedecorator plugin to draw an arrow at the end of the line
        if (latlngs.length > 1) {
             const decorator = L.polylineDecorator(currPolyline, {
                patterns: [
                    {
                        offset: '100%', // Arrow at the end
                        repeat: 0,      // Only one arrow
                        symbol: L.Symbol.arrowHead({
                            pixelSize: 8,
                            polygon: false,
                            pathOptions: { stroke: true, color: 'white', weight: 2 }
                        })
                    }
                ]
            })
            decorator.on('click', () => zoomToPoint(point.id))
            markersLayer.addLayer(decorator)
        }

        // Draw small circle markers for each measurement
        latlngs.forEach(coord => {
            const marker = L.circleMarker(coord, {
                radius: 3,
                fillColor: colors[point.id % colors.length],
                color: 'gray',
                weight: 1,
                opacity: 0.5,
                fillOpacity: 0.8
            })
            

            marker.on('click', () => zoomToPoint(point.id))
            markersLayer.addLayer(marker)
        })

        const textMarker = L.marker(latlngs[0], {
            icon: L.divIcon({
                className: 'text-labels',   // Set class for CSS styling
                html: `<span style="color: white; font-size: 12px; text-shadow: 1px 1px 2px black;">${point.name}</span>`,
                iconSize: [0, 0],
                iconAnchor: [10, -10]
            })
        })
        textMarker.on('click', () => zoomToPoint(point.id))
        markersLayer.addLayer(textMarker)
    })
}

watch([selectedReference, selectedMeasurement], () => {
    // As soon as selection changes, redraw the map
    drawMap()
})

onMounted(async () => {
    if (!mapContainer.value)
        return

    const leafletMap = L.map(mapContainer.value).setView([47.5, 9.75], 13)
    map.value = leafletMap
    markersLayer.addTo(leafletMap)

    leafletMap.on('zoom', () => {
        console.log('Zoom level changed to:', leafletMap.getZoom())
    })

    var mainSatelliteMap = L.tileLayer('https://api.maptiler.com/maps/satellite/{z}/{x}/{y}.png?key=DGwAtMAEBbbrxqSn9k9p', {
        maxZoom: 23,
        minZoom: 4,
        tileSize: 512,
        zoomOffset: -1,
        attribution: '© MapTiler'
    })

    var outdoorMap = L.tileLayer('https://api.maptiler.com/maps/outdoor-v4/{z}/{x}/{y}.png?key=DGwAtMAEBbbrxqSn9k9p', {
        maxZoom: 23,
        minZoom: 4,
        tileSize: 512,
        zoomOffset: -1,
        attribution: '© MapTiler'
    })

    var mainMap = L.tileLayer('https://api.maptiler.com/maps/base-v4/{z}/{x}/{y}.png?key=DGwAtMAEBbbrxqSn9k9p', {
        maxZoom: 23,
        minZoom: 4,
        tileSize: 512,
        zoomOffset: -1,
        attribution: '© MapTiler'
    }).addTo(leafletMap) // show only this initially
    
    // WMS Layer by vogis for hillshade
    var schummerung = L.tileLayer.wms('https://vogis.cnv.at/mapserver/mapserv?map=i_schummerung_2023_r_wms.map', {
        layers: 'schummerung_2023_oberflaeche_25cm',
        format: 'image/png',
        transparent: true,
        maxZoom: 23,
        minZoom: 4,
        attribution: '© VOGIS CNV'
    })

    // Layer control: Change base layer and toggle hillshade
    L.control.layers({
        "Standard": mainMap,
        "Satellit": mainSatelliteMap,
        "Outdoor": outdoorMap,
    }, {
        "Schummerung 25cm": schummerung
    }).addTo(leafletMap);

    // https://api.maptiler.com/tiles/contours-v2/{z}/{x}/{y}.pbf?key=DGwAtMAEBbbrxqSn9k9p

    const { data } = await axios.get<Point[]>('/api/projects/1/points-with-measurements')
    points.value = data

    // Extract unique measurements
    const measurementsMap = new Map<number, {id: number, name: string, date: string}>()
    points.value.forEach(p => {
        p.measurement_values.forEach(m => {
            if (!measurementsMap.has(m.measurement_id)) {
                measurementsMap.set(m.measurement_id, {
                    id: m.measurement_id,
                    name: m.measurement_name,
                    date: m.datetime
                })
            }
        })
    })
    availableMeasurements.value = Array.from(measurementsMap.values()).sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())

    selectedReference.value = availableMeasurements.value.length ? availableMeasurements.value[0].id : null
    selectedMeasurement.value = availableMeasurements.value.length > 1 ? availableMeasurements.value[availableMeasurements.value.length-1].id : null

    drawMap()
    
    // Initial fit bounds
    const allCoords: [number, number][] = []
    points.value.forEach(p => p.measurement_values.forEach(m => allCoords.push([m.lat, m.lon])))
    if (allCoords.length) {
        map.value.fitBounds(allCoords, { padding: [50, 50] })
    }
})
</script>
