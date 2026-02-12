<script lang="ts" setup>
import { Measurement, Point, PointDisplacement } from '@/@types/measurement';
import { ProjectDetails } from '@/@types/project';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LeafletComponent from '../components/LeafletComponent.vue';
import ProjectTimeline from '../components/ProjectTimeline.vue';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

const props = defineProps<{
    project: ProjectDetails;
    points: Point[];
    measurements: Measurement[];
    referenceId: number | null;
    comparisonId: number | null;
    displacements: Record<number, PointDisplacement>;
}>();

const activeTab = ref('results');

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
            <div class="relative right-0">
                <ul class="relative flex flex-wrap px-1.5 py-1.5 list-none rounded-md bg-slate-100" data-tabs="tabs"
                    role="list">
                    <!-- Moving white background indicator -->
                    <span
                        class="absolute z-10 h-9 transition-transform duration-300 ease-in-out bg-white rounded-md"
                        :style="{
                            width: '50%',
                            transform: activeTab === 'results' ? 'translateX(0)' : 'translateX(100%)'
                        }"
                    ></span>
                    <li class="z-30 flex-auto text-center">
                        <a class="relative z-20 flex items-center justify-center w-full px-0 py-2 text-sm mb-0 border-0 rounded-md cursor-pointer"
                            :class="activeTab === 'results' ? 'text-slate-700 font-semibold' : 'text-slate-600'"
                            data-tab-target=""
                            role="tab" @click="activeTab = 'results'">
                            Ergebnisse
                        </a>
                    </li>
                    <li class="z-30 flex-auto text-center">
                        <a class="relative z-20 flex items-center justify-center w-full px-0 py-2 mb-0 text-sm border-0 rounded-lg cursor-pointer"
                            :class="activeTab === 'basics' ? 'text-slate-700 font-semibold' : 'text-slate-600'"
                            data-tab-target="" role="tab"
                            @click="activeTab = 'basics'">
                            Grundlagen
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <div id="results" v-show="activeTab === 'results'" class="grid grid-cols-1 gap-4 p-4">
                    <LeafletComponent
                        :points="points"
                        :point-colors="pointColors"
                        :measurements="measurements"
                        :reference-id="referenceId"
                        :comparison-id="comparisonId"
                        :displacements="displacements"
                    />
                    <div class="flex justify-center">
                        <ProjectTimeline
                            :points="points"
                            :point-colors="pointColors"
                            :measurements="measurements"
                        />
                    </div>
                </div>
                <div id="basics" v-show="activeTab === 'basics'" class="p-4">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold mb-4 text-slate-700">Projektdetails</h2>
                        <table class="w-full max-w-2xl">
                            <tbody>
                                <tr class="border-b border-slate-200">
                                    <td class="py-3 pr-4 font-semibold text-slate-600 w-48">Auftraggeber</td>
                                    <td class="py-3 text-slate-800">{{ project.client || '—' }}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="py-3 pr-4 font-semibold text-slate-600">Gemeinde</td>
                                    <td class="py-3 text-slate-800">{{ project.municipality || '—' }}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="py-3 pr-4 font-semibold text-slate-600">Sachbearbeiter</td>
                                    <td class="py-3 text-slate-800">{{ project.clerk || '—' }}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="py-3 pr-4 font-semibold text-slate-600">Typ</td>
                                    <td class="py-3 text-slate-800">{{ project.type || '—' }}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="py-3 pr-4 font-semibold text-slate-600">Intervall</td>
                                    <td class="py-3 text-slate-800">{{ project.period || '—' }}</td>
                                </tr>
                                <tr class="border-b border-slate-200">
                                    <td class="py-3 pr-4 font-semibold text-slate-600">Letzte Geschäftszahl</td>
                                    <td class="py-3 text-slate-800">{{ project.last_file_number || '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-slate-600 align-top">Anmerkung</td>
                                    <td class="py-3 text-slate-800">{{ project.comment || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
