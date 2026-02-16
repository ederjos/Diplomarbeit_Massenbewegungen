<script setup lang="ts">
import { BaseMeasurement } from '@/@types/measurement';
import { MAX_VECTOR_SCALE, MIN_VECTOR_SCALE } from '@/config/mapConstants';
import { formatDate } from '@/utils/date';
import { computed } from 'vue';

const props = defineProps<{
    measurements: BaseMeasurement[];
}>();

const selectedReference = defineModel<number | null>('selectedReference');
const selectedComparison = defineModel<number | null>('selectedComparison');
const vectorScale = defineModel<number>('vectorScale');
const isGaitLine = defineModel<boolean>('isGaitLine');

// Prevent the user from entering invalid vector scales:
const safeVectorScale = computed({
    get() {
        return vectorScale.value ?? MIN_VECTOR_SCALE;
    },
    set(value: number | null) {
        if (value === null || !Number.isFinite(value) || value < MIN_VECTOR_SCALE) {
            vectorScale.value = MIN_VECTOR_SCALE;
            return;
        }

        vectorScale.value = Math.min(Math.max(Math.floor(value), MIN_VECTOR_SCALE), MAX_VECTOR_SCALE);
    },
});
</script>

<template>
    <div class="z-10 flex shrink-0 items-center gap-4 bg-white p-4 shadow">
        <div>
            <label for="reference-select" class="mb-1 block text-sm font-bold">Referenzepoche</label>
            <select
                v-model.number="selectedReference"
                class="rounded border p-1 disabled:text-gray-400"
                :disabled="isGaitLine"
                :aria-disabled="isGaitLine"
                id="reference-select"
            >
                <option v-for="m in props.measurements" :key="m.id" :value="m.id">
                    {{ m.name }} ({{ formatDate(m.datetime) }})
                </option>
            </select>
        </div>
        <div>
            <label for="comparison-select" class="mb-1 block text-sm font-bold">Vergleichsepoche</label>
            <!-- aria-disabled -> accessibility announcement -> tells screen readers it's disabled -->
            <select
                v-model.number="selectedComparison"
                class="rounded border p-1 disabled:text-gray-400"
                :disabled="isGaitLine"
                :aria-disabled="isGaitLine"
                id="comparison-select"
            >
                <option v-for="m in props.measurements" :key="m.id" :value="m.id">
                    {{ m.name }} ({{ formatDate(m.datetime) }})
                </option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold" for="input-vector-scale">Vektormaßstab M 1&nbsp;:&nbsp;</label>
            <input
                v-model.number="safeVectorScale"
                type="number"
                class="w-28 rounded border p-1"
                :min="MIN_VECTOR_SCALE"
                :max="MAX_VECTOR_SCALE"
                id="input-vector-scale"
            />
        </div>
        <div class="me-4 flex items-center">
            <input
                type="checkbox"
                v-model="isGaitLine"
                id="checkbox-is-gait-line"
                class="border-default-medium bg-neutral-secondary-medium h-4 w-4 rounded-xs border"
            />
            <label class="text-heading ml-2 block text-sm font-bold select-none" for="checkbox-is-gait-line"
                >Ganglinie</label
            >
        </div>
    </div>
</template>
