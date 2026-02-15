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
    distance2d: number;
    distance3d: number;
    projectedDistance: number | null;
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
