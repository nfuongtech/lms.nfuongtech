import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // BỔ SUNG 2 entry mới cho Filament sidebar
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/sidebar.css',
                'resources/js/filament/admin/sidebar.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
