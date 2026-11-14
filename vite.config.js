import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), 'VITE_');
    const configuredPort = Number.parseInt(env.VITE_PORT ?? '5173', 10);
    const port = Number.isNaN(configuredPort) ? 5173 : configuredPort;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.tsx'],
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            react(),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            origin: `http://localhost:${port}`,
            port,
            strictPort: true,
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
