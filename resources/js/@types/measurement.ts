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

export interface Measurement {
    id: number;
    name: string;
    datetime: string;
}
