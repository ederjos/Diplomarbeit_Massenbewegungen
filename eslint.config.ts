import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
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
    // Turn off some rules that might conflict with Prettier
    // alternatively, eslint-config-prettier could be used, but it disables a lot of rules that I'd like to keep
    {
        rules: {
            'vue/html-indent': 'off',
            'vue/max-attributes-per-line': 'off',
            'vue/singleline-html-element-content-newline': 'off',

            // Prettier requires self-closing for void elements (https://github.com/prettier/prettier/issues/15336)
            'vue/html-self-closing': ['error', { html: { void: 'always' } }],
        },
    },
);
