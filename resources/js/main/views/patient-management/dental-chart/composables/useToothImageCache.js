import { ref, reactive } from 'vue';

// Global image cache (In-Memory only)
const imageCache = reactive(new Map());
const preloadingStatus = ref({
    isPreloading: false,
    progress: 0,
    total: 0,
    loaded: 0,
    activeRequests: 0
});

// All possible tooth IDs and views
const TOOTH_IDS = [
    11, 12, 13, 14, 15, 16, 17, 18,
    19, 21, 22, 23, 24, 25, 26, 27, 28,
    31, 32, 33, 34, 35, 36, 37, 38,
    41, 42, 43, 44, 45, 46, 47, 48
];

const VIEWS = ['', '-top', '-ud']; // empty string for front view
const VARIANTS = ['', '-missing', '-top-missing', '-ud-missing', '-implant'];

// Priority Queue Implementation
const MAX_CONCURRENT_REQUESTS = 6;
const queue = reactive([]);
let activeRequests = 0;

const useToothImageCache = () => {
    
    const getImageCacheKey = (type, toothId, view = '', variant = '') => {
        return `${type}-${toothId}${view}${variant}`;
    };

    const getCachedImage = (type, toothId, view = '', variant = '') => {
        const key = getImageCacheKey(type, toothId, view, variant);
        return imageCache.get(key);
    };

    const setCachedImage = (type, toothId, view = '', variant = '', imageElement) => {
        const key = getImageCacheKey(type, toothId, view, variant);
        imageCache.set(key, imageElement);
    };

    const getToothImageUrl = (type, toothId, view = '', variant = '') => {
        let viewSuffix = view;
        let variantSuffix = variant;
        
        if (variant) {
            return `/assets/teeth/${type}/variants/${toothId}${viewSuffix}${variantSuffix}.webp`;
        }
        
        return `/assets/teeth/${type}/${toothId}${viewSuffix}.webp`;
    };

    const isValidCombination = (view, variant) => {
        if (variant === '-top-missing' && view !== '-top') return false;
        if (variant === '-ud-missing' && view !== '-ud') return false;
        if ((variant === '-top-missing' || variant === '-ud-missing') && view === '') return false;
        return true;
    };

    const processQueue = () => {
        if (activeRequests >= MAX_CONCURRENT_REQUESTS || queue.length === 0) return;

        // Sort queue by priority (higher number = higher priority)
        queue.sort((a, b) => b.priority - a.priority);

        while (activeRequests < MAX_CONCURRENT_REQUESTS && queue.length > 0) {
            const item = queue.shift();
            activeRequests++;
            preloadingStatus.value.activeRequests = activeRequests;

            const { url, type, toothId, view, variant, resolve, reject } = item;

            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            const handleLoad = () => {
                setCachedImage(type, toothId, view, variant, img);
                activeRequests--;
                preloadingStatus.value.activeRequests = activeRequests;
                preloadingStatus.value.loaded++;
                updateProgress();
                resolve(img);
                processQueue();
            };
            
            const handleError = () => {
                activeRequests--;
                preloadingStatus.value.activeRequests = activeRequests;
                preloadingStatus.value.loaded++;
                updateProgress();
                resolve(null); // Resolve with null on error to prevent blocking
                processQueue();
            };
            
            img.onload = handleLoad;
            img.onerror = handleError;
            img.src = url;
        }
    };

    const updateProgress = () => {
        if (preloadingStatus.value.total > 0) {
            preloadingStatus.value.progress = Math.round(
                (preloadingStatus.value.loaded / preloadingStatus.value.total) * 100
            );
        }
    };

    const addToQueue = (url, type, toothId, view, variant, priority = 1) => {
        return new Promise((resolve, reject) => {
            // Check in-memory cache first
            const cached = getCachedImage(type, toothId, view, variant);
            if (cached) {
                resolve(cached);
                return;
            }

            // Check if already in queue
            const existingItem = queue.find(item => 
                item.type === type && 
                item.toothId === toothId && 
                item.view === view && 
                item.variant === variant
            );

            if (existingItem) {
                // Update priority if higher
                if (priority > existingItem.priority) {
                    existingItem.priority = priority;
                }
                // Hook into existing promise
                const originalResolve = existingItem.resolve;
                existingItem.resolve = (img) => {
                    originalResolve(img);
                    resolve(img);
                };
                return;
            }

            queue.push({ url, type, toothId, view, variant, priority, resolve, reject });
            preloadingStatus.value.total++;
            processQueue();
        });
    };

    const preloadVisibleImages = async (viewMode = 'all', teethIds = TOOTH_IDS) => {
        // Determine which views are visible based on viewMode
        // viewMode: 'all', 'upper', 'lower'
        
        const priorities = [];

        teethIds.forEach(toothId => {
            const type = (toothId >= 11 && toothId <= 28) ? 'upper' : 'lower';
            
            // Skip if tooth not visible in current mode
            if (viewMode === 'upper' && type === 'lower') return;
            if (viewMode === 'lower' && type === 'upper') return;

            VIEWS.forEach(view => {
                VARIANTS.forEach(variant => {
                    if (!isValidCombination(view, variant)) return;

                    // Calculate priority
                    let priority = 1; // Default low priority

                    // Base view (no variant) is higher priority
                    if (variant === '') {
                        priority = 10;
                    }

                    // Visible views get highest priority
                    // Assuming 'front' ('') is always visible initially or most important
                    if (view === '') {
                        priority += 5;
                    }

                    const url = getToothImageUrl(type, toothId, view, variant);
                    priorities.push({ url, type, toothId, view, variant, priority });
                });
            });
        });

        // Add to queue
        priorities.forEach(p => addToQueue(p.url, p.type, p.toothId, p.view, p.variant, p.priority));
    };

    const preloadAllToothImages = async () => {
        preloadVisibleImages('all');
    };

    const clearCache = () => {
        imageCache.clear();
        queue.length = 0;
        preloadingStatus.value = {
            isPreloading: false,
            progress: 0,
            total: 0,
            loaded: 0,
            activeRequests: 0
        };
    };

    return {
        preloadVisibleImages,
        preloadAllToothImages,
        getImageFromCache: getCachedImage,
        getCachedImage,
        setCachedImage,
        getToothImageUrl,
        clearCache,
        preloadingStatus,
        imageCache
    };
};

export default useToothImageCache;
