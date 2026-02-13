<script lang="ts" setup>
import { Measurement, Point, PointDisplacement } from '@/@types/measurement';
import { ProjectDetails } from '@/@types/project';
import { User } from '@/@types/user';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProjectDetailsTab from '../components/ProjectDetailsTab.vue';
import ProjectResultsTab from '../components/ProjectResultsTab.vue';
import TabSwitcher from '../components/TabSwitcher.vue';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    project: ProjectDetails;
    points: Point[];
    measurements: Measurement[];
    referenceId: number | null;
    comparisonId: number | null;
    displacements: Record<number, PointDisplacement>;
    contactPersons: User[];
}>();

const activeTab = ref<'results' | 'basics'>('results');

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
        <!-- Source: https://www.material-tailwind.com/docs/html/tabs -->
        <!-- Gemini 2.5 Pro, 2026-02-12
             "Please fix the tab so that when changing the tab selection 'Results' or 'Basics' that the white background 'moves' from one to the other, like in the original."
         -->
        <div class="w-full">
            <!-- v-model used for 2-way data-binding -->
            <TabSwitcher v-model:activeTab="activeTab" />
            <div>
                <ProjectResultsTab
                    id="results"
                    v-show="activeTab == 'results'"
                    :points="points"
                    :point-colors="pointColors"
                    :measurements="measurements"
                    :reference-id="referenceId"
                    :comparison-id="comparisonId"
                    :displacements="displacements"
                />
                <ProjectDetailsTab
                    id="basics"
                    v-show="activeTab == 'basics'"
                    :project="project"
                    :contact-persons="contactPersons"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
