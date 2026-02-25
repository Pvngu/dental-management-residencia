import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js', 
                'resources/js/landing.js'
            ],
            refresh: true,
            detectTls: 'dental-management-residencia.test'
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        })
    ],
    optimizeDeps: {
        include: ['vuedraggable'],
        exclude: ['vue'],
    },
    build: {
        rollupOptions: {
            output: {
                // Prevent any cross-dependencies between chunks
                manualChunks(id) {
                    // Only landing-specific files go in landing chunk
                    if (id.includes('landing.js') || (id.includes('/landing/') && !id.includes('node_modules'))) {
                        return 'landing-app';
                    }
                    
                    // Only main app files go in app chunk
                    if (id.includes('app.js') || id.includes('/main/') || id.includes('/superadmin/')) {
                        return 'main-app';
                    }
                    
                    // Common utilities that can be shared
                    if (id.includes('/common/') && !id.includes('node_modules')) {
                        // Split common files - some can be shared, others should be duplicated
                        if (id.includes('i18n') || id.includes('composable') || id.includes('services')) {
                            return 'shared-utils';
                        }
                        // UI components should be duplicated to avoid conflicts
                        return undefined; // Let it be included in both chunks if needed
                    }
                    
                    // Vendor dependencies
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') && !id.includes('vue3-print')) {
                            return 'vue-core';
                        }
                        if (id.includes('ant-design')) {
                            return 'antd';
                        }
                        return 'vendor';
                    }
                }
            }
        }
    }
});
