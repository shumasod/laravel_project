import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { visualizer } from 'rollup-plugin-visualizer';
import viteCompression from 'vite-plugin-compression';

export default defineConfig(({ command, mode }) => {
    const isProduction = mode === 'production';
    const isAnalyze = mode === 'analyze';

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),
            react(),
            tailwindcss(),

            // Production only plugins
            ...(isProduction ? [
                // Gzip compression
                viteCompression({
                    algorithm: 'gzip',
                    ext: '.gz',
                    threshold: 1024,
                    deleteOriginFile: false,
                }),
                // Brotli compression
                viteCompression({
                    algorithm: 'brotliCompress',
                    ext: '.br',
                    threshold: 1024,
                    deleteOriginFile: false,
                }),
            ] : []),

            // Bundle analyzer (only in analyze mode)
            ...(isAnalyze ? [
                visualizer({
                    filename: 'public/build/stats.html',
                    open: true,
                    gzipSize: true,
                    brotliSize: true,
                }),
            ] : []),
        ],

        // Build configuration
        build: {
            // Output directory
            outDir: 'public/build',

            // Enable source maps in production for debugging
            sourcemap: isProduction ? 'hidden' : true,

            // Minification settings
            minify: isProduction ? 'terser' : false,
            terserOptions: isProduction ? {
                compress: {
                    drop_console: true,
                    drop_debugger: true,
                    pure_funcs: ['console.log', 'console.info', 'console.debug'],
                },
                mangle: {
                    safari10: true,
                },
                format: {
                    comments: false,
                },
            } : undefined,

            // Chunk splitting strategy
            rollupOptions: {
                output: {
                    // Manual chunk splitting for better caching
                    manualChunks: (id) => {
                        // React core
                        if (id.includes('node_modules/react') ||
                            id.includes('node_modules/react-dom') ||
                            id.includes('node_modules/scheduler')) {
                            return 'react-vendor';
                        }
                        // Inertia
                        if (id.includes('node_modules/@inertiajs')) {
                            return 'inertia-vendor';
                        }
                        // Other vendor modules
                        if (id.includes('node_modules')) {
                            return 'vendor';
                        }
                    },
                    // Asset file naming with hash for cache busting
                    entryFileNames: 'assets/[name]-[hash].js',
                    chunkFileNames: 'assets/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash].[ext]',
                },
            },

            // Chunk size warning limit
            chunkSizeWarningLimit: 500,

            // Target modern browsers
            target: 'es2020',

            // CSS code splitting
            cssCodeSplit: true,

            // Inline assets under 4kb
            assetsInlineLimit: 4096,
        },

        // Development server configuration
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            hmr: {
                host: 'localhost',
            },
        },

        // Optimization
        optimizeDeps: {
            include: ['react', 'react-dom', '@inertiajs/react'],
        },

        // Resolve aliases
        resolve: {
            alias: {
                '@': '/resources/js',
                '@components': '/resources/js/Components',
                '@pages': '/resources/js/Pages',
            },
        },

        // CSS configuration
        css: {
            devSourcemap: true,
        },

        // Enable JSON tree-shaking
        json: {
            stringify: true,
        },

        // Performance hints
        esbuild: {
            legalComments: 'none',
        },
    };
});
