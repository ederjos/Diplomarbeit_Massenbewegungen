/**
 * Map and visualization constants
 * Centralized configuration for Leaflet map, displacement table, and related UI components
 */

/**
 * Claude Sonnet 4.5, 2026-02-15
 * "Please extract all the magic numbers and constants from the LeafletComponent and its sub-components to this file."
 */

// ============================================
// VECTOR SCALE CONFIGURATION
// ============================================

/** Default vector scale multiplier for displacement arrows */
export const DEFAULT_VECTOR_SCALE = 100;

/** Minimum allowed vector scale (1:1) */
export const MIN_VECTOR_SCALE = 1;

/** Maximum allowed vector scale (1:100000) */
export const MAX_VECTOR_SCALE = 100_000;

// ============================================
// MAP ZOOM & POSITIONING
// ============================================

/** Minimum zoom level for map tiles */
export const MIN_MAP_ZOOM = 9;

/** Maximum zoom level for map tiles */
export const MAX_MAP_ZOOM = 19;

/** Default map center coordinates [latitude, longitude] - Bregenz, Austria */
export const DEFAULT_MAP_CENTER: [number, number] = [47.5, 9.75];

/** Default zoom level when map is initialized */
export const DEFAULT_ZOOM_LEVEL = 13;

/** Zoom level when focusing on a specific point */
export const POINT_FOCUS_ZOOM_LEVEL = 17;

/** Map bounds padding [horizontal, vertical] in pixels */
export const MAP_BOUNDS_PADDING: [number, number] = [50, 50];

// ============================================
// MAP VISUAL STYLING
// ============================================

/** Radius of circle markers for measurement points (in pixels) */
export const MARKER_CIRCLE_RADIUS = 4;

/** Weight (thickness) of polyline frame/outline (in pixels) */
export const POLYLINE_FRAME_WEIGHT = 4;

/** Weight (thickness) of main polyline (in pixels) */
export const POLYLINE_MAIN_WEIGHT = 2;

/** Color for polyline frame/outline */
export const POLYLINE_FRAME_COLOR = 'white';

/** Color for main polyline */
export const POLYLINE_MAIN_COLOR = 'black';

/** Color for circle marker border */
export const MARKER_BORDER_COLOR = 'gray';

/** Fallback color when point doesn't have assigned color */
export const MARKER_FALLBACK_COLOR = 'gray';

/** Opacity of marker border (0-1) */
export const MARKER_BORDER_OPACITY = 0.5;

/** Fill opacity of marker (0-1) */
export const MARKER_FILL_OPACITY = 0.8;

/** Weight (thickness) of marker border (in pixels) */
export const MARKER_BORDER_WEIGHT = 1;

// ============================================
// UI INTERACTION TIMING
// ============================================

/** Duration of row highlight animation in displacement table (milliseconds) */
export const HIGHLIGHT_DURATION_MS = 1100;

// ============================================
// TABLE DIMENSIONS
// ============================================

/** Width of displacement table sidebar (Tailwind class) */
export const DISPLACEMENT_TABLE_WIDTH = 'w-96'; // 24rem / 384px
