import { User } from '@/@types/user';

export interface MeasurementValue {
    measurementId: number;
    height: number;
    lat: number;
    lon: number;
}

export interface Point {
    id: number;
    name: string;
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
    // null if no projection
    projectedDistance: number | null;
    deltaHeight: number;
}

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
