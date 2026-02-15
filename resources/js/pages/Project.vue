<script setup lang="ts">
import { Measurement, Point, PointDisplacement } from '@/@types/measurement';
import { ProjectDetails } from '@/@types/project';
import { User } from '@/@types/user';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProjectDetailsTab from '@/components/ProjectDetailsTab.vue';
import ProjectResultsTab from '@/components/ProjectResultsTab.vue';
import TabSwitcher from '@/components/TabSwitcher.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { colors } from '@/config/colors';

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
