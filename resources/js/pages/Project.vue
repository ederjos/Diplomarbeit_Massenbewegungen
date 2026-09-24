<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import type { ChartDisplacements, MapDisplacements, Measurement, Point } from '@/types/measurement';
import type { ProjectDetails } from '@/types/project';
import type { User } from '@/types/user';

import { createMeasurementImport } from '@/actions/App/Http/Controllers/AdminController';
import DetailsTab from '@/components/project/DetailsTab.vue';
import ResultsTab from '@/components/project/ResultsTab.vue';
import TabSwitcher from '@/components/ui/TabSwitcher.vue';
import { colors } from '@/config/colors';

const props = defineProps<{
    project: ProjectDetails;
    points: Point[];
    measurements: Measurement[];
    referenceId: number | null;
    comparisonId: number | null;
    mapDisplacements: MapDisplacements;
    chartDisplacements: ChartDisplacements;
    contactPersons: User[];
}>();

const activeTab = ref<'results' | 'basics'>('results');
const page = usePage();
const isAdmin = computed(() => page.props.auth.user?.permissions.isAdmin === true);

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

    <!-- Source: https://www.material-tailwind.com/docs/html/tabs -->
    <!-- Gemini 2.5 Pro, 2026-02-12
         "Please fix the tab so that when changing the tab selection 'Results' or 'Basics' that the white background 'moves' from one to the other, like in the original."
    -->
    <div class="w-full">
        <!-- Page title -->
        <div class="mb-4 grid grid-cols-[1fr_auto_1fr] items-center gap-3">
            <span aria-hidden="true" />
            <h1 class="text-center text-2xl font-bold text-slate-700">Projekt {{ project.name }}</h1>
            <Link
                v-if="isAdmin"
                :href="createMeasurementImport(project.id)"
                class="justify-self-end rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium whitespace-nowrap text-white hover:bg-indigo-700"
            >
                CSV importieren
            </Link>
        </div>
        <!-- v-model used for 2-way data-binding -->
        <TabSwitcher v-model:active-tab="activeTab" />
        <div>
            <ResultsTab
                v-show="activeTab === 'results'"
                id="results-panel"
                role="tabpanel"
                aria-labelledby="results-tab"
                :project-id="project.id"
                :points="points"
                :point-colors="pointColors"
                :measurements="measurements"
                :initial-reference-id="referenceId"
                :initial-comparison-id="comparisonId"
                :initial-map-displacements="mapDisplacements"
                :chart-displacements="chartDisplacements"
            />
            <DetailsTab
                v-show="activeTab === 'basics'"
                id="basics-panel"
                role="tabpanel"
                aria-labelledby="basics-tab"
                :project="project"
                :contact-persons="contactPersons"
            />
        </div>
    </div>
</template>
