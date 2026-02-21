<script setup lang="ts">
import { ProjectOverview } from '@/@types/project';
import { formatDate } from '@/utils/date';
import { Link, router } from '@inertiajs/vue3';
import AppTableWrapper from '../ui/AppTableWrapper.vue';
// works thanks to laravel wayfinder, no hardcoded URL
import { show } from '@/actions/App/Http/Controllers/ProjectController';
import { toggleFavorite } from '@/routes/project';

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

function handleToggleFavorite(event: Event, projectId: number) {
    event.preventDefault();
    // Also prevent parent handlers (like the Link!)
    event.stopPropagation();
    router.post(
        toggleFavorite(projectId),
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}
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
            { label: 'Favorit', columnName: null },
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
            v-for="project in projects"
            :key="project.id"
            :href="show.url(project.id)"
            :title="project.isActive ? 'Aktives Projekt' : 'Inaktives Projekt'"
            :aria-label="`Projekt ${project.name} öffnen`"
            class="cursor-pointer transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset"
            :class="project.isFavorite ? 'bg-amber-50 hover:bg-amber-100' : 'hover:bg-gray-50'"
            as="tr"
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
            <!--
            Claude Sonnet 4.6, 2026-02-20
            "Edit this th to have a star icon for selecting whether a project is a favorite."
            -->
            <td class="px-4 py-4 text-center" @click="handleToggleFavorite($event, project.id)">
                <button :title="project.isFavorite ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'">
                    <!-- filled star -->
                    <svg
                        v-if="project.isFavorite"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-amber-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118L10 14.347l-3.95 2.679c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.064 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z"
                        />
                    </svg>
                    <!-- outline star -->
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-gray-300 hover:text-amber-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                        />
                    </svg>
                </button>
            </td>
        </Link>
    </AppTableWrapper>
</template>
