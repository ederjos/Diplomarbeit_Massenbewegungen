// the user model with permissions for the page.props.auth.user object
export interface AuthUser {
    id: number;
    name: string;
    permissions: Permissions;
}

export interface Permissions {
    isAdmin: boolean;
    canManageProjects: boolean;
    canManageMeasurements: boolean;
    canComment: boolean;
}

// the user model without permissions but with the role name
export interface User {
    id: number;
    name: string;
    role: Role;
}

export interface Role {
    id: number;
    name: string;
}
