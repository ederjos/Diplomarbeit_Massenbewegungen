import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import SortableHeader from './SortableHeader.vue';

test('renders the label text', () => {
    // creates an instance of this component
    const wrapper = mount(SortableHeader, {
        props: {
            label: 'Name',
            isActive: false,
            direction: 'asc',
        },
    });
    expect(wrapper.text()).toContain('Name');
});

test('emits sort event when clicked', async () => {
    const wrapper = mount(SortableHeader, {
        props: {
            label: 'Name',
            column: 'name',
            isActive: false,
            direction: 'asc',
        },
    });

    await wrapper.find('th').trigger('click');

    // The component should have emitted an event called 'sort'
    expect(wrapper.emitted()).toHaveProperty('sort');
    // It should have been emitted exactly once
    expect(wrapper.emitted('sort')).toHaveLength(1);
});

test('emits sort event when Enter key is pressed', async () => {
    const wrapper = mount(SortableHeader, {
        props: {
            label: 'Name',
            column: 'name',
            isActive: false,
            direction: 'asc',
        },
    });

    await wrapper.find('th').trigger('keydown.enter');

    // The component should have emitted an event called 'sort'
    expect(wrapper.emitted()).toHaveProperty('sort');
    // It should have been emitted exactly once
    expect(wrapper.emitted('sort')).toHaveLength(1);
});
