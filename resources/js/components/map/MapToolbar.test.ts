import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import type { BaseMeasurement } from '@/@types/measurement';
import MapToolbar from './MapToolbar.vue';

const measurements: BaseMeasurement[] = [
    { id: 1, name: 'Measurement 1', datetime: '2025-01-01T00:00:00' },
    { id: 2, name: 'Measurement 2', datetime: '2026-01-01T00:00:00' },
];

test('measurement options are rendered in selects', () => {
    const wrapper = mount(MapToolbar, {
        props: {
            measurements,
            selectedReference: 1,
            selectedComparison: 2,
            vectorScale: 100,
            isGaitLine: false,
        },
    });
    const options = wrapper.findAll('option');
    // 2 for reference, 2 for comparison
    expect(options).toHaveLength(4);
    expect(options[0].text()).toBe('Measurement 1 (1.1.2025)');
    expect(options[1].text()).toBe('Measurement 2 (1.1.2026)');
    expect(options[2].text()).toBe('Measurement 1 (1.1.2025)');
    expect(options[3].text()).toBe('Measurement 2 (1.1.2026)');
});

test('selects are disabled when isGaitLine is true', () => {
    const wrapper = mount(MapToolbar, {
        props: {
            measurements,
            selectedReference: 1,
            selectedComparison: 2,
            vectorScale: 100,
            isGaitLine: true,
        },
    });
    const selects = wrapper.findAll('select');
    expect(selects).toHaveLength(2);
    expect(selects[0].attributes('disabled')).toBeDefined();
    expect(selects[1].attributes('disabled')).toBeDefined();
});

test('emits "update:isGaitLine" when checkbox is toggled', async () => {
    const wrapper = mount(MapToolbar, {
        props: {
            measurements,
            selectedReference: 1,
            selectedComparison: 2,
            vectorScale: 100,
            isGaitLine: false,
        },
    });
    const checkbox = wrapper.find('input[type="checkbox"]');
    await checkbox.setValue(true);
    expect(wrapper.emitted('update:isGaitLine')?.[0]).toEqual([true]);
});
