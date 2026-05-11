import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        rolldownOptions: {
            output: {
                codeSplitting: {
                    groups: [
                        {
                            // transitive dependency of echarts, but large enough to deserve its own chunk
                            test: /node_modules[\\/]zrender/,
                            name: 'zrender',
                        },
                        {
                            test: /node_modules[\\/]echarts/,
                            name: 'echarts',
                        },
                        {
                            test: /node_modules[\\/]leaflet/,
                            name: 'leaflet',
                        },
                        {
                            test: /node_modules/,
                            name: 'vendor',
                        },
                    ],
                },
            },
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                google('Instrument Sans', {
                    weights: ['400..700'],
                    display: 'auto',
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue(),
        wayfinder(),
    ],
});
