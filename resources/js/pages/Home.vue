<script setup lang="ts">
import { ProjectOverview } from '@/@types/project';
import ActiveProjectsToggle from '@/components/ActiveProjectsToggle.vue';
import ProjectsTable from '@/components/ProjectsTable.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Get data directly from inertia without an additional API call
const props = defineProps<{
    projects: ProjectOverview[];
}>();

// only properties of Project allowed
const sortColumn = ref<keyof ProjectOverview | null>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');
const showOnlyActive = ref(false);

/**
 * Gemini 3 Pro, 2026-01-01
 * "In this table I want you to add arrows in the thead part where the user can select by which column it is ordered and if its descending or ascending. Also, there should be a Checkbox with better design (like toggles in tailwind) that selects if only active projects will be displayed or if all the projects should be shown."
 */

const displayedProjects = computed(() => {
    let result = props.projects;

    // Filter active projects if needed
    if (showOnlyActive.value) {
        result = result.filter((project) => project.isActive);
    }

    // Sort if a column is selected
    if (sortColumn.value) {
        // Create a copy of the array to avoid mutating the original
        // props should be treated as immutable, Source: https://vuejs.org/guide/components/props.html#one-way-data-flow
        result = [...result].sort((a, b) => {
            // -1 -> a before b
            // 0 -> unchanged
            // 1 -> b before a

            // Because of the if above, we can be sure that sortColumn.value is not null
            const valueA = a[sortColumn.value!];
            const valueB = b[sortColumn.value!];

            // Handle null/undefined values
            // Should technically never happen
            if (valueA == null && valueB == null) return 0;
            if (valueA == null) return 1;
            if (valueB == null) return -1;

            // Compare values
            let comparison = 0;
            if (valueA > valueB) comparison = 1;
            if (valueA < valueB) comparison = -1;

            // Apply sort direction
            return sortDirection.value === 'asc' ? comparison : -comparison;
        });
    }

    return result;
});

function handleSort(column: keyof ProjectOverview) {
    if (sortColumn.value == column) {
        // Toggle direction in the same column
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Different column, start with ascending
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
}
</script>

<template>
    <Head title="Home" />
    <AuthenticatedLayout>
        <h1 class="text-2xl font-bold">Projekte</h1>
        <div class="flex w-full max-w-4xl flex-col gap-4">
            <div class="flex items-center justify-end">
                <ActiveProjectsToggle v-model="showOnlyActive" />
            </div>
            <ProjectsTable
                :projects="displayedProjects"
                :sort-column="sortColumn"
                :sort-direction="sortDirection"
                @sort-by="handleSort"
            />
        </div>
    </AuthenticatedLayout>
</template>
