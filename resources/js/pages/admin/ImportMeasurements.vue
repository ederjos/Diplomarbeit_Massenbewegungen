<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

import type { BaseProject } from '@/types/project';

import { storeMeasurementImport } from '@/actions/App/Http/Controllers/AdminController';
import { show } from '@/actions/App/Http/Controllers/ProjectController';

defineProps<{
    project: BaseProject;
}>();

const selectedFileName = ref('');

function handleFileChange(event: Event, clearErrors: (...fields: string[]) => void) {
    const input = event.target as HTMLInputElement;
    selectedFileName.value = input.files?.[0]?.name ?? '';
    clearErrors('file');
}
</script>

<template>
    <Head title="Messungen importieren" />

    <div class="w-full max-w-3xl space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <p class="text-sm font-medium text-indigo-600">Projekt: {{ project.name }}</p>
                <h1 class="text-3xl font-bold text-gray-900">Messungen importieren</h1>
                <p class="max-w-2xl text-sm leading-6 text-gray-600">
                    Lade eine Messung als CSV ohne Kopfzeile hoch. Jede Zeile enthält einen Punkt im Format
                    <span class="font-mono text-gray-800">Punktname;X;Y;Z</span>.
                </p>
            </div>
            <Link :href="show(project.id)" class="text-sm font-medium text-gray-600 underline hover:text-gray-900">
                Zurück zum Projekt
            </Link>
        </div>

        <Form
            v-slot="{ errors, processing, progress, clearErrors }"
            :action="storeMeasurementImport.url(project.id)"
            method="post"
            class="space-y-6 rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-7"
        >
            <div class="grid gap-5 border-b border-gray-100 pb-6 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-semibold text-gray-900">Messungsname</label>
                    <input
                        id="name"
                        name="name"
                        required
                        maxlength="255"
                        autocomplete="off"
                        :placeholder="
                            project.previousMeasurementName ? `zuletzt: ${project.previousMeasurementName}` : ''
                        "
                        class="w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <label for="measurement_datetime" class="block text-sm font-semibold text-gray-900"
                        >Datum und Uhrzeit</label
                    >
                    <input
                        id="measurement_datetime"
                        name="measurement_datetime"
                        type="datetime-local"
                        required
                        class="w-full rounded-md border-gray-300 px-3 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="errors.measurement_datetime" class="text-sm text-red-600">
                        {{ errors.measurement_datetime }}
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <label for="file" class="block text-sm font-semibold text-gray-900">CSV-Datei</label>
                <div
                    class="rounded-md border border-dashed border-gray-300 bg-gray-50 p-4 transition-colors focus-within:border-indigo-500 focus-within:bg-indigo-50/40"
                >
                    <input
                        id="file"
                        name="file"
                        type="file"
                        accept=".csv,.txt,text/csv,text/plain"
                        required
                        class="block w-full cursor-pointer text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200"
                        @change="handleFileChange($event, clearErrors)"
                    />
                    <p class="mt-3 text-xs text-gray-500">
                        Semikolon als Trennzeichen, Punkt als Dezimalzeichen, maximal 10.000 Punkte.
                    </p>
                    <p v-if="selectedFileName" class="mt-2 truncate text-sm font-medium text-gray-800">
                        Ausgewählt: {{ selectedFileName }}
                    </p>
                </div>
                <p v-if="errors.file" class="text-sm text-red-600">{{ errors.file }}</p>
            </div>

            <progress
                v-if="progress"
                :value="progress.percentage"
                max="100"
                class="h-2 w-full overflow-hidden rounded-full"
            />

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Link
                    :href="show(project.id)"
                    class="rounded-md px-4 py-2.5 text-center text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                >
                    Abbrechen
                </Link>
                <button
                    type="submit"
                    :disabled="processing"
                    class="rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ processing ? 'Datei wird hochgeladen...' : 'Messung importieren' }}
                </button>
            </div>
        </Form>
    </div>
</template>
