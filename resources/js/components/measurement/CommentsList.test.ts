import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import type { Measurement } from '@/@types/measurement';
import CommentsList from './CommentsList.vue';

function makeMeasurementWithoutComments(): Measurement {
    return {
        id: 1,
        name: 'Test Measurement',
        datetime: '2025-01-01',
        comments: [],
    };
}

function makeMeasurementWithComments(): Measurement {
    const measurement = makeMeasurementWithoutComments();
    measurement.comments = [
        {
            id: 1,
            content: 'First comment',
            createdDatetime: '2026-01-01',
            updatedDatetime: '2026-01-01',
            user: {
                id: 1,
                name: 'Josef',
                role: { id: 1, name: 'admin' },
            },
        },
    ];
    return measurement;
}

test('shows "no comparison selected" when comparisonId is null', () => {
    const wrapper = mount(CommentsList, {
        props: {
            measurements: [makeMeasurementWithComments()],
            comparisonId: null,
        },
    });

    expect(wrapper.text()).toContain('Keine Vergleichsepoche ausgewählt');
});

test('shows "no comments" when measurement has no comments', () => {
    const wrapper = mount(CommentsList, {
        props: {
            measurements: [makeMeasurementWithoutComments()],
            comparisonId: 1,
        },
    });

    expect(wrapper.text()).toContain('Keine Kommentare');
});

test('shows comment when measurement has comments', () => {
    const wrapper = mount(CommentsList, {
        props: {
            measurements: [makeMeasurementWithComments()],
            comparisonId: 1,
        },
    });

    // Comment's content
    expect(wrapper.text()).toContain('First comment');
    // Author's name
    expect(wrapper.text()).toContain('Josef');
    // Author's role
    expect(wrapper.text()).toContain('admin');
});

test('shows measurement name in heading', () => {
    const wrapper = mount(CommentsList, {
        props: {
            measurements: [makeMeasurementWithComments()],
            comparisonId: 1,
        },
    });

    expect(wrapper.find('h2').text()).toBe('Kommentare zur Messepoche Test Measurement');
});
