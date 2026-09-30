<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import type { BaseMeasurement } from '@/types/measurement';
import type { BaseProject } from '@/types/project';

import { downloadMeasurementExport } from '@/actions/App/Http/Controllers/AdminController';
import { show } from '@/actions/App/Http/Controllers/ProjectController';

const props = defineProps<{
    project: BaseProject;
    measurements: BaseMeasurement[];
}>();

const selectedMeasurementId = ref<number | null>(props.measurements[0]?.id ?? null);
const downloadUrl = computed(() =>
    selectedMeasurementId.value === null
        ? '#'
        : downloadMeasurementExport.url({ project: props.project.id, measurement: selectedMeasurementId.value }),
);
</script>

<template>
    <Head title="Messungen exportieren" />

    <div class="w-full max-w-3xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-sm font-medium text-indigo-600">Projekt: {{ project.name }}</p>
                <h1 class="text-3xl font-bold text-gray-900">Messung exportieren</h1>
                <p class="max-w-2xl text-sm leading-6 text-gray-600">
                    Wähle eine Messepoche aus. Die Datei enthält jede Zeile im Format
                    <span class="font-mono text-gray-800">Punktname;X;Y;Z</span> und kann wieder importiert werden.
                </p>
            </div>
            <Link :href="show(project.id)" class="text-sm font-medium text-gray-600 underline hover:text-gray-900">
                Zurück zum Projekt
            </Link>
        </div>

        <form
            :action="downloadUrl"
            method="get"
            class="space-y-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-7"
        >
            <div v-if="measurements.length > 0" class="space-y-2">
                <label for="measurement" class="block text-sm font-semibold text-gray-900">Messepoche</label>
                <select
                    id="measurement"
                    v-model="selectedMeasurementId"
                    class="w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option v-for="measurement in measurements" :key="measurement.id" :value="measurement.id">
                        {{ measurement.name }} ({{ measurement.datetime }})
                    </option>
                </select>
            </div>

            <p v-else class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">
                Für dieses Projekt gibt es keine Messepochen.
            </p>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Link
                    :href="show(project.id)"
                    class="rounded-md px-4 py-2.5 text-center text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                >
                    Abbrechen
                </Link>
                <button
                    type="submit"
                    :disabled="selectedMeasurementId === null"
                    class="rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    CSV exportieren
                </button>
            </div>
        </form>
    </div>
</template>
