import { mount } from '@vue/test-utils';
import { test, expect, vi } from 'vitest';
import type { ProjectOverview } from '@/@types/project';
import Home from './Home.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div />' },
    Link: { template: '<tr><slot /></tr>', props: ['href', 'as', 'method'] },
    router: { post: vi.fn() },
    usePage: () => ({
        props: {
            auth: {
                user: {
                    id: 1,
                    name: 'Test User',
                    permissions: {
                        isAdmin: false,
                    },
                },
            },
        },
        url: '/',
    }),
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
        lastMeasurement: '2025-01-01',
        nextMeasurement: '2025-02-01',
        isFavorite: false,
    },
    {
        id: 2,
        name: 'Project 2',
        isActive: false,
        lastMeasurement: '2026-01-01',
        nextMeasurement: null,
        isFavorite: true,
    },
];

test('all projects are rendered by default', () => {
    const wrapper = mount(Home, {
        props: {
            projects,
        },
    });
    expect(wrapper.text()).toContain('Project 1');
    expect(wrapper.text()).toContain('Project 2');
});

test('only active projects are rendered when toggle is activated', async () => {
    const wrapper = mount(Home, {
        props: {
            projects,
        },
    });
    const checkboxShowOnlyActives = wrapper.find('input[type="checkbox"]');
    await checkboxShowOnlyActives.setValue(true);
    expect(wrapper.text()).toContain('Project 1');
    expect(wrapper.text()).not.toContain('Project 2');
});

test('favorites are displayed before non-favorites', () => {
    const wrapper = mount(Home, {
        props: {
            projects,
        },
    });
    const rows = wrapper.findAll('tbody tr');
    expect(rows[0].text()).toContain('Project 2');
    expect(rows[1].text()).toContain('Project 1');
});
