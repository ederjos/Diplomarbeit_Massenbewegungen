<script lang="ts" setup>
import { Measurement } from '@/@types/measurement';
import { formatDate } from '@/utils/date';
import { computed } from 'vue';

const props = defineProps<{
    measurements: Measurement[];
    comparisonId: number | null;
}>();

const selectedComparisonMeasurement = computed(() => {
    if (!props.comparisonId) return null;
    return props.measurements.find((measurement) => measurement.id === props.comparisonId) ?? null;
});
</script>

<template>
    <!--
    GPT-5.3-Codex, 2026-02-13
    "Taking a look at measurement.ts and then Project.vue, please insert a list of all comments regarding the currently selected comparison measurement showing all data that is loaded, i.e. content, created, updated, author name, author role."
    -->
    <h2 class="mb-4 text-xl font-bold text-slate-700">
        Kommentare zur Messepoche {{ selectedComparisonMeasurement?.name }}
    </h2>
    <p v-if="selectedComparisonMeasurement" class="mb-4 text-sm text-slate-600">
        {{ selectedComparisonMeasurement.name }}
        ({{ formatDate(selectedComparisonMeasurement.datetime) }})
    </p>

    <p v-if="!selectedComparisonMeasurement" class="text-slate-500">Keine Vergleichsepoche ausgewählt.</p>

    <p v-else-if="selectedComparisonMeasurement.comments.length === 0" class="text-slate-500">
        Keine Kommentare für diese Messepoche vorhanden.
    </p>

    <div v-else class="space-y-4">
        <article
            v-for="comment in selectedComparisonMeasurement.comments"
            :key="comment.id"
            class="rounded-md border border-slate-200 bg-slate-50 p-4"
        >
            <p class="mb-3 whitespace-pre-wrap text-slate-800">{{ comment.content }}</p>
            <div class="grid gap-1 text-sm text-slate-600 md:grid-cols-2">
                <p>
                    <span class="font-semibold text-slate-700">Erstellt:</span>
                    {{ new Date(comment.created_datetime).toLocaleString('de-AT') }}
                </p>
                <p>
                    <span class="font-semibold text-slate-700">Aktualisiert:</span>
                    {{ new Date(comment.updated_datetime).toLocaleString('de-AT') }}
                </p>
                <p>
                    <span class="font-semibold text-slate-700">Autor:</span>
                    {{ comment.user.name }}
                </p>
                <p>
                    <span class="font-semibold text-slate-700">Rolle:</span>
                    {{ comment.user.role?.name || '—' }}
                </p>
            </div>
        </article>
    </div>
</template>
