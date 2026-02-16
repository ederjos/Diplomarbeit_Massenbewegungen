<script setup lang="ts">
import {
    BaseMeasurement,
    DisplacementDistanceMode,
    DisplacementRow,
    Measurement,
    Point,
    PointDisplacement,
} from '@/@types/measurement';
import DisplacementTable from '@/components/map/DisplacementTable.vue';
import MapToolbar from '@/components/map/MapToolbar.vue';
import { useLeafletMap } from '@/composables/useLeafletMap';
import { useSortableData } from '@/composables/useSortableData';
import { DEFAULT_VECTOR_SCALE, HIGHLIGHT_DURATION_MS } from '@/config/mapConstants';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, toRef, watch } from 'vue';

/** -- Use PHP Configs in Vue --
 * import { usePage } from '@inertiajs/vue3';
 * const spatial = usePage().props.spatial as any;
 * If we ever need the srid from the backend
 * Requires "'spatial' => config('spatial')," in Middleware/HandleInertiaRequests.php
 * Source: https://inertiajs.com/docs/v2/data-props/shared-data
 */

const props = defineProps<{
    points: Point[];
    // like hash map
    pointColors: Record<number, string>;
    measurements: Measurement[];
    /** Fixed reference measurement ID (Bezugsepoche, set per project by Admin/Editor) */
    referenceId: number | null;
    /** Selected comparison measurement ID */
    comparisonId: number | null;
    /** Backend-computed displacement values. Key: point id */
    displacements: Record<number, PointDisplacement>;
}>();

/**
 * Gemini 3 Pro, 2025-12-02
 * "this component works greatly. now, we want to rearrange the design though and add a new table. in this table there should be a col for point name, one for delta pos (x and y) and one for delta height (z). keep in mind that the values in the Measurement interface of x,y,z are in the epsg 31254"
 */

const mapContainer = ref<HTMLDivElement | null>(null);
// const map = ref<L.Map | null>(null);
// former selectedMeasurement
const selectedComparison = ref<number | null>(props.comparisonId);
const vectorScale = ref<number>(DEFAULT_VECTOR_SCALE);
const isGaitLine = ref<boolean>(false);
const selectedPointId = ref<number | null>(null);

// Table state for displacement table
const displacementMode = ref<DisplacementDistanceMode>('distance2d');
// If two points are clicked quickly after one another, the highlight animation should restart
let highlightTimeout: number | null = null;
// ResizeObserver detects size changes. More reliable than onmount of the table
let resizeObserver: ResizeObserver | null = null;

// Composable: All the Leaflet logic is in useLeafletMap
// Quote, Vue.js: "toRef() is useful when you want to pass the ref of a prop to a composable function."
const { initMap, fitBounds, zoomToPoint, invalidateMap, drawMap } = useLeafletMap(
    toRef(props.points),
    toRef(props.pointColors),
    toRef(props.referenceId),
    selectedComparison,
    vectorScale,
    isGaitLine,
    handlePointClick,
);

/**
 * Claude Opus 4.6, 2026-02-11
 * "[...] Then, apply the projection changes to the LeafletComponent file, so that the user can select the display mode for the displacements (2D, projection, 3D) and the map updates accordingly."
 */

// Trigger Inertia visit when comparison epoch changes
// Use inertia to ensure SPA experience and preserve scroll/state, but still update the URL for shareability and back button support
watch(selectedComparison, (newComp) => {
    if (newComp) {
        router.get(window.location.pathname, { comparison: newComp }, { preserveScroll: true, preserveState: true });
    }
});

/**
 * Extract only base measurement data (id, name, datetime) without comments.
 * MapToolbar doesn't need the full Measurement objects.
 */
const baseMeasurements = computed<BaseMeasurement[]>(() => {
    return props.measurements.map((m) => ({
        id: m.id,
        name: m.name,
        datetime: m.datetime,
    }));
});

/**
 * Displacement table data — uses pre-computed backend values.
 * Also: Don't give DisplacementTable more data than necessary
 */
const unsortedDisplacementRows = computed<DisplacementRow[]>(() => {
    // Don't compute if no comparison selected or table not shown
    if (!props.referenceId || !selectedComparison.value || isGaitLine.value) {
        return [];
    }

    return props.points
        .map((point) => {
            const displacement = props.displacements[point.id];
            if (!displacement) return null;
            // compute displayDistance according to current mode once here
            const rawDisplayDistance = displacement[displacementMode.value];
            const displayDistance = rawDisplayDistance ?? displacement.distance2d;

            const result: DisplacementRow = {
                pointId: point.id,
                name: point.name,
                distance2d: displacement.distance2d,
                distance3d: displacement.distance3d,
                projectedDistance: displacement.projectedDistance,
                deltaHeight: displacement.deltaHeight,
                displayDistance: displayDistance,
                hasProjection: displacement.projectedDistance !== null,
            };

            return result;
        })
        // filters all null out (points w/o data for this epoch) & guarantees that there are no nulls
        // Also tells TS now the type is DisplacementRow w/o "| null"
        .filter((row): row is DisplacementRow => row !== null);
});

// Use composable for sorting with custom comparison
const { sortColumn, sortDirection, sorted: displacementRows, handleSort } = useSortableData(
    unsortedDisplacementRows
);

function handlePointClick(pointId: number) {
    zoomToPoint(pointId);

    // Set selected point id, so the table can highlight the row.
    // Clear any pending highlight timeout so successive clicks don't race.
    if (highlightTimeout) {
        clearTimeout(highlightTimeout);
        highlightTimeout = null;
    }
    selectedPointId.value = pointId;

    // Matches CSS animation (1s + 100ms buffer)
    highlightTimeout = window.setTimeout(() => {
        // Only clear if the timeout is still the latest
        selectedPointId.value = null;
        highlightTimeout = null;
    }, HIGHLIGHT_DURATION_MS);
}

// Plain prop array wouldn't trigger updates -> watch with toRef to make it reactive
// also watch for changes deep inside the points array, e.g. new measurements being added to an epoch
watch([toRef(props.points), selectedComparison, vectorScale, isGaitLine], () => drawMap(), { deep: true });

onMounted(() => {
    // If no map can be found, there's nothing we can do.
    if (!mapContainer.value) return;

    initMap(mapContainer.value);

    // Initial setup
    if (props.points.length > 0) {
        if (!selectedComparison.value && props.measurements.length > 1) {
            selectedComparison.value = props.measurements[props.measurements.length - 1].id;
        }
        drawMap();
        fitBounds();
    }

    // Watch for map container size changes to replace the old workaround.
    resizeObserver = new ResizeObserver(invalidateMap);
    resizeObserver.observe(mapContainer.value);
});

onUnmounted(() => {
    if (highlightTimeout) {
        clearTimeout(highlightTimeout);
        highlightTimeout = null;
    }
    // Stop observing the map
    resizeObserver?.disconnect();
});
</script>

<template>
    <div class="flex h-full w-full flex-col overflow-hidden">
        <MapToolbar
            :measurements="baseMeasurements"
            :reference-id="props.referenceId"
            v-model:selected-comparison="selectedComparison"
            v-model:vector-scale="vectorScale"
            v-model:is-gait-line="isGaitLine"
        />

        <!-- The map is always 85% high, no matter the table size -->
        <div class="flex h-[85vh] overflow-hidden">
            <div ref="mapContainer" class="relative z-0 h-full flex-1"></div>

            <!-- Table is only shown if there is a selected comparison epoch -->
            <DisplacementTable
                v-if="!isGaitLine"
                :displacement-rows="displacementRows"
                :highlighted-point-id="selectedPointId"
                v-model:displacement-mode="displacementMode"
                :sort-column="sortColumn"
                :sort-direction="sortDirection"
                @select-point="handlePointClick"
                @sort-by="handleSort"
            />
        </div>
    </div>
</template>
