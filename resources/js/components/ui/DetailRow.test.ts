import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import DetailRow from './DetailRow.vue';

test('renders the label and value text', () => {
    // creates an instance of this component
    const wrapper = mount(DetailRow, {
        props: {
            label: 'Name',
            value: 'Test Content',
        },
    });
    expect(wrapper.text()).toContain('Name');
    expect(wrapper.text()).toContain('Test Content');
});

test('should render slot content when provided instead of value prop', () => {
    const wrapper = mount(DetailRow, {
        props: {
            label: 'Name',
            value: 'Test Content',
        },
        slots: {
            default: '<b>Slot Content</b>',
        },
    });

    // Shows the slot content
    expect(wrapper.html()).toContain('<b>Slot Content</b>');
    // And doesn't show the value prop content
    expect(wrapper.text()).not.toContain('Test Content');
});

test('should render a table row with two cells', () => {
    const wrapper = mount(DetailRow, {
        props: {
            label: 'Anzahl',
            value: 100,
        },
    });

    expect(wrapper.findAll('tr')).toHaveLength(1);
    // two td elements should be rendered
    expect(wrapper.findAll('td')).toHaveLength(2);
});
