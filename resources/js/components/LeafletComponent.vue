<template>
    <div class="w-screen h-screen">
        <div ref="mapContainer" class="w-full h-full"></div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import L from 'leaflet'
import axios from 'axios'

const mapContainer = ref(null)

onMounted(async () => {
    const map = L.map(mapContainer.value).setView([47.5, 9.75], 13)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map)

    const { data: points } = await axios.get('/api/projects/1/points-with-measurements')

    const bounds = []

    // Iterate over each point
    points.forEach(point => {
        // Ensure measurements exist and sort chronologically
        const measurements = point.measurement_values
            .filter(m => m.lat !== null && m.lon !== null)
            .sort((a, b) => a.measurement_id - b.measurement_id)

        if (!measurements.length) return

        // Convert to [lat, lng] array for Leaflet
        const latlngs = measurements.map(m => [parseFloat(m.lat), parseFloat(m.lon)])

        // Draw polyline connecting measurements
        L.polyline(latlngs, { color: 'blue', weight: 2 }).addTo(map)

        // Draw small circle markers for each measurement
        latlngs.forEach(coord => {
            L.circleMarker(coord, {
                radius: 4,
                fillColor: 'red',
                color: 'black',
                weight: 1,
                opacity: 1,
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
