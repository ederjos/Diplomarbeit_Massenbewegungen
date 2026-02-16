<script setup lang="ts">
import { DisplacementDistanceMode, DisplacementRow } from '@/@types/measurement';
import { DISPLACEMENT_TABLE_WIDTH } from '@/config/mapConstants';
import AppTableWrapper from '../ui/AppTableWrapper.vue';

const props = withDefaults(
    defineProps<{
        displacementRows: DisplacementRow[];
        highlightedPointId?: number | null;
        sortColumn: keyof DisplacementRow | null;
        sortDirection: 'asc' | 'desc';
    }>(),
    {
        highlightedPointId: null,
    },
);

const displacementMode = defineModel<DisplacementDistanceMode>('displacementMode', { required: true });

// Quote "succinct" way to define emits
const emit = defineEmits<{
    'select-point': [pointId: number];
    'sort-by': [column: keyof DisplacementRow];
}>();

/**
 * Displacement display mode (for table):
 *   '2D'         — Option A: √(dX² + dY²) Pythagoras 2D
 *   'projection' — Option B: Dot product on normalized axis
 *   '3D'         — Option C: √(dX² + dY² + dZ²) Pythagoras 3D
 */
const displacementModeLabels: Record<DisplacementDistanceMode, string> = {
    distance2d: '2D Lage',
    projectedDistance: 'Projektion',
    distance3d: '3D Gesamt',
};
</script>

<template>
    <div
        class="z-10 h-full shrink-0 overflow-y-auto border-l bg-gray-50 p-4 shadow-lg"
        :class="DISPLACEMENT_TABLE_WIDTH"
    >
        <h2 class="mb-3 text-lg font-bold">Verschiebungen</h2>
        <div class="mb-3">
            <label class="mb-1 block text-sm font-bold">Darstellungsart</label>
            <div class="flex gap-1">
                <button
                    v-for="(label, mode) in displacementModeLabels"
                    :key="mode"
                    @click="displacementMode = mode"
                    :aria-pressed="displacementMode === mode"
                    class="rounded px-2 py-1 text-xs transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-40"
                    :class="
                        displacementMode === mode ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 hover:bg-gray-200'
                    "
                >
                    {{ label }}
                </button>
            </div>
        </div>
        <AppTableWrapper
            :columns="[
                { label: 'Punkt', columnName: 'name' },
                { label: `Δ ${displacementModeLabels[displacementMode]} [cm]`, columnName: 'displayDistance' },
                { label: 'Δ Höhe [cm]', columnName: 'deltaHeight' },
            ]"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            tableClass="relative w-full border-collapse text-left text-sm"
            theadClass="sticky top-0 z-10 border-b bg-gray-100 text-xs uppercase shadow-sm"
            @sort-by="(col) => emit('sort-by', col)"
        >
            <tr
                v-for="p in props.displacementRows"
                :key="p.pointId"
                class="cursor-pointer border-b bg-white transition-colors odd:bg-gray-50 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset"
                :class="{ 'leaflet-row-highlight': p.pointId === props.highlightedPointId }"
                @click="emit('select-point', p.pointId)"
            >
                <td class="px-3 py-2 font-medium text-gray-900">
                    {{ p.name }}
                    <span
                        v-if="displacementMode === 'projectedDistance' && !p.hasProjection"
                        class="text-xs text-amber-500"
                        title="Keine Projektionsachse"
                        >⚠</span
                    >
                </td>
                <td class="px-3 py-2 text-right tabular-nums">{{ p.displayDistance.toFixed(4) }}</td>
                <td class="px-3 py-2 text-right tabular-nums">
                    {{ p.deltaHeight > 0 ? '+' : '' }}{{ p.deltaHeight.toFixed(4) }}
                </td>
            </tr>
            <tr v-if="props.displacementRows.length === 0">
                <td colspan="3" class="px-3 py-4 text-center text-gray-500">Keine Daten für die Auswahl</td>
            </tr>
        </AppTableWrapper>
    </div>
</template>

<style scoped>
.leaflet-row-highlight {
    animation: leaflet-highlight 1s both;
}

@keyframes leaflet-highlight {
    0% {
        transform: none;
        background-color: transparent; /* yellow-200 */
    }
    50%,
    60% {
        transform: scale(1.02);
        background-color: rgba(254, 240, 138, 1);
    }
    100% {
        transform: none;
        background-color: transparent;
    }
}
</style>
