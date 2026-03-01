import { expect, test } from 'vitest';
import { DEFAULT_WMS_OPTIONS, MAP_LAYERS, WMS_LAYERS } from './mapLayers';

test('MAP_LAYERS has valid VOGIS URLs', () => {
    expect(MAP_LAYERS.vogis.schummerung).toContain('vogis.cnv.at');
    expect(MAP_LAYERS.vogis.luftbilder).toContain('vogis.cnv.at');
});

test('DEFAULT_WMS_OPTIONS has expected format', () => {
    expect(DEFAULT_WMS_OPTIONS.format).toBe('image/png');
    expect(DEFAULT_WMS_OPTIONS.transparent).toBe(true);
});

test('WMS_LAYERS is a non-empty array with valid entries', () => {
    expect(WMS_LAYERS.length).toBeGreaterThan(0);

    for (const layer of WMS_LAYERS) {
        expect(layer.label).toBeTruthy();
        expect(layer.url).toContain('https://');
        expect(layer.layers).toBeTruthy();
    }
});
