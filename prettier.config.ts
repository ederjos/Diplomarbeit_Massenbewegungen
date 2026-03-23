import type { Config } from 'prettier';
import type { PluginOptions } from 'prettier-plugin-tailwindcss';

const config: Config & PluginOptions = {
    printWidth: 120,
    tabWidth: 4,
    singleQuote: true,
    plugins: ['prettier-plugin-tailwindcss'],
    tailwindStylesheet: 'resources/css/app.css',
    overrides: [
        {
            files: '**/*.{yml,yaml}',
            excludeFiles: 'compose.yaml',
            options: {
                tabWidth: 2,
            },
        },
    ],
};

export default config;
