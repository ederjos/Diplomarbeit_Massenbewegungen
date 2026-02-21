import { expect, test } from 'vitest';
import { formatDate } from './date';

test('formatDate formats ISO date string as date only', () => {
    const result = formatDate('2026-01-01T12:00:00Z', true, 'de-AT');
    expect(result).toBe('1.1.2026');
});

test('formatDate formats ISO date string with time', () => {
    const result = formatDate('2026-01-01T12:30:00Z', false, 'de-AT');
    expect(result).toBe('1.1.2026, 13:30:00');
});

test('formatDate defaults to date-only mode', () => {
    const result = formatDate('2026-01-01T12:00:00Z', undefined, 'de-AT');
    expect(result).toBe('1.1.2026');
});

test('formatDate returns "-" for invalid date string', () => {
    const result = formatDate('this-is-not-a-date', true, 'de-AT');
    expect(result).toBe('-');
});
