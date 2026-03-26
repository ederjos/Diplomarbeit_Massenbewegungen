import { createInertiaApp } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        if (name.startsWith('auth/')) {
            return null;
        }

        return AuthenticatedLayout;
    },
    progress: {
        color: '#4B5563',
    },
});
