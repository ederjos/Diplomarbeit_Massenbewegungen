import type { WMSOptions } from 'leaflet';

// Base URLs for map services
export const MAP_LAYERS = {
    vogis: {
        schummerung: 'https://vogis.cnv.at/mapserver/mapserv?map=i_schummerung_2023_r_wms.map',
        luftbilder: 'https://vogis.cnv.at/mapserver/mapserv?map=i_luftbilder_r_wms.map',
    },
} as const;

// Default WMS layer options
export const DEFAULT_WMS_OPTIONS: WMSOptions = {
    format: 'image/png',
    transparent: true,
    maxZoom: 23,
    minZoom: 4,
    attribution: '&copy; VOGIS CNV',
};

// Available WMS layers configuration

export const WMS_LAYERS = [
    { label: 'Schummerung Oberfläche', url: MAP_LAYERS.vogis.schummerung, layers: 'schummerung_2023_oberflaeche_25cm' },
    { label: 'Schummerung Gelände', url: MAP_LAYERS.vogis.schummerung, layers: 'schummerung_2023_gelaende_25cm' },
    { label: 'Luftbild: Echtfarben Winter Mosaik 2024-25', url: MAP_LAYERS.vogis.luftbilder, layers: 'wi2024-25_20cm' },
    { label: 'Luftbild: 2018', url: MAP_LAYERS.vogis.luftbilder, layers: 'ef2018_10cm' },
    { label: 'Echtfarbenbild_2023_10cm_technisch', url: MAP_LAYERS.vogis.luftbilder, layers: 'ef2023_10cm_t' },
];
