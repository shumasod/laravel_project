import js from '@eslint/js';

export default [
    js.configs.recommended,
    {
        files: ['resources/js/**/*.{js,jsx,ts,tsx}'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                setTimeout: 'readonly',
                clearTimeout: 'readonly',
                fetch: 'readonly',
                AbortController: 'readonly',
                URLSearchParams: 'readonly',
            },
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },
        rules: {
            'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'prefer-const': 'error',
            'no-var': 'error',
            'eqeqeq': ['error', 'always'],
            'curly': ['error', 'all'],
        },
    },
    {
        ignores: ['node_modules/**', 'public/**', 'vendor/**'],
    },
];
