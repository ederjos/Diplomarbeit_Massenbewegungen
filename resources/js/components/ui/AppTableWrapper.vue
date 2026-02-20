<script setup lang="ts" generic="T">
import SortableHeader from '@/components/ui/SortableHeader.vue';

/**
 * Claude 4.5 Sonnet, 2026-02-16
 * "What would be the cleanest approach to ensure type safety for the column names?"
 */

type TableLabel<T> = {
    label: string;
    // null for non-sortable columns
    columnName: keyof T | null;
};

const props = withDefaults(
    defineProps<{
        columns: TableLabel<T>[];
        // Type T inferred by sortColumn, which is used in SortableHeader emits
        sortColumn: keyof T | null;
        sortDirection: 'asc' | 'desc';
        tableClass?: string;
        theadClass?: string;
        tbodyClass?: string;
        thClass?: string;
    }>(),
    {
        tableClass: 'relative w-full border-collapse text-left text-sm',
        theadClass: 'sticky top-0 z-10 border-b bg-gray-100 text-xs uppercase shadow-sm',
        tbodyClass: '',
        thClass: '',
    },
);

const emit = defineEmits<{
    'sort-by': [column: keyof T];
}>();
</script>

<template>
    <!--
    Claude Sonnet 4.5, 2026-02-15
    "please improve the tailwind style of SortableHeader and all uses of SortableHeader where, depending on the situation, a few additional style changes can be applied."
    -->
    <table :class="props.tableClass">
        <thead :class="props.theadClass">
            <tr>
                <template v-for="column in props.columns" :key="column.columnName">
                    <SortableHeader
                        v-if="column.columnName !== null"
                        :label="column.label"
                        :is-active="props.sortColumn === column.columnName"
                        :direction="props.sortDirection"
                        :style-class="props.thClass"
                        @sort="emit('sort-by', column.columnName)"
                    />
                    <th
                        v-else
                        class="bg-gray-50 px-2 py-2 text-xs font-semibold text-gray-600 uppercase select-none"
                        :class="props.thClass"
                    >
                        {{ column.label }}
                    </th>
                </template>
            </tr>
        </thead>
        <tbody :class="props.tbodyClass">
            <slot></slot>
        </tbody>
    </table>
</template>
