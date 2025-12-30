export interface MeasurementValue {
    measurement_id: number
    x: number
    y: number
    z: number
    lat: number
    lon: number
    datetime: string
    measurement_name: string
}

export interface Point {
    id: number
    name: string
    projection_id: number | null
    measurement_values: MeasurementValue[]
}

export interface Measurement {
    id: number
    name: string
    date: string // Same as MeasurementValue
}