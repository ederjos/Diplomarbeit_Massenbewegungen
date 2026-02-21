import { expect, test } from 'vitest';
import { colors } from './colors';

test('colors is a non-empty array', () => {
    expect(Array.isArray(colors)).toBe(true);
    expect(colors.length).toBeGreaterThan(0);
});

test('colors contains only valid hex color strings', () => {
    for (const color of colors) {
        expect(color).toMatch(/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/);
    }
});

test('colors contains unique values', () => {
    const uniqueColors = new Set(colors);
    expect(uniqueColors.size).toBe(colors.length);
});
