import type { Ref } from 'vue';
import { computed, ref } from 'vue';

/**
 * Claude Sonnet 4.5, 2026-02-15
 * "Can this code be put into a composable to reuse it in Home and LeafletMap? [old sorting code from those two vue files]"
 * Rem: Adjusted by hand
 */

export function useSortableData<T>(
    data: Ref<T[]>,
    allowUnsort: boolean = true,
    initialColumn?: keyof T | null,
    initialDirection: 'asc' | 'desc' = 'asc',
) {
    const sortColumn = ref<keyof T | null>(initialColumn ?? null);
    const sortDirection = ref<'asc' | 'desc'>(initialDirection);

    const sorted = computed(() => {
        if (!sortColumn.value) return data.value;

        const column = sortColumn.value;

        return [...data.value].sort((a, b) => {
            let res = 0;

            // Default generic comparison
            const valueA = a[column as keyof T];
            const valueB = b[column as keyof T];

            // Handle null/undefined values (nulls go to the end)
            if (valueA === null || valueA === undefined) {
                if (valueB === null || valueB === undefined) return 0;
                return 1;
            }
            if (valueB === null || valueB === undefined) return -1;

            // String comparison
            if (typeof valueA === 'string' && typeof valueB === 'string') {
                res = valueA.localeCompare(valueB);
            }
            // Numeric comparison
            else if (typeof valueA === 'number' && typeof valueB === 'number') {
                res = valueA - valueB;
            }
            // Generic comparison as fallback
            else if (valueA > valueB) {
                res = 1;
            } else if (valueA < valueB) {
                res = -1;
            }

            return sortDirection.value === 'asc' ? res : -res;
        });
    });

    function handleSort(column: keyof T) {
        if (sortColumn.value === column) {
            if (allowUnsort) {
                // 3-state cycle: asc → desc → null
                if (sortDirection.value === 'asc') {
                    sortDirection.value = 'desc';
                } else {
                    sortColumn.value = null;
                }
            } else {
                // 2-state toggle: asc ↔ desc
                sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
            }
        } else {
            sortColumn.value = column;
            sortDirection.value = 'asc';
        }
    }

    return {
        sortColumn,
        sortDirection,
        sorted,
        handleSort,
    };
}
