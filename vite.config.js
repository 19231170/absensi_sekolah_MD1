import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        https: {
            // Arahkan ke file sertifikat dan kunci yang baru saja dibuat
            key: fs.readFileSync('./localhost+2-key.pem'),
            cert: fs.readFileSync('./localhost+2.pem'),
        },
        // Atur host agar bisa diakses dari perangkat lain di jaringan
        host: '192.168.1.18',
    },

});
