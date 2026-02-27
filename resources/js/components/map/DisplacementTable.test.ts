import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import type { DisplacementRow, Measurement } from '@/@types/measurement';
import DisplacementTable from './DisplacementTable.vue';

const rows: DisplacementRow[] = [
    { pointId: 123, name: 'P1', distance2dOrProjection: 1.23, distance3d: 2.45, deltaHeight: -0.5 },
    { pointId: 200, name: 'P2', distance2dOrProjection: 3.67, distance3d: 4.89, deltaHeight: 1.2 },
];

test('the table renders all displacement rows', () => {
    const wrapper = mount(DisplacementTable, {
        props: {
            displacementRows: rows,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });

    expect(wrapper.text()).toContain('P1');
    expect(wrapper.html()).toContain('P2');
});

test('the table shows "no data" when there are no rows', () => {
    const wrapper = mount(DisplacementTable, {
        props: {
            displacementRows: [],
            sortColumn: null,
            sortDirection: 'asc',
        },
    });

    expect(wrapper.text()).toContain('Keine Daten');
});

test('positive deltaHeights get a "+" sign', () => {
    const wrapper = mount(DisplacementTable, {
        props: {
            displacementRows: rows,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });

    expect(wrapper.text()).toContain('+1.2');
});

test('emits "select-point" when a row is clicked', async () => {
    const wrapper = mount(DisplacementTable, {
        props: {
            displacementRows: rows,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });
    // tr is a direct child of tbody (ignore thead)
    const tableRows = wrapper.findAll('tbody>tr');
    await tableRows[0].trigger('click');

    // Automatically tests for if the emitted event exists and was emitted (at least) once
    expect(wrapper.emitted('select-point')?.[0]).toEqual([123]);
});

test('emits "sort-by" when a header is clicked', async () => {
    const wrapper = mount(DisplacementTable, {
        props: {
            displacementRows: rows,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });

    const headers = wrapper.findAll('th');
    await headers[0].trigger('click');

    // First column in the visible table is the point name
    expect(wrapper.emitted('sort-by')?.[0]).toEqual(['name']);
});

test('the correct row is highlighted', () => {
    const wrapper = mount(DisplacementTable, {
        props: {
            displacementRows: rows,
            highlightedPointId: 200,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });

    const tableRows = wrapper.findAll('tbody>tr');

    // The second row should be selected
    expect(tableRows[1].classes()).toContain('leaflet-row-highlight');
    // The first row must not be selected
    expect(tableRows[0].classes()).not.toContain('leaflet-row-highlight');
});
