export interface Project {
    id: number;
    name: string;
    isActive: boolean;
    lastMeasurement: string | null;
    nextMeasurement: string | null;
}
