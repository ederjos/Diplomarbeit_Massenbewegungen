<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';

defineProps<{
    label: string;
    isActive: boolean;
    direction: 'asc' | 'desc';
}>();

const emit = defineEmits<{
    sort: [];
}>();
</script>

<template>
    <th
        @click="emit('sort')"
        class="group cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase select-none hover:bg-gray-100"
    >
        <div class="flex items-center gap-1">
            {{ label }}
            <ArrowUp v-if="isActive && direction == 'asc'" class="h-4 w-4 text-indigo-600" />
            <ArrowDown v-else-if="isActive && direction == 'desc'" class="h-4 w-4 text-indigo-600" />
            <ArrowUpDown v-else class="h-4 w-4 text-gray-400 opacity-0 group-hover:opacity-100" />
        </div>
    </th>
</template>
