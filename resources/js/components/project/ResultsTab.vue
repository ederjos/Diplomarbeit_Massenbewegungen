<script setup lang="ts">
import { computed, ref } from 'vue';
import type {
    BasePoint,
    DisplacementsByPointAndMeasurement,
    Measurement,
    Point,
    PointDisplacement,
} from '@/@types/measurement';
import DisplacementChart from '@/components/chart/DisplacementChart.vue';
import LeafletMap from '@/components/map/LeafletMap.vue';
import CommentsList from '@/components/measurement/CommentsList.vue';
import ErrorBoundary from '@/components/ui/ErrorBoundary.vue';

const props = defineProps<{
    points: Point[];
    pointColors: Record<number, string>;
    measurements: Measurement[];
    displacements: DisplacementsByPointAndMeasurement;
}>();

// chart only needs base point info
const basePoints = computed(() => props.points as BasePoint[]);

// temp. workaround until Josef updates LeafletMap
const tempReferenceId = ref(1);
const tempComparisonId = ref(28);
const tempDisplacements = computed(() => {
    const displacements: Record<number, PointDisplacement> = {};
    for (const point of props.points) {
        const pointDisplacements = props.displacements[point.id]?.[tempComparisonId.value];
        if (!pointDisplacements) {
            continue;
        }
        displacements[point.id] = pointDisplacements;
    }
    return displacements;
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
                    :reference-id="tempReferenceId"
                    :comparison-id="tempComparisonId"
                    :displacements="tempDisplacements"
                />
            </ErrorBoundary>
        </section>

        <section class="flex justify-center">
            <ErrorBoundary component-name="Zeitachse">
                <DisplacementChart
                    :points="basePoints"
                    :point-colors="pointColors"
                    :measurements="measurements"
                    :displacements="displacements"
                />
            </ErrorBoundary>
        </section>

        <section class="rounded-lg bg-white p-6 shadow-md">
            <CommentsList :measurements="measurements" :comparison-id="tempComparisonId" />
        </section>
    </section>
</template>
