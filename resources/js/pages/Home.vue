<template>
  <Head title="Home" />
  <div class="flex justify-center flex-col items-center gap-8">
    <div v-if="loading" class="text-center p-8">Laden...</div>
    <template v-else>
      <LeafletComponent :points="points" :point-colors="pointColors" />
      <ProjectTimeline class="w-full max-w-4xl" :points="points" :point-colors="pointColors" />
    </template>
  </div>
</template>

<script lang="ts" setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'
import LeafletComponent from '../components/LeafletComponent.vue'
import ProjectTimeline from '../components/ProjectTimeline.vue'
import { Point } from '@/@types/measurement'

const points = ref<Point[]>([])
const pointColors = ref<Record<number, string>>({})
const loading = ref(true)

// Distinct colors (from Simon's file)
const colors = [
  '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
  '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'
]

/* Prompt (Gemini 3 Pro)
 * "Help me change the file so that the api is called only once in the home"
 */

onMounted(async () => {
  try {
    const { data } = await axios.get<Point[]>('/api/projects/1/points-with-measurements')
    points.value = data
    
    // Assign colors
    data.forEach((p, index) => {
      pointColors.value[p.id] = colors[index % colors.length]
    })
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>
