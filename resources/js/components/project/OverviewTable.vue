<script setup lang="ts">
import { ProjectOverview } from '@/@types/project';
import SortableHeader from '@/components/ui/SortableHeader.vue';
import { formatDate } from '@/utils/date';
import { Link } from '@inertiajs/vue3';

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
    <table class="min-w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <thead class="bg-gray-50">
            <tr>
                <SortableHeader
                    label="ID"
                    :is-active="sortColumn === 'id'"
                    :direction="sortDirection"
                    @sort="emit('sort-by', 'id')"
                />
                <!-- Syntax to call an emit -->
                <SortableHeader
                    label="Name"
                    :is-active="sortColumn === 'name'"
                    :direction="sortDirection"
                    @sort="emit('sort-by', 'name')"
                />
                <SortableHeader
                    label="Letzte Messung"
                    :is-active="sortColumn === 'lastMeasurement'"
                    :direction="sortDirection"
                    @sort="emit('sort-by', 'lastMeasurement')"
                />
                <SortableHeader
                    label="Nächste Messung"
                    :is-active="sortColumn === 'nextMeasurement'"
                    :direction="sortDirection"
                    @sort="emit('sort-by', 'nextMeasurement')"
                />
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <Link
                as="tr"
                v-for="project in projects"
                :key="project.id"
                :href="`/projects/${project.id}`"
                class="cursor-pointer hover:bg-gray-50"
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
        </tbody>
    </table>
</template>
