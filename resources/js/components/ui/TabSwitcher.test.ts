import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import TabSwitcher from './TabSwitcher.vue';

test('renders two buttons with appropriate labels', () => {
    // creates an instance of this component
    const wrapper = mount(TabSwitcher, {
        props: {
            activeTab: 'results',
        },
    });
    const buttons = wrapper.findAll('button');
    // Exactly two buttons with correct labels
    expect(buttons).toHaveLength(2);
    // Left button: Results
    expect(buttons[0].text()).toBe('Ergebnisse');
    // Right button: Basics
    expect(buttons[1].text()).toBe('Grundlagen');
});

test('emits update:activeTab when clicking results', async () => {
    const wrapper = mount(TabSwitcher, {
        props: {
            activeTab: 'basics',
        },
    });

    // Click the "Ergebnisse" button
    await wrapper.findAll('button')[0].trigger('click');

    // The component should have emitted an event called 'update:activeTab'
    expect(wrapper.emitted()).toHaveProperty('update:activeTab');
    // Exactly one event should have been emitted
    expect(wrapper.emitted('update:activeTab')).toHaveLength(1);
    // The first (0th) emitted event should have had its argument set to 'results'
    expect(wrapper.emitted('update:activeTab')?.[0]).toEqual(['results']);
});

test('emits update:activeTab when clicking basics', async () => {
    const wrapper = mount(TabSwitcher, {
        props: {
            activeTab: 'results',
        },
    });

    // Click the "results" button
    await wrapper.findAll('button')[1].trigger('click');

    // The component should have emitted an event called 'update:activeTab'
    expect(wrapper.emitted()).toHaveProperty('update:activeTab');
    // Exactly one event should have been emitted
    expect(wrapper.emitted('update:activeTab')).toHaveLength(1);
    // The first (0th) emitted event should have had its argument set to 'results'
    expect(wrapper.emitted('update:activeTab')?.[0]).toEqual(['basics']);
});
