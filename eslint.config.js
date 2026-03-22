import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import prettier from 'eslint-config-prettier/flat';
import perfectionist from 'eslint-plugin-perfectionist';
import vue from 'eslint-plugin-vue';

export default defineConfigWithVueTs(
    vue.configs['flat/recommended'],
    vueTsConfigs.recommendedTypeChecked,
    vueTsConfigs.stylisticTypeChecked,
    {
        plugins: {
            perfectionist,
        },
        rules: {
            '@typescript-eslint/consistent-type-imports': 'error',
            'perfectionist/sort-imports': ['error', { tsconfig: { rootDir: '.' } }],
            'perfectionist/sort-named-imports': 'error',
        },
    },
    {
        ignores: [
            'vendor',
            'node_modules',
            'public/build',
            'bootstrap/ssr',
            'resources/js/actions/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
    },
    // Pages use single-word names by convention
    {
        files: ['resources/js/pages/**/*.vue'],
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
    // Turn off all rules that might conflict with Prettier
    prettier,
);
