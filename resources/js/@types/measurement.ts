export interface MeasurementValue {
    measurementId: number
    x: number
    y: number
    z: number
    lat: number
    lon: number
    datetime: string
    measurementName: string
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
    date: string // Same as MeasurementValue
}