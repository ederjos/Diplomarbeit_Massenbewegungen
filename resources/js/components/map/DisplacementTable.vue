<script setup lang="ts">
import { DisplacementRow } from '@/@types/measurement';
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

// Quote "succinct" way to define emits
const emit = defineEmits<{
    'select-point': [pointId: number];
    'sort-by': [column: keyof DisplacementRow];
}>();
</script>

<template>
    <div
        class="z-10 h-full shrink-0 overflow-y-auto border-l bg-gray-50 p-4 shadow-lg"
        :class="DISPLACEMENT_TABLE_WIDTH"
    >
        <h3 class="mb-3 text-lg font-bold" id="displacement-heading">Verschiebungen</h3>
        <AppTableWrapper
            :columns="[
                { label: 'Punkt', columnName: 'name' },
                { label: `Δ2D Lage [cm]`, columnName: 'distance2dOrProjection' },
                { label: `ΔHöhe [cm]`, columnName: 'deltaHeight' },
                { label: 'ΔGesamt [cm]', columnName: 'distance3d' },
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
                <td class="px-3 py-2 font-medium text-gray-900">{{ p.name }}</td>
                <td class="px-3 py-2 text-right tabular-nums">{{ p.distance2dOrProjection.toFixed(1) }}</td>
                <td class="px-3 py-2 text-right tabular-nums">
                    {{ p.deltaHeight > 0 ? '+' : '' }}{{ p.deltaHeight.toFixed(1) }}
                </td>
                <td class="px-3 py-2 text-right tabular-nums">{{ p.distance3d.toFixed(1) }}</td>
            </tr>
            <tr v-if="props.displacementRows.length === 0">
                <td colspan="4" class="px-3 py-4 text-center text-gray-500">Keine Daten für die Auswahl</td>
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
        background-color: transparent;
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
