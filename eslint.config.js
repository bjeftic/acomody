import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';

export default [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        files: ['resources/js/**/*.{js,vue}'],
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.es2021,
                ...globals.node,
            },
        },
        rules: {
            // Warnings — existing code has violations; fix gradually
            'no-unused-vars': 'warn',
            'no-console': 'warn',
            'no-useless-catch': 'warn',
            'no-empty-pattern': 'warn',
            'vue/multi-word-component-names': 'off',
            'vue/no-unused-vars': 'warn',
            'vue/html-indent': 'off',
            'vue/html-self-closing': 'off',
            'vue/no-reserved-component-names': 'warn',
            'vue/no-deprecated-destroyed-lifecycle': 'warn',
            'vue/no-deprecated-delete-set': 'warn',
            'vue/no-unused-components': 'warn',
            'vue/no-deprecated-model-definition': 'warn',
            'no-constant-binary-expression': 'warn',
        },
    },
    {
        ignores: [
            'node_modules/**',
            'public/**',
            'vendor/**',
            'bootstrap/**',
        ],
    },
];
