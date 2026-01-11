export interface MeasurementValue {
    measurementId: number
    x: number
    y: number
    z: number
    lat: number
    lon: number
    datetime: string
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
    datetime: string // Same as MeasurementValue
}