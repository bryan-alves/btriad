import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const devServerHost = process.env.VITE_DEV_SERVER_HOST || '127.0.0.1';
const devServerPort = Number(process.env.VITE_DEV_SERVER_PORT || 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js',  'resources/css/index.css',
                'resources/js/index.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    server: {
        host: '0.0.0.0',
        port: devServerPort,
        strictPort: true,
        cors: {
            origin: [
                /^https?:\/\/127\.0\.0\.1(?::\d+)?$/,
                /^https?:\/\/localhost(?::\d+)?$/,
                /^https?:\/\/apjiujitsu\.com\.br(?::\d+)?$/,
                /^https?:\/\/btriadjiujitsu\.com\.br(?::\d+)?$/,
            ],
        },
        hmr: {
            host: devServerHost,
            port: devServerPort,
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
