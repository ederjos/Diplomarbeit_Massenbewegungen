<script setup lang="ts">
const isChecked = defineModel<boolean>({ required: true });
defineProps<{
    label: string;
}>();
</script>

<template>
    <label class="flex cursor-pointer items-center gap-3">
        <input type="checkbox" v-model="isChecked" class="sr-only" />
        <div
            class="relative h-6 w-11 rounded-full transition-colors"
            :class="isChecked ? 'bg-indigo-600' : 'bg-gray-300'"
        >
            <div
                class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white transition-transform"
                :class="{ 'translate-x-5': isChecked }"
            ></div>
        </div>
        <span class="text-sm text-gray-700">{{ label }}</span>
    </label>
</template>
