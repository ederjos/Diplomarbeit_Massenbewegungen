<script lang="ts" setup>
import { Measurement, Point } from '@/@types/measurement';
import { Project } from '@/@types/project';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import LeafletComponent from '../components/LeafletComponent.vue';
import ProjectTimeline from '../components/ProjectTimeline.vue';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    project: Project;
    points: Point[];
    measurements: Measurement[];
}>();

/**
 * GPT-5 mini, 2026-02-03
 * "Please improve the colors array in Project.vue and add a comment next to each color with the name of the color."
 */
const colors = [
    '#1f77b4', // Blue
    '#ff7f0e', // Orange
    '#2ca02c', // Green
    '#d62728', // Red
    '#9467bd', // Purple
    '#8b5e3c', // Brown
    '#e377c2', // Pink
    '#6b7280', // Gray
    '#bdbf2f', // Olive
    '#17becf', // Cyan
];

const pointColors = computed(() => {
    const colorMap: Record<number, string> = {};
    props.points.forEach((p, index) => {
        colorMap[p.id] = colors[index % colors.length];
    });
    return colorMap;
});
</script>

<template>
    <Head :title="`${project.name}`" />
    <AuthenticatedLayout>
        <LeafletComponent :points="points" :point-colors="pointColors" :measurements="measurements" />
        <ProjectTimeline :points="points" :point-colors="pointColors" :measurements="measurements" />
    </AuthenticatedLayout>
</template>
