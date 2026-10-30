import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// El Frontend se compila dentro del Backend (Backend/public/build), que es lo que se sube al cPanel.
export default defineConfig({
    envDir: '../Backend',
    plugins: [
        laravel({
            input: ['Aplicacion_Principal.js'],
            publicDirectory: '../Backend/public',
            hotFile: '../Backend/public/hot',
            refresh: ['../Backend/resources/views/**', '../Backend/routes/**'],
        }),
        vue({ template: { transformAssetUrls: { base: null, includeAbsolute: false } } }),
    ],
    build: { emptyOutDir: true },
});
