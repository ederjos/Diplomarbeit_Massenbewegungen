<script lang="ts" setup>
import { Measurement } from '@/@types/measurement';
import { formatDate } from '@/utils/date';
import { computed } from 'vue';

const props = defineProps<{
    measurements: Measurement[];
    referenceId: number | null;
}>();

const selectedComparison = defineModel<number | null>('selectedComparison');
const vectorScale = defineModel<number>('vectorScale');
const isGaitLine = defineModel<boolean>('isGaitLine');

/** Display name for the fixed reference epoch */
const referenceLabel = computed(() => {
    if (!props.referenceId) return 'Nicht gesetzt';
    const m = props.measurements.find((m) => m.id === props.referenceId);
    return m ? `${m.name} (${formatDate(m.datetime)})` : 'Unbekannt';
});
</script>

<template>
    <div class="z-10 flex shrink-0 items-center gap-4 bg-white p-4 shadow">
        <div>
            <label class="mb-1 block text-sm font-bold">Referenzepoche</label>
            <span class="inline-block rounded border bg-gray-100 px-2 py-1 text-sm text-gray-700">
                {{ referenceLabel }}
            </span>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold">Vergleichsepoche</label>
            <select
                v-model.number="selectedComparison"
                class="rounded border p-1 disabled:text-gray-400"
                :disabled="isGaitLine"
            >
                <option v-for="m in props.measurements" :key="m.id" :value="m.id">
                    {{ m.name }} ({{ formatDate(m.datetime) }})
                </option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold" for="inputVectorScale">Vektormaßstab M 1&nbsp;:&nbsp;</label>
            <input v-model.number="vectorScale" type="number" class="w-28 rounded border p-1" min="1" max="100000" id="inputVectorScale" />
        </div>
        <div class="me-4 flex items-center">
            <input
                type="checkbox"
                v-model="isGaitLine"
                id="checkboxIsGaitLine"
                class="border-default-medium bg-neutral-secondary-medium h-4 w-4 rounded-xs border"
            />
            <label class="text-heading ml-2 block text-sm font-bold select-none" for="checkboxIsGaitLine"
                >Ganglinie</label
            >
        </div>
    </div>
</template>
