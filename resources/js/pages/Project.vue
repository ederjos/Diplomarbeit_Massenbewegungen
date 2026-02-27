<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { DisplacementsByPointAndMeasurement, Measurement, Point } from '@/@types/measurement';
import type { ProjectDetails } from '@/@types/project';
import type { User } from '@/@types/user';
import DetailsTab from '@/components/project/DetailsTab.vue';
import ResultsTab from '@/components/project/ResultsTab.vue';
import TabSwitcher from '@/components/ui/TabSwitcher.vue';
import { colors } from '@/config/colors';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    project: ProjectDetails;
    points: Point[];
    measurements: Measurement[];
    displacements: DisplacementsByPointAndMeasurement;
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
            <!-- Page title -->
            <h1 class="mb-4 text-center text-2xl font-bold text-slate-700">Projekt {{ project.name }}</h1>
            <!-- v-model used for 2-way data-binding -->
            <TabSwitcher v-model:activeTab="activeTab" />
            <div>
                <ResultsTab
                    id="results"
                    v-show="activeTab === 'results'"
                    :points="points"
                    :point-colors="pointColors"
                    :measurements="measurements"
                    :displacements="displacements"
                />
                <DetailsTab
                    id="basics"
                    v-show="activeTab === 'basics'"
                    :project="project"
                    :contact-persons="contactPersons"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
