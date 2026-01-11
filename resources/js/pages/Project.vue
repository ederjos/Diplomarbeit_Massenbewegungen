<template>
  <Head :title="`${project.name}`" />
  <div class="flex justify-center flex-col items-center gap-8">
    <Link href="/" class="text-blue-500 hover:underline">Zur Startseite</Link>
    <LeafletComponent :points="points" :point-colors="pointColors" :measurements="measurements" />
    <ProjectTimeline class="w-full max-w-4xl" :points="points" :point-colors="pointColors" />
  </div>
</template>

<script lang="ts" setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import LeafletComponent from '../components/LeafletComponent.vue'
import ProjectTimeline from '../components/ProjectTimeline.vue'
import { Measurement, Point } from '@/@types/measurement'
import { Project } from '@/@types/project'

const props = defineProps<{
  project: Project,
  points: Point[],
  measurements: Measurement[]
}>();

// Distinct colors (from Simon's file)
const colors = [
  '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
  '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'
]

const pointColors = computed(() => {
  const colorMap: Record<number, string> = {}
  props.points.forEach((p, index) => {
    colorMap[p.id] = colors[index % colors.length]
  })
  return colorMap
})
</script>
