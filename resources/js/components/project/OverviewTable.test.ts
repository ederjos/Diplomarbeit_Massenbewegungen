import { mount } from '@vue/test-utils';
import { test, expect, vi } from 'vitest';
import type { ProjectOverview } from '@/@types/project';
import OverviewTable from './OverviewTable.vue';

// mock the Link component as we assume it works correctly and we don't want to test it here
// adjusted from https://vitest.dev/guide/mocking
vi.mock('@inertiajs/vue3', () => ({
    Link: { template: '<tr><slot /></tr>', props: ['href', 'as', 'method'] },
    router: { post: vi.fn() },
}));

vi.mock('@/actions/App/Http/Controllers/ProjectController', () => ({
    show: { url: (id: number) => `/projects/${id}` },
}));

vi.mock('@/routes/project', () => ({
    toggleFavorite: (id: number) => `/projects/${id}/favorite`,
}));

const projects: ProjectOverview[] = [
    {
        id: 1,
        name: 'Project 1',
        isActive: true,
        lastMeasurement: '2026-01-01',
        nextMeasurement: '2026-02-01',
        isFavorite: false,
    },
    {
        id: 2,
        name: 'Project 2',
        isActive: false,
        lastMeasurement: '2025-01-01',
        nextMeasurement: null,
        isFavorite: true,
    },
];

test('all project names are rendered', () => {
    const wrapper = mount(OverviewTable, {
        props: {
            projects,
            sortColumn: null,
        },
    });
    expect(wrapper.text()).toContain('Project 1');
    expect(wrapper.text()).toContain('Project 2');
});

test('shows "-" for nextMeasurement of inactive projects', () => {
    const wrapper = mount(OverviewTable, {
        props: {
            projects,
            sortColumn: null,
        },
    });
    const rows = wrapper.findAll('tbody tr');
    expect(rows[1].text()).toContain('-');
});

test('emits "sort-by" with correct column when header is clicked', async () => {
    const wrapper = mount(OverviewTable, {
        props: {
            projects,
            sortColumn: null,
        },
    });
    const headers = wrapper.findAll('th');
    await headers[0].trigger('click');
    expect(wrapper.emitted('sort-by')?.[0]).toEqual(['id']);
    await headers[2].trigger('click');
    expect(wrapper.emitted('sort-by')?.[1]).toEqual(['lastMeasurement']);
});
