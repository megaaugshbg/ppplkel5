import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/tokens.css', 'resources/js/app.js', 'resources/js/verifikasi-sim.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
