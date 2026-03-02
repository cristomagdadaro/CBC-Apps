/// <reference types="vitest" />
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
//import dotenv from 'dotenv';

//dotenv.config();

export default defineConfig({
    /* server: {
         host: process.env.APP_URL,
     },*/
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
            ],
            refresh: true,
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
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
    },
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: 'tests/setup.ts',
        css: true,
        include: ['resources/js/**/*.{test,spec}.{js,ts,jsx,tsx}'],
    },
});
