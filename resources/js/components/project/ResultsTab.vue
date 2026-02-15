<script setup lang="ts">
import { Measurement, Point, PointDisplacement } from '@/@types/measurement';
import ProjectTimeline from '@/components/chart/ProjectTimeline.vue';
import LeafletMap from '@/components/map/LeafletMap.vue';
import CommentsList from '@/components/measurement/CommentsList.vue';

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
            <LeafletMap
                :points="points"
                :point-colors="pointColors"
                :measurements="measurements"
                :reference-id="referenceId"
                :comparison-id="comparisonId"
                :displacements="displacements"
            />
        </section>

        <section class="flex justify-center">
            <ProjectTimeline :points="points" :point-colors="pointColors" :measurements="measurements" />
        </section>
        <section class="rounded-lg bg-white p-6 shadow-md">
            <CommentsList :measurements="measurements" :comparison-id="comparisonId" />
        </section>
    </section>
</template>
