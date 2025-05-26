import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    css: {
        postcss: './postcss.config.cjs',
    },
    server: {
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
        },
        host: true,
        port: 5173,
    },
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
            external: (id) => {
                // 在生产构建中排除 stagewise 相关包
                if (process.env.NODE_ENV === 'production' && id.includes('@stagewise/')) {
                    return true;
                }
                return false;
            }
        },
    },
    publicDir: 'public',
});
