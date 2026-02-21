<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        label: string;
        isActive: boolean;
        direction: 'asc' | 'desc';
        styleClass?: string;
    }>(),
    {
        styleClass: '',
    },
);

const emit = defineEmits<{
    sort: [];
}>();
</script>

<template>
    <!--
    Claude Sonnet 4.5, 2026-02-15
    "please improve the tailwind style of SortableHeader and all uses of SortableHeader where, depending on the situation, a few additional style changes can be applied."
    -->
    <th
        :aria-sort="isActive ? (direction === 'asc' ? 'ascending' : 'descending') : 'none'"
        @click="emit('sort')"
        @keydown.enter="emit('sort')"
        @keydown.space.prevent="emit('sort')"
        class="group cursor-pointer bg-gray-50 px-2 py-2 text-xs font-semibold text-gray-600 uppercase transition-colors select-none hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset"
        :class="styleClass"
        tabindex="0"
        role="button"
    >
        <div class="flex items-center gap-1">
            {{ label }}
            <ArrowUp v-if="isActive && direction === 'asc'" class="h-4 w-4 text-indigo-600" />
            <ArrowDown v-else-if="isActive && direction === 'desc'" class="h-4 w-4 text-indigo-600" />
            <ArrowUpDown v-else class="h-4 w-4 text-gray-400 opacity-0 transition-opacity group-hover:opacity-100" />
        </div>
    </th>
</template>
