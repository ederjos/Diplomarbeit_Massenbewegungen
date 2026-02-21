export interface User {
    id: number;
    name: string;
    role: Role | null;
}

export interface Role {
    id: number;
    name: string;
}
