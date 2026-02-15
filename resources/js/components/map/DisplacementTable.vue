<script setup lang="ts">
import { DisplacementRow } from '@/@types/measurement';
import { DISPLACEMENT_TABLE_WIDTH } from '@/config/mapConstants';
import { computed, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        displacementRows: DisplacementRow[];
        highlightedPointId?: number | null;
    }>(),
    {
        highlightedPointId: null,
    },
);

// Quote "succinct" way to define emits
const emit = defineEmits<{
    selectPoint: [pointId: number];
}>();

// Less complicated than enum
type DisplacementMode = 'twoD' | 'projection' | 'threeD';
// Doesn't change the map, so keep it here
const displacementMode = ref<DisplacementMode>('twoD');

/**
 * Displacement display mode (for table):
 *   'twoD'       — Option A: √(dX² + dY²) Pythagoras 2D
 *   'projection' — Option B: Dot product on normalized axis
 *   'threeD'     — Option C: √(dX² + dY² + dZ²) Pythagoras 3D
 */
const displacementModeLabels: Record<DisplacementMode, string> = {
    twoD: '2D',
    projection: 'Projektion',
    threeD: '3D',
};

/**
 * Displacement table data — uses pre-computed backend values.
 * No raw coordinates needed in the frontend.
 */
const pointDeltas = computed(() => {
    return props.displacementRows
        .map((row) => {
            let displayDistance: number;
            switch (displacementMode.value) {
                case 'projection':
                    // If no projection set, fallback to 2d (with warning icon)
                    displayDistance = row.projectedDistance ?? row.distance2d;
                    break;
                case 'threeD':
                    displayDistance = row.distance3d;
                    break;
                case 'twoD':
                default:
                    displayDistance = row.distance2d;
            }

            return {
                pointId: row.pointId,
                name: row.name,
                deltaHeight: row.deltaHeight,
                distance: displayDistance,
                hasProjection: row.projectedDistance !== null,
            };
            // filters all null out (points w/o data for this epoch) & guarantees that there are no nulls
        })
        .filter((p): p is NonNullable<typeof p> => p != null);
});
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
                    class="rounded px-2 py-1 text-xs transition-colors disabled:cursor-not-allowed disabled:opacity-40"
                    :class="displacementMode === mode ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'"
                >
                    {{ label }}
                </button>
            </div>
        </div>
        <table class="relative w-full border-collapse text-left text-sm">
            <thead class="top-0 z-10 border-b bg-gray-100 text-xs uppercase shadow-sm">
                <tr>
                    <th class="px-3 py-2 font-semibold text-gray-800">Punkt</th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-800">
                        Δ {{ displacementModeLabels[displacementMode] }} [cm]
                    </th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-800">Δ Höhe [cm]</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="p in pointDeltas"
                    :key="p.pointId"
                    class="cursor-pointer border-b bg-white odd:bg-gray-50 hover:bg-gray-100"
                    :class="{ 'leaflet-row-highlight': p.pointId == props.highlightedPointId }"
                    @click="emit('selectPoint', p.pointId)"
                >
                    <td class="px-3 py-2 font-medium text-gray-900">
                        {{ p.name }}
                        <span
                            v-if="displacementMode == 'projection' && !p.hasProjection"
                            class="text-xs text-amber-500"
                            title="Keine Projektionsachse"
                            >⚠</span
                        >
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">{{ p.distance.toFixed(4) }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">
                        {{ p.deltaHeight > 0 ? '+' : '' }}{{ p.deltaHeight.toFixed(4) }}
                    </td>
                </tr>
                <tr v-if="pointDeltas.length === 0">
                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">Keine Daten für die Auswahl</td>
                </tr>
            </tbody>
        </table>
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
