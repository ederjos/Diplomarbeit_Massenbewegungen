export interface MeasurementValue {
    measurementId: number;
    x: number; // temp
    y: number; // temp
    z: number; // temp
    height: number;
    lat: number;
    lon: number;
}

export interface Point {
    id: number;
    name: string;
    projectionId: number | null;
    measurementValues: MeasurementValue[];
}

export interface Measurement {
    id: number;
    name: string;
    datetime: string;
}
