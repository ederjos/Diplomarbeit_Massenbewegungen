// the user model with permissions for the page.props.auth.user object
export interface AuthUser {
    id: number;
    name: string;
    permissions: Permissions;
}

export interface Permissions {
    manage_users: boolean;
    manage_projects: boolean;
    manage_measurements: boolean;
    manage_comments: boolean;
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
