export interface BaseProject {
    id: number;
    name: string;
}

interface ActiveProject extends BaseProject {
    isActive: boolean;
}

export interface ProjectOverview extends ActiveProject {
    lastMeasurement: string | null;
    isFavorite: boolean;
}

export interface ProjectDetails extends ActiveProject {
    comment: string;
    lastFileNumber: number;
    measurementInterval: string | null;
    movementMagnitude: string | null;
    client: string;
    clerk: string;
    municipality: string;
    type: string;
    referenceMeasurementId: number | null;
    firstMeasurement: string | null;
    lastMeasurement: string | null;
}
