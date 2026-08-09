import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'public', // Output directory for the build
        emptyOutDir: false,
        chunkSizeWarningLimit: 2000,
        modulePreload: {
            polyfill: false, // Disable polyfills for module preload
        },
        sourcemap: false, // Generate source maps for easier debugging
        rollupOptions: {
            input: 'resources/js/app.js',

            output: {
                entryFileNames: 'js/app.js',
                inlineDynamicImports: true,

                assetFileNames: (assetInfo) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return 'css/app.css';
                    }

                    if (/\.(woff2?|ttf|eot|otf)$/i.test(assetInfo.name)) {
                        return 'fonts/[name][extname]';
                    }

                    return 'assets/[name][extname]';
                },
            },
        },
    },
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: [
                    'import',
                    'color-functions',
                    'global-builtin',
                    'if-function',
                    'function-units',
                ],
            },
        },
    },
});