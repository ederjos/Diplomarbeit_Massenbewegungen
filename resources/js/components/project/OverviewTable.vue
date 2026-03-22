<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

import type { ProjectOverview } from '@/types/project';

// works thanks to laravel wayfinder, no hardcoded URL
import { show, toggleFavorite } from '@/actions/App/Http/Controllers/ProjectController';
import AppTableWrapper from '@/components/ui/AppTableWrapper.vue';
import { formatDate } from '@/utils/date';

withDefaults(
    defineProps<{
        projects: ProjectOverview[];
        sortColumn: keyof ProjectOverview | null;
        sortDirection?: 'asc' | 'desc';
    }>(),
    {
        sortDirection: 'asc',
    },
);

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
        <template v-if="projects.length">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="show(project.id)"
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
                <td class="px-4 py-4 text-center">
                    <button
                        type="button"
                        :title="project.isFavorite ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'"
                        :aria-label="
                            project.isFavorite
                                ? `Projekt ${project.name} aus Favoriten entfernen`
                                : `Projekt ${project.name} zu Favoriten hinzufügen`
                        "
                        @click="handleToggleFavorite($event, project.id)"
                    >
                        <Star
                            class="h-5 w-5"
                            :class="
                                project.isFavorite
                                    ? 'fill-amber-400 stroke-amber-400'
                                    : 'stroke-gray-300 hover:stroke-amber-400'
                            "
                        />
                    </button>
                </td>
            </Link>
        </template>
        <tr v-else>
            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Keine Projekte vorhanden.</td>
        </tr>
    </AppTableWrapper>
</template>
