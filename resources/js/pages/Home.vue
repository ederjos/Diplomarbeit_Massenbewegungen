<script lang="ts" setup>
import { Project } from '@/@types/project';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue';

// Get data directly from inertia without an additional API call
const props = defineProps<{
    projects: Project[];
}>();

const sortColumn = ref<keyof Project | null>(null); // only properties of Project allowed: 'name', 'lastMeasurement', 'nextMeasurement'
const sortDirection = ref<'asc' | 'desc'>('asc');
const showOnlyActive = ref(false);

/**
 * Gemini 3 Pro, 2026-01-01
 * "In this table I want you to add arrows in the thead part where the user can select by which column it is ordered and if its descending or ascending. Also, there should be a Checkbox with better design (like toggles in tailwind) that selects if only active projects will be displayed or if all the projects should be shown."
 */

const sortedAndFilteredProjects = computed(() => {
    // First, filter for active projects if needed
    const filtered = showOnlyActive.value ? props.projects.filter((p) => p.isActive) : props.projects;

    // Then, sort based on the selected column and direction
    const key = sortColumn.value;
    if (!key) return filtered;

    // Use [...filtered] to create a copy before sorting
    return [...filtered].sort((a, b) => {
        // The comparison logic
        const valA = a[key];
        const valB = b[key];

        // Handle nulls/undefined
        if (valA == null && valB == null) return 0;
        if (valA == null) return 1;
        if (valB == null) return -1;

        if (valA === valB) return 0;

        const result = valA > valB ? 1 : -1;
        return sortDirection.value === 'asc' ? result : -result;
    });
});

const toggleSort = (column: keyof Project) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
};

const formatDate = (dateStr?: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString();
};
</script>

<template>
    <Head title="Home" />
    <AuthenticatedLayout>
        <h1 class="text-2xl font-bold">Projekte</h1>
        <div class="flex w-full max-w-4xl flex-col gap-4">
            <div class="flex items-center justify-end gap-2">
                <label class="inline-flex cursor-pointer items-center">
                    <!-- "peer" allows siblings to style themselves based on this input's state -->
                    <input type="checkbox" v-model="showOnlyActive" class="peer sr-only" />
                    <!-- Toggle background and circle (using after: pseudo-element) -->
                    <div
                        class="relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-indigo-600 peer-focus:ring-4 peer-focus:ring-indigo-300 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white"
                    ></div>
                    <span class="ml-3 text-sm font-medium text-gray-900">Nur aktive Projekte anzeigen</span>
                </label>
            </div>
            <table class="min-w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <!-- "group" allows children (like the arrow icon) to react when this header is hovered -->
                        <th
                            @click="toggleSort('name')"
                            class="group cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase select-none hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-1">
                                Name
                                <component
                                    :is="
                                        sortColumn === 'name'
                                            ? sortDirection === 'asc'
                                                ? ArrowUp
                                                : ArrowDown
                                            : ArrowUpDown
                                    "
                                    class="h-4 w-4"
                                    :class="
                                        sortColumn === 'name'
                                            ? 'text-indigo-600'
                                            : 'text-gray-400 opacity-0 group-hover:opacity-100'
                                    "
                                />
                            </div>
                        </th>
                        <th
                            @click="toggleSort('lastMeasurement')"
                            class="group cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase select-none hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-1">
                                Letzte Messung
                                <component
                                    :is="
                                        sortColumn === 'lastMeasurement'
                                            ? sortDirection === 'asc'
                                                ? ArrowUp
                                                : ArrowDown
                                            : ArrowUpDown
                                    "
                                    class="h-4 w-4"
                                    :class="
                                        sortColumn === 'lastMeasurement'
                                            ? 'text-indigo-600'
                                            : 'text-gray-400 opacity-0 group-hover:opacity-100'
                                    "
                                />
                            </div>
                        </th>
                        <th
                            @click="toggleSort('nextMeasurement')"
                            class="group cursor-pointer px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase select-none hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-1">
                                N&auml;chste Messung
                                <component
                                    :is="
                                        sortColumn === 'nextMeasurement'
                                            ? sortDirection === 'asc'
                                                ? ArrowUp
                                                : ArrowDown
                                            : ArrowUpDown
                                    "
                                    class="h-4 w-4"
                                    :class="
                                        sortColumn === 'nextMeasurement'
                                            ? 'text-indigo-600'
                                            : 'text-gray-400 opacity-0 group-hover:opacity-100'
                                    "
                                />
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <Link
                        as="tr"
                        v-for="project in sortedAndFilteredProjects"
                        :key="project.id"
                        :href="`/projects/${project.id}`"
                        class="cursor-pointer hover:bg-gray-50"
                        :title="project.isActive ? 'Aktives Projekt' : 'Inaktives Projekt'"
                    >
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
        </div>
    </AuthenticatedLayout>
</template>
