<script setup lang="ts">
import { ref, watch } from 'vue';
import type { ChartDisplacements, MapDisplacements, Measurement, Point } from '@/@types/measurement';
import { displacementsForPair } from '@/actions/App/Http/Controllers/ProjectController';
import DisplacementChart from '@/components/chart/DisplacementChart.vue';
import LeafletMap from '@/components/map/LeafletMap.vue';
import CommentsList from '@/components/measurement/CommentsList.vue';
import ErrorBoundary from '@/components/ui/ErrorBoundary.vue';

const props = defineProps<{
    projectId: number;
    points: Point[];
    pointColors: Record<number, string>;
    measurements: Measurement[];
    initialReferenceId: number | null;
    initialComparisonId: number | null;
    initialMapDisplacements: MapDisplacements;
    chartDisplacements: ChartDisplacements;
}>();

const selectedReference = ref<number | null>(props.initialReferenceId);
const selectedComparison = ref<number | null>(props.initialComparisonId);
const mapDisplacements = ref<MapDisplacements>(props.initialMapDisplacements);

let fetchController: AbortController | null = null;

/**
 * Claude Sonnet 4.6, 2026-02-28
 * "Wenn sich die Referenz- oder Vergleichsepoche ändert, dann sollen die URL-Parameter aktualisiert werden."
 * (Simon)
 */
// When the selected epochs change, fetch new displacements and update the URL
watch([selectedReference, selectedComparison], async ([refVal, compVal]) => {
    // Update URL query params for shareability (without navigation)
    const params = new URLSearchParams(window.location.search);

    if (refVal != null) {
        params.set('reference', String(refVal));
    } else {
        params.delete('reference');
    }

    if (compVal != null) {
        params.set('comparison', String(compVal));
    } else {
        params.delete('comparison');
    }

    history.replaceState(null, '', `${window.location.pathname}?${params}`);

    // Fetch new displacements from the API
    if (refVal != null && compVal != null) {
        fetchController?.abort();
        fetchController = new AbortController();

        const url = displacementsForPair.url(props.projectId, {
            query: { reference: refVal, comparison: compVal },
        });

        try {
            const response = await fetch(url, { signal: fetchController.signal });
            if (!response.ok) return;
            mapDisplacements.value = await response.json();
        } catch (error) {
            // Ignore abort errors (expected when a newer request supersedes)
            if (error instanceof DOMException && error.name === 'AbortError') return;
            console.error('Failed to fetch map displacements:', error);
        }
    }
});
</script>

<template>
    <section class="grid grid-cols-1 gap-4 p-4">
        <section>
            <ErrorBoundary component-name="Karte">
                <LeafletMap
                    :points="points"
                    :point-colors="pointColors"
                    :measurements="measurements"
                    :reference-id="selectedReference"
                    :comparison-id="selectedComparison"
                    :displacements="mapDisplacements"
                    @update:reference-id="selectedReference = $event"
                    @update:comparison-id="selectedComparison = $event"
                />
            </ErrorBoundary>
        </section>

        <section class="flex justify-center">
            <ErrorBoundary component-name="Zeitachse">
                <DisplacementChart
                    :points="points"
                    :point-colors="pointColors"
                    :measurements="measurements"
                    :displacements="chartDisplacements"
                />
            </ErrorBoundary>
        </section>

        <section class="rounded-lg bg-white p-6 shadow-md">
            <CommentsList :measurements="measurements" :comparison-id="selectedComparison" />
        </section>
    </section>
</template>
