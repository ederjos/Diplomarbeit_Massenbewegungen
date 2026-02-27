import type { WMSOptions } from 'leaflet';
import { MAX_MAP_ZOOM, MIN_MAP_ZOOM } from '@/config/mapConstants';

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
    maxZoom: MAX_MAP_ZOOM,
    minZoom: MIN_MAP_ZOOM,
    attribution: '&copy; Land Vorarlberg',
};

// Available WMS layers configuration

export const WMS_LAYERS = [
    {
        label: 'Schummerung Oberfläche 2023',
        url: MAP_LAYERS.vogis.schummerung,
        layers: 'schummerung_2023_oberflaeche_25cm',
    },
    { label: 'Schummerung Gelände 2023', url: MAP_LAYERS.vogis.schummerung, layers: 'schummerung_2023_gelaende_25cm' },
    { label: 'Echtfarben-Orthofoto Winter 2024-25', url: MAP_LAYERS.vogis.luftbilder, layers: 'wi2024-25_20cm' },
    { label: 'Echtfarben-Orthofoto 2023 technisch', url: MAP_LAYERS.vogis.luftbilder, layers: 'ef2023_10cm_t' },
    { label: 'Echtfarben-Orthofoto 2022', url: MAP_LAYERS.vogis.luftbilder, layers: 'ef2022_10cm' },
];
