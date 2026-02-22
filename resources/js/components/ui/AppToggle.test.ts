import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import AppToggle from './AppToggle.vue';

test('renders the label text', () => {
    // creates an instance of this component
    const wrapper = mount(AppToggle, {
        props: {
            modelValue: false,
            label: 'Toggle label',
        },
    });
    expect(wrapper.text()).toContain('Toggle label');
});

test('checkbox is unchecked when modelValue is false', () => {
    const wrapper = mount(AppToggle, {
        props: {
            modelValue: false,
            label: 'Toggle label',
        },
    });

    const checkbox = wrapper.get('input[type="checkbox"]');
    expect((checkbox.element as HTMLInputElement).checked).toBe(false);
});

test('checkbox is checked when modelValue is true', () => {
    const wrapper = mount(AppToggle, {
        props: {
            modelValue: true,
            label: 'Toggle label',
        },
    });

    const checkbox = wrapper.get('input[type="checkbox"]');
    expect((checkbox.element as HTMLInputElement).checked).toBe(true);
});

test('emits update:modelValue when toggled', async () => {
    // async as the DOM updates asynchronously
    const wrapper = mount(AppToggle, {
        props: {
            modelValue: true,
            label: 'Toggle label',
        },
    });

    const checkbox = wrapper.get('input[type="checkbox"]');
    await checkbox.setValue(false);

    /**
     * Claude 4.6, 2026-02-22
     * "[...] And quickly, how do you test if an event was emitted? [...]"
     */

    // Should have emitted an event called 'update:modelValue'
    expect(wrapper.emitted()).toHaveProperty('update:modelValue');
    // Exactly one event should have been emitted
    expect(wrapper.emitted('update:modelValue')).toHaveLength(1);
    // The first (0th) emitted event should have had its argument set to false
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([false]);
});
