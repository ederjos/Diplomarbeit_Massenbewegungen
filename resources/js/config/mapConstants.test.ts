import {
    DEFAULT_VECTOR_SCALE,
    DEFAULT_ZOOM_LEVEL,
    HIGHLIGHT_DURATION_MS,
    MAP_BOUNDS_PADDING,
    MARKER_BORDER_OPACITY,
    MARKER_CIRCLE_RADIUS,
    MARKER_FILL_OPACITY,
    MAX_MAP_ZOOM,
    MAX_VECTOR_SCALE,
    MIN_MAP_ZOOM,
    MIN_VECTOR_SCALE,
    POINT_FOCUS_ZOOM_LEVEL,
    POLYLINE_FRAME_WEIGHT,
    POLYLINE_MAIN_WEIGHT,
} from './mapConstants';
import { expect, test } from 'vitest';

test('mapConstants has valid zoom range', () => {
    expect(MIN_MAP_ZOOM).toBeLessThan(MAX_MAP_ZOOM);
    expect(DEFAULT_ZOOM_LEVEL).toBeGreaterThanOrEqual(MIN_MAP_ZOOM);
    expect(DEFAULT_ZOOM_LEVEL).toBeLessThanOrEqual(MAX_MAP_ZOOM);
});

test('mapConstants has valid vector scale range', () => {
    expect(MIN_VECTOR_SCALE).toBeLessThan(MAX_VECTOR_SCALE);
    expect(DEFAULT_VECTOR_SCALE).toBeGreaterThanOrEqual(MIN_VECTOR_SCALE);
    expect(DEFAULT_VECTOR_SCALE).toBeLessThanOrEqual(MAX_VECTOR_SCALE);
});

test('mapConstants has positive map bounds padding', () => {
    expect(MAP_BOUNDS_PADDING[0]).toBeGreaterThan(0);
    expect(MAP_BOUNDS_PADDING[1]).toBeGreaterThan(0);
});

test('mapConstants has valid marker opacities between 0 and 1', () => {
    expect(MARKER_BORDER_OPACITY).toBeGreaterThan(0);
    expect(MARKER_BORDER_OPACITY).toBeLessThanOrEqual(1);
    expect(MARKER_FILL_OPACITY).toBeGreaterThan(0);
    expect(MARKER_FILL_OPACITY).toBeLessThanOrEqual(1);
});

test('mapConstants has positive visual dimensions', () => {
    expect(MARKER_CIRCLE_RADIUS).toBeGreaterThan(0);
    expect(POLYLINE_FRAME_WEIGHT).toBeGreaterThan(0);
    expect(POLYLINE_MAIN_WEIGHT).toBeGreaterThan(0);
});

test('mapConstants frame polyline is thicker than main (outline effect)', () => {
    expect(POLYLINE_FRAME_WEIGHT).toBeGreaterThan(POLYLINE_MAIN_WEIGHT);
});

test('mapConstants POINT_FOCUS_ZOOM_LEVEL is higher than default zoom', () => {
    expect(POINT_FOCUS_ZOOM_LEVEL).toBeGreaterThan(DEFAULT_ZOOM_LEVEL);
});

test('mapConstants has a positive highlight duration', () => {
    expect(HIGHLIGHT_DURATION_MS).toBeGreaterThan(0);
});