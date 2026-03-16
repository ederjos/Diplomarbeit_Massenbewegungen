import type { AuthUser } from '@/@types/user';

// Define the types for the shared page properties
// The next version of Laravel Wayfinder may automatically generate these types, making this file unnecessary.
declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: { user: AuthUser | null };
        };
    }
}
