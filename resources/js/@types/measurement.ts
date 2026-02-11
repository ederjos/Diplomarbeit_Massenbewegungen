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
    distance2d: number; // cm
    distance3d: number; // cm
    projectedDistance: number | null; // cm, null if no projection
    deltaHeight: number; // cm
}

export interface Measurement {
    id: number;
    name: string;
    datetime: string;
}
