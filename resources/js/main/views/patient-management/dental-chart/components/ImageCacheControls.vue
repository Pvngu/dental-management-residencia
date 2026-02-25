<template>
    <div class="image-cache-controls mb-4 p-3 bg-gray-50 rounded-lg border">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Image Cache Management</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">
                    {{ cacheSize }} images cached
                </span>
                <a-button 
                    size="small" 
                    @click="handleClearCache"
                    :disabled="cacheSize === 0"
                >
                    Clear Cache
                </a-button>
                <a-button 
                    size="small" 
                    type="primary" 
                    @click="handlePreloadImages"
                    :loading="preloadingStatus.isPreloading"
                    :disabled="preloadingStatus.isPreloading"
                >
                    {{ preloadingStatus.isPreloading ? 'Loading ALL Images...' : 'Preload ALL Images' }}
                </a-button>
            </div>
        </div>
        
        <div v-if="preloadingStatus.isPreloading" class="mb-2">
            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                <span>Loading ALL tooth images...</span>
                <span>{{ preloadingStatus.loaded }}/{{ preloadingStatus.total }}</span>
            </div>
            <a-progress 
                :percent="preloadingStatus.progress" 
                size="small" 
                status="active"
                :show-info="false"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { message } from 'ant-design-vue';
import useToothImageCache from '../composables/useToothImageCache.js';

const { 
    preloadAllToothImages, 
    clearCache, 
    getCacheSize, 
    preloadingStatus 
} = useToothImageCache();

const cacheSize = computed(() => getCacheSize());

const handlePreloadImages = async () => {
    try {
        await preloadAllToothImages();
        message.success(`Successfully preloaded ALL ${getCacheSize()} tooth images for instant access`);
    } catch (error) {
        message.error('Failed to preload images');
        console.error('Preload error:', error);
    }
};

const handleClearCache = () => {
    clearCache();
    message.success('Image cache cleared');
};
</script>
