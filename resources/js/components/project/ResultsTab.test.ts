import { mount } from '@vue/test-utils';
import { test, expect, vi } from 'vitest';
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
    displacementsForPair: { url: () => 'projects/1/displacements' },
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
