export interface MeasurementValue {
    measurementId: number
    x: number
    y: number
    z: number
    lat: number
    lon: number
}

export interface Point {
    id: number
    name: string
    projectionId: number | null
    measurementValues: MeasurementValue[]
}

export interface Measurement {
    id: number
    name: string
    datetime: string
}