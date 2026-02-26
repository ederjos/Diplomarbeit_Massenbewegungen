import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        // configure vitest
        reporters: 'verbose',
        environment: 'jsdom',
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // create a separate chunk for each node module
                    if (id.includes('node_modules')) {
                        const moduleName = id.toString().split('node_modules/')[1].split('/')[0];
                        return `vendor/${moduleName}`;
                    }

                    return null;
                },
            },
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
