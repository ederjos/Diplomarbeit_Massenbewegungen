interface ProjectBase {
    id: number;
    name: string;
    isActive: boolean;
}

export interface ProjectOverview extends ProjectBase {
    lastMeasurement: string | null;
    nextMeasurement: string | null;
}

export interface ProjectDetails extends ProjectBase {
    id: number;
    name: string;
    comment: string;
    last_file_number: number;
    isActive: boolean;
    period: string;
    client: string;
    clerk: string;
    municipality: string;
    type: string;
}
