import { expect, test } from 'vitest';
import { ref } from 'vue';
import { useSortableData } from './useSortableData';

function makeTestData() {
    return ref([
        { name: 'Charlie', age: 19 },
        { name: 'Alice', age: 18 },
        { name: 'Bob', age: 21 },
    ]);
}

test('should return unsorted data when no column is selected', () => {
    const data = makeTestData();
    const { sorted: sortedData } = useSortableData(data);

    expect(sortedData.value.map((d) => d.name)).toEqual(['Charlie', 'Alice', 'Bob']);
});

test('should sort ascending, then descending, then unsort after third click when allowUnsort is true', () => {
    const data = makeTestData();
    const { sorted: sortedData, handleSort } = useSortableData(data);

    // Sort by name ascending
    handleSort('name');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Alice', 'Bob', 'Charlie']);

    // Sort by name descending
    handleSort('name');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Charlie', 'Bob', 'Alice']);

    // Unsort
    handleSort('name');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Charlie', 'Alice', 'Bob']);
});

test('should toggle between ascending and descending when allowUnsort is false', () => {
    const data = makeTestData();
    const { sorted: sortedData, handleSort } = useSortableData(data, false);

    // Sort by name ascending
    handleSort('name');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Alice', 'Bob', 'Charlie']);

    // Sort by name descending
    handleSort('name');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Charlie', 'Bob', 'Alice']);

    // Toggle back to ascending
    handleSort('name');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Alice', 'Bob', 'Charlie']);
});

test('should sort by age (numeric column) correctly', () => {
    const data = makeTestData();
    const { sorted: sortedData, handleSort } = useSortableData(data);

    // Sort by age ascending
    handleSort('age');
    expect(sortedData.value.map((d) => d.age)).toEqual([18, 19, 21]);

    // Sort by age descending
    handleSort('age');
    expect(sortedData.value.map((d) => d.age)).toEqual([21, 19, 18]);
});

test('should switch sort column and reset sort direction', () => {
    const data = makeTestData();
    const { sortColumn, sortDirection, handleSort } = useSortableData(data);

    handleSort('name');
    handleSort('name');
    handleSort('age');

    expect(sortColumn.value).toBe('age');
    expect(sortDirection.value).toBe('asc');
});

test('accept initial sort column and direction', () => {
    const data = makeTestData();
    const { sorted: sortedData, sortColumn, sortDirection } = useSortableData(data, true, 'name', 'desc');

    expect(sortColumn.value).toBe('name');
    expect(sortDirection.value).toBe('desc');
    expect(sortedData.value.map((d) => d.name)).toEqual(['Charlie', 'Bob', 'Alice']);
});
