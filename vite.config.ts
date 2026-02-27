import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import webfontDownload from 'vite-plugin-webfont-dl';

export default defineConfig({
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
        webfontDownload(['https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400..700'], {
            assetsSubfolder: 'webfonts',
            subsetsAllowed: ['latin'],
        }),
    ],
});
