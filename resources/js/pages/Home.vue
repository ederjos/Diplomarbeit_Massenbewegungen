<script setup lang="ts">
import { ProjectOverview } from '@/@types/project';
import OverviewTable from '@/components/project/OverviewTable.vue';
import AppToggle from '@/components/ui/AppToggle.vue';
import { useSortableData } from '@/composables/useSortableData';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Get data directly from inertia without an additional API call
const props = defineProps<{
    projects: ProjectOverview[];
}>();

const showOnlyActive = ref(false);

/**
 * Gemini 3 Pro, 2026-01-01
 * "In this table I want you to add arrows in the thead part where the user can select by which column it is ordered and if its descending or ascending. Also, there should be a Checkbox with better design (like toggles in tailwind) that selects if only active projects will be displayed or if all the projects should be shown."
 */

// Filter logic separate from sorting
const filteredProjects = computed(() => {
    return showOnlyActive.value ? props.projects.filter((project) => project.isActive) : props.projects;
});

// Use composable for sorting logic
const { sortColumn, sortDirection, sorted: displayedProjects, handleSort } = useSortableData(filteredProjects);
</script>

<template>
    <Head title="Home" />
    <AuthenticatedLayout>
        <h1 class="text-2xl font-bold">Projekte</h1>
        <div class="flex w-full max-w-4xl flex-col gap-4">
            <div class="flex items-center justify-end">
                <AppToggle v-model="showOnlyActive" label="Nur aktive Projekte anzeigen" />
            </div>
            <OverviewTable
                :projects="displayedProjects"
                :sort-column="sortColumn"
                :sort-direction="sortDirection"
                @sort-by="handleSort"
            />
        </div>
    </AuthenticatedLayout>
</template>
