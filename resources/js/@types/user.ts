export interface User {
    id: number;
    name: string;
    email: string;
    role: Role | null;
}

export interface Role {
    id: number;
    name: string;
}
