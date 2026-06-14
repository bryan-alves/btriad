import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

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
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: false,
            manifest: false,
            includeAssets: ['pwa/*.png', 'img/logo/triangulo.png'],
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2}'],
                navigateFallback: undefined,
                runtimeCaching: [
                    {
                        urlPattern: ({ url }) => url.pathname.startsWith('/api/'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 60 * 24,
                            },
                            networkTimeoutSeconds: 10,
                        },
                    },
                    {
                        urlPattern: ({ request }) => request.destination === 'image',
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'images-cache',
                            expiration: {
                                maxEntries: 60,
                                maxAgeSeconds: 60 * 60 * 24 * 30,
                            },
                        },
                    },
                ],
            },
            devOptions: {
                enabled: false,
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: devServerPort,
        strictPort: true,
        cors: {
            origin: [
                /^https?:\/\/127\.0\.0\.1(?::\d+)?$/,
                /^https?:\/\/localhost(?::\d+)?$/,
                /^https?:\/\/[\w.-]+\.test(?::\d+)?$/,
                /^https?:\/\/apjiujitsu\.com\.br(?::\d+)?$/,
                /^https?:\/\/btriadjiujitsu\.com\.br(?::\d+)?$/,
                /^https?:\/\/tatameiro\.com\.br(?::\d+)?$/,
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
