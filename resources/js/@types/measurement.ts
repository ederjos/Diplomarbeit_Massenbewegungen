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

export interface User {
    id: number;
    name: string;
    role: string;
}

interface Comment {
    id: number;
    content: string;
    created_datetime: string;
    updated_datetime: string;
    user: User;
}

export interface Measurement {
    id: number;
    name: string;
    datetime: string;
    comments: Comment[];
}
