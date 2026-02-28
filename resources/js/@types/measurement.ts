import type { User } from '@/@types/user';

export interface MeasurementValue {
    measurementId: number;
    height: number;
    lat: number;
    lon: number;
}

export interface BasePoint {
    id: number;
    name: string;
}

export interface Point extends BasePoint {
    measurementValues: MeasurementValue[];
    axis: ProjectionAxis | null;
}

export interface ProjectionAxis {
    startLat: number;
    startLon: number;
    vectorLat: number;
    vectorLon: number;
}

export interface PointDisplacement {
    // all values in cm
    distance2d: number;
    distance3d: number;
    deltaHeight: number;
    // null if no projection
    projectedDistance: number | null;
}

// Type of the mapDisplacements object returned by ProjectController::show()
// Key: point id
export type MapDisplacements = Record<number, PointDisplacement>;

// Type of the chartDisplacements object returned by ProjectController::show()
// Outer key: point id, inner key: measurement id
export type ChartDisplacements = Record<number, Record<number, PointDisplacement>>;

export interface DisplacementRow {
    pointId: number;
    name: string;
    // distance 2d or projection
    distance2dOrProjection: number;
    distance3d: number;
    deltaHeight: number;
}

interface Comment {
    id: number;
    content: string;
    createdDatetime: string;
    updatedDatetime: string;
    user: User;
}

export interface BaseMeasurement {
    id: number;
    name: string;
    datetime: string;
}

export interface Measurement extends BaseMeasurement {
    comments: Comment[];
}
