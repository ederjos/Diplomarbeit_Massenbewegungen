import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';

import ResultsTab from './ResultsTab.vue';

vi.mock('@/components/map/LeafletMap.vue', () => ({
    default: { template: '<div class="leafletMapMock"></div>' },
}));

vi.mock('@/components/chart/DisplacementChart.vue', () => ({
    default: { template: '<div class="displacementChartMock"></div>' },
}));

vi.mock('@/components/measurement/CommentsList.vue', () => ({
    default: { template: '<div class="commentsListMock"></div>' },
}));

vi.mock('@/actions/App/Http/Controllers/ProjectController', () => ({
    index: Object.assign(() => '/projects', { definition: { url: '/projects' } }),
    displacementsForPair: { url: () => 'projects/1/displacements' },
    image: { url: (id: number) => `projects/${id}/image` },
}));

const defaultProps = {
    projectId: 1,
    points: [],
    pointColors: {},
    measurements: [],
    initialReferenceId: 1,
    initialComparisonId: 2,
    initialMapDisplacements: {},
    chartDisplacements: {},
};

test('all three mocked sections are rendered', () => {
    const wrapper = mount(ResultsTab, {
        props: defaultProps,
    });
    expect(wrapper.find('.leafletMapMock').exists()).toBe(true);
    expect(wrapper.find('.displacementChartMock').exists()).toBe(true);
    expect(wrapper.find('.commentsListMock').exists()).toBe(true);
});

test('renders without errors when no measurements are provided', () => {
    const wrapper = mount(ResultsTab, {
        props: {
            ...defaultProps,
            initialReferenceId: null,
            initialComparisonId: null,
        },
    });
    expect(wrapper.find('.leafletMapMock').exists()).toBe(true);
    expect(wrapper.find('.displacementChartMock').exists()).toBe(true);
    expect(wrapper.find('.commentsListMock').exists()).toBe(true);
});

test('renders project image with correct src', () => {
    const wrapper = mount(ResultsTab, { props: defaultProps });
    const img = wrapper.find('img');
    expect(img.exists()).toBe(true);
    expect(img.attributes('src')).toBe('projects/1/image');
});
