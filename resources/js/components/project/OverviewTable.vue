<script setup lang="ts">
import { ProjectOverview } from '@/@types/project';
import { formatDate } from '@/utils/date';
import { Link } from '@inertiajs/vue3';
import AppTableWrapper from '../ui/AppTableWrapper.vue';

defineProps<{
    projects: ProjectOverview[];
    sortColumn: keyof ProjectOverview | null;
    sortDirection: 'asc' | 'desc';
}>();

// https://vuejs.org/api/sfc-script-setup.html#defineprops-defineemits
// Emit called sort-by with the column to sort by when a header is clicked
// Gets a function "sort" with parameter "column"
const emit = defineEmits<{
    'sort-by': [column: keyof ProjectOverview];
}>();
</script>

<template>
    <!--
    Claude Sonnet 4.5, 2026-02-13
    "Now, give this table a good Tailwind design again."
    -->
    <AppTableWrapper
        :columns="[
            { label: 'ID', columnName: 'id' },
            { label: 'Name', columnName: 'name' },
            { label: 'Letzte Messung', columnName: 'lastMeasurement' },
            { label: 'Nächste Messung', columnName: 'nextMeasurement' },
        ]"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        table-class="min-w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm"
        thead-class="bg-gray-50"
        tbody-class="divide-y divide-gray-200"
        th-class="px-6 py-3"
        @sort-by="(col) => emit('sort-by', col)"
    >
        <Link
            as="tr"
            v-for="project in projects"
            :key="project.id"
            :href="`/projects/${project.id}`"
            class="cursor-pointer transition-colors hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset"
            :title="project.isActive ? 'Aktives Projekt' : 'Inaktives Projekt'"
        >
            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ project.id }}
            </td>
            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">
                {{ project.name }}
            </td>
            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ formatDate(project.lastMeasurement) }}
            </td>
            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                {{ project.isActive ? formatDate(project.nextMeasurement) : '-' }}
            </td>
        </Link>
    </AppTableWrapper>
</template>
