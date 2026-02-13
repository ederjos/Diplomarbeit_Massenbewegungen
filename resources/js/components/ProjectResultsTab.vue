<script lang="ts" setup>
import { Measurement, Point, PointDisplacement } from '@/@types/measurement';
import LeafletComponent from './LeafletComponent.vue';
import ProjectTimeline from './ProjectTimeline.vue';
import MeasurementComments from './MeasurementCommentsList.vue';

defineProps<{
    points: Point[];
    pointColors: Record<number, string>;
    measurements: Measurement[];
    referenceId: number | null;
    comparisonId: number | null;
    displacements: Record<number, PointDisplacement>;
}>();
</script>

<template>
    <section class="grid grid-cols-1 gap-4 p-4">
        <section>
            <LeafletComponent :points="points" :point-colors="pointColors" :measurements="measurements"
                :reference-id="referenceId" :comparison-id="comparisonId" :displacements="displacements" />
        </section>

        <section class="flex justify-center">
            <ProjectTimeline :points="points" :point-colors="pointColors" :measurements="measurements" />
        </section>
        <section class="rounded-lg bg-white p-6 shadow-md">
            <MeasurementComments :measurements="measurements" :comparison-id="comparisonId" />
        </section>
    </section>
</template>
