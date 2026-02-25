<template>
    <div class="tooth-image-preloader" v-if="showProgress">
        <div class="preloader-content">
            <div class="text-xs text-gray-600 mb-1">
                Loading ALL tooth images... {{ preloadingStatus.loaded }}/{{ preloadingStatus.total }}
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
import { computed, onMounted } from 'vue';
import useToothImageCache from '../composables/useToothImageCache.js';

const props = defineProps({
    autoStart: {
        type: Boolean,
        default: true
    },
    showProgress: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['preload-complete', 'preload-error']);

const { 
    preloadAllToothImages, 
    preloadingStatus 
} = useToothImageCache();

const isPreloading = computed(() => preloadingStatus.value.isPreloading);

const startPreloading = async () => {
    try {
        await preloadAllToothImages();
        emit('preload-complete');
    } catch (error) {
        emit('preload-error', error);
    }
};

// Auto-start preloading if enabled
onMounted(() => {
    if (props.autoStart) {
        startPreloading();
    }
});

// Expose the preloading function for manual control
defineExpose({
    startPreloading,
    isPreloading,
    preloadingStatus
});
</script>

<style scoped>
.tooth-image-preloader {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 200px;
}

.preloader-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
</style>
