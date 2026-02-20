interface ProjectBase {
    id: number;
    name: string;
    isActive: boolean;
}

export interface ProjectOverview extends ProjectBase {
    lastMeasurement: string | null;
    nextMeasurement: string | null;
    isFavorite: boolean;
}

export interface ProjectDetails extends ProjectBase {
    comment: string;
    lastFileNumber: number;
    period: string;
    client: string;
    clerk: string;
    municipality: string;
    type: string;
    movementMagnitude: number | null;
    firstMeasurement: string | null;
    lastMeasurement: string | null;
}
