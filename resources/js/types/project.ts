interface ProjectBase {
    id: number;
    name: string;
    isActive: boolean;
}

export interface ProjectOverview extends ProjectBase {
    lastMeasurement: string | null;
    isFavorite: boolean;
}

export interface ProjectDetails extends ProjectBase {
    comment: string;
    lastFileNumber: number;
    measurementInterval: string | null;
    movementMagnitude: string | null;
    client: string;
    clerk: string;
    municipality: string;
    type: string;
    firstMeasurement: string | null;
    lastMeasurement: string | null;
}
