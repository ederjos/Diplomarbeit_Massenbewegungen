<template>
    <div class="w-screen h-screen">
        <div ref="mapContainer" class="w-full h-full"></div>
    </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import L from 'leaflet'
import axios from 'axios'

interface Measurement {
    measurement_id: number
    x: number
    y: number
    z: number
    lat: number
    lon: number
    date: string
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

const mapContainer = ref<HTMLDivElement | null>(null)

const colors = [
    '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
    '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe'
]

onMounted(async () => {
    if (!mapContainer.value)
        return

    const map = L.map(mapContainer.value).setView([47.5, 9.75], 13)

    L.tileLayer('https://api.maptiler.com/tiles/satellite-v2/{z}/{x}/{y}.jpg?key=DGwAtMAEBbbrxqSn9k9p',
        {
            maxZoom: 23,
            tileSize: 512,
            zoomOffset: -1,
            attribution: '© MapTiler'
        }).addTo(map)

    const { data: points } = await axios.get<Point[]>('/api/projects/1/points-with-measurements')

    const bounds: [number, number][] = []

    // Iterate over each point
    points.forEach(point => {
        // Ensure measurements exist and sort chronologically
        const measurements = point.measurement_values

        if (!measurements.length) return

        // Only need lat & lon for leaflet (maybe edit api)
        const latlngs: [number, number][] = measurements.map(m => [m.lat, m.lon])

        // Draw polyline connecting measurements
        L.polyline(latlngs, { color: 'white', weight: 2 }).addTo(map)

        // Draw small circle markers for each measurement
        latlngs.forEach(coord => {
            L.circleMarker(coord, {
                radius: 3,
                fillColor: colors[point.id % colors.length],
                color: 'gray',
                weight: 1,
                opacity: 0.5,
                fillOpacity: 0.8
            }).addTo(map)
        })

        bounds.push(...latlngs)
    })

    if (bounds.length) {
        map.fitBounds(bounds, { padding: [50, 50] })
    }
})
</script>
