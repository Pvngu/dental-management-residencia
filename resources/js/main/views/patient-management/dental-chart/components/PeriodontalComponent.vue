<template>
    <div class="periodontal-component w-full">
        <!-- Periodontal Position Selection -->
        <div class="mt-4">
            <div class="grid grid-cols-3 gap-4">
                <div
                    v-for="(position, key) in periodontalPositions"
                    :key="key"
                    class="periodontal-card"
                    :class="{
                        'periodontal-card-selected': isPositionSelected(key),
                        'periodontal-card-diseased': isProbingDepthDiseased(key),
                    }"
                    @click="selectPeriodontalPosition(key)"
                >
                    <div class="periodontal-card-content">
                        <div
                            class="periodontal-indicators"
                            :class="`${key}-indicators`"
                        >
                            <span
                                class="indicator"
                                :class="{
                                    'indicator-red':
                                        periodontalData[key].bleeding,
                                }"
                            ></span>
                            <span
                                class="indicator"
                                :class="{
                                    'indicator-yellow':
                                        periodontalData[key].plaque,
                                }"
                            ></span>
                            <span
                                class="indicator"
                                :class="{
                                    'indicator-blue':
                                        periodontalData[key].tartar,
                                }"
                            ></span>
                            <span
                                class="indicator"
                                :class="{
                                    'indicator-orange':
                                        periodontalData[key].pus,
                                }"
                            ></span>
                        </div>
                        <span class="periodontal-value">{{
                            periodontalData[key].probingDepth !== null && periodontalData[key].probingDepth !== undefined 
                                ? periodontalData[key].probingDepth || 0
                                : ''
                        }}</span>
                        <span class="periodontal-divider"></span>
                        <span
                            class="periodontal-value periodontal-value-secondary"
                            >{{
                                periodontalData[key].gingivalMargin !== null && periodontalData[key].gingivalMargin !== undefined 
                                    ? periodontalData[key].gingivalMargin || 0
                                    : ''
                            }}</span
                        >
                    </div>
                    <span class="periodontal-divider-horizontal"></span>
                    <span
                        class="periodontal-label"
                        :class="{
                            'periodontal-label-blue': key === 'disto_palatal',
                        }"
                    >
                        {{ t(`dental_chart.${key}`) }}
                    </span>
                </div>
            </div>
            <div
                class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4 relative transition-shadow duration-200"
            >
                <div class="flex justify-between mb-2">
                    <a-button 
                        type="text" 
                        :class="[
                            'font-semibold text-lg w-full text-center',
                            selectedMeasurementType === 'probingDepth' ? 'text-blue-600!' : 'text-gray-500'
                        ]"
                        @click="selectMeasurementType('probingDepth')"
                    >
                        {{ t("dental_chart.probing_depth") }}
                    </a-button>
                    <a-button 
                        type="text" 
                        :class="[
                            'font-semibold text-lg w-full text-center',
                            selectedMeasurementType === 'gingivalMargin' ? 'text-blue-600!' : 'text-gray-500'
                        ]"
                        @click="selectMeasurementType('gingivalMargin')"
                    >
                        {{ t("dental_chart.recession") }}
                    </a-button>
                </div>
                
                <!-- Toggle Button for Gingival Margin -->
                <div v-if="selectedMeasurementType === 'gingivalMargin'" class="flex justify-center mb-3">
                    <a-button 
                        size="small"
                        :type="gingivalMarginMode === 'negative' ? 'primary' : 'default'"
                        @click="toggleGingivalMarginMode"
                    >
                        {{ gingivalMarginMode === 'negative' ? 'Negative Values' : 'Positive Values' }}
                    </a-button>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <button
                        v-for="(val, idx) in measurementValues"
                        :key="val"
                        type="button"
                        class="h-14 rounded-xl border border-gray-300 flex items-center justify-center text-lg font-medium transition-colors cursor-pointer"
                        :class="{
                            'bg-blue-500 text-white! border-blue-600': selectedDepth === val && !isProbingDepthDisabled(val),
                            'bg-white text-gray-700': selectedDepth !== val && !isProbingDepthDisabled(val),
                            'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed': isProbingDepthDisabled(val)
                        }"
                        :disabled="isProbingDepthDisabled(val)"
                        @click="!isProbingDepthDisabled(val) && selectDepth(val)"
                    >
                        {{ val }}
                    </button>
                </div>
            </div>
            <div class="mt-6 grid grid-cols-4 gap-4">
                <div
                    class="periodontal-indicator-card indicator-red"
                    :class="{
                        'border-blue-500! bg-blue-500!': selectedPosition && periodontalData[selectedPosition]?.bleeding,
                        'border-gray-200!': !selectedPosition || !periodontalData[selectedPosition]?.bleeding
                    }"
                    @click="toggleIndicator('bleeding')"
                >
                    <span class="indicator-circle bg-red-500"></span>
                    <span 
                        :class="[
                            'font-semibold',
                            selectedPosition && periodontalData[selectedPosition]?.bleeding 
                                ? 'text-white' 
                                : 'text-red-600'
                        ]"
                    >
                        {{ t("dental_chart.bleeding") }}
                    </span>
                </div>
                <div
                    class="periodontal-indicator-card indicator-blue"
                    :class="{
                        'border-blue-500! bg-blue-500!': selectedPosition && periodontalData[selectedPosition]?.plaque,
                        'border-gray-200!': !selectedPosition || !periodontalData[selectedPosition]?.plaque
                    }"
                    @click="toggleIndicator('plaque')"
                >
                    <span class="indicator-circle bg-blue-500"></span>
                    <span 
                        :class="[
                            'font-semibold',
                            selectedPosition && periodontalData[selectedPosition]?.plaque 
                                ? 'text-white' 
                                : 'text-blue-600'
                        ]"
                    >
                        {{ t("dental_chart.plaque") }}
                    </span>
                </div>
                <div
                    class="periodontal-indicator-card indicator-yellow"
                    :class="{
                        'border-blue-500! bg-blue-500!': selectedPosition && periodontalData[selectedPosition]?.pus,
                        'border-gray-200!': !selectedPosition || !periodontalData[selectedPosition]?.pus
                    }"
                    @click="toggleIndicator('pus')"
                >
                    <span class="indicator-circle bg-yellow-400"></span>
                    <span 
                        :class="[
                            'font-semibold',
                            selectedPosition && periodontalData[selectedPosition]?.pus 
                                ? 'text-white' 
                                : 'text-yellow-600'
                        ]"
                    >
                        {{ t("dental_chart.pus") }}
                    </span>
                </div>
                <div
                    class="periodontal-indicator-card indicator-gray"
                    :class="{
                        'border-blue-500! bg-blue-500!': selectedPosition && periodontalData[selectedPosition]?.tartar,
                        'border-gray-200!': !selectedPosition || !periodontalData[selectedPosition]?.tartar
                    }"
                    @click="toggleIndicator('tartar')"
                >
                    <span class="indicator-circle bg-white border border-gray-400"></span>
                    <span 
                        :class="[
                            'font-semibold',
                            selectedPosition && periodontalData[selectedPosition]?.tartar 
                                ? 'text-white' 
                                : 'text-gray-600'
                        ]"
                    >
                        {{ t("dental_chart.tartar") }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end pt-4 border-t mt-4">
            <a-button
                type="primary"
                size="large"
                class="h-12 px-6 text-lg"
                @click="saveChanges"
            >
                {{ $t("common.save") }}
            </a-button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter, useRoute } from "vue-router";

const { t } = useI18n();
const router = useRouter();
const route = useRoute();

const props = defineProps({
    toothId: {
        type: Number,
        required: true,
    },
    selectedPosition: {
        type: String,
        default: null,
    },
    initialData: {
        type: Object,
        default: () => ({
            // Add default periodontal data structure here
            disto_palatal: {
                probingDepth: 2,
                gingivalMargin: 0,
                bleeding: false,
                plaque: false,
                tartar: false,
                pus: false,
            },
            palatal: {
                probingDepth: 2,
                gingivalMargin: 0,
                bleeding: false,
                plaque: false,
                tartar: false,
                pus: false,
            },
            mesio_palatal: {
                probingDepth: 2,
                gingivalMargin: 0,
                bleeding: false,
                plaque: false,
                tartar: true,
                pus: false,
            },
            disto_buccal: {
                probingDepth: 2,
                gingivalMargin: 0,
                bleeding: false,
                plaque: false,
                tartar: false,
                pus: false,
            },
            buccal: {
                probingDepth: 2,
                gingivalMargin: 0,
                bleeding: false,
                plaque: false,
                tartar: false,
                pus: false,
            },
            mesio_buccal: {
                probingDepth: 2,
                gingivalMargin: 0,
                bleeding: false,
                plaque: false,
                tartar: false,
                pus: false,
            },
        }),
    },
});

const emit = defineEmits([
    "close",
    "saved",
    "periodontal-type-changed",
]);

const periodontalData = ref(JSON.parse(JSON.stringify(props.initialData)));

// Store original data to revert if not saved
const originalData = ref({});
const hasBeenSaved = ref(false);
const isComponentActive = ref(true);

// Define periodontal positions
const periodontalPositions = {
    disto_palatal: true,
    palatal: true,
    mesio_palatal: true,
    disto_buccal: true,
    buccal: true,
    mesio_buccal: true,
};

// New reactive variables for measurement selection
const selectedMeasurementType = ref('probingDepth');
const selectedDepth = ref(null);
const gingivalMarginMode = ref('negative'); // 'negative' or 'positive'

// Computed property to get the current selected position
const selectedPosition = computed(() => {
    if (!props.selectedPosition) return 'disto_palatal'; // default
    return props.selectedPosition.replace(/-/g, '_');
});

// Computed property for measurement values
const measurementValues = computed(() => {
    if (selectedMeasurementType.value === 'gingivalMargin') {
        if (gingivalMarginMode.value === 'negative') {
            return [0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -11, -12, '<-12'];
        } else {
            return [0, 1, 2, 3, 4, 5, 6, 7, '>7'];
        }
    } else {
        return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '>12'];
    }
});

// Computed property to get disabled probing depth values based on gingival margin
const getDisabledProbingDepthValues = computed(() => {
    if (selectedMeasurementType.value === 'probingDepth' && selectedPosition.value) {
        const gingivalMargin = periodontalData.value[selectedPosition.value]?.gingivalMargin;
        
        // Special case: if gingival margin is '<-12', disable everything except 0
        if (gingivalMargin === '<-12') {
            const allValues = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '>12'];
            return allValues.slice(1); // Return all values except 0
        }
        
        if (gingivalMargin < 0) {
            const disabledCount = Math.abs(gingivalMargin);
            const allValues = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '>12'];
            
            // Disable the last 'disabledCount' values
            return allValues.slice(-disabledCount);
        }
    }
    
    return [];
});

// Function to check if a probing depth value should be disabled
const isProbingDepthDisabled = (value) => {
    return getDisabledProbingDepthValues.value.includes(value);
};

// Function to find the closest allowed probing depth value
const getClosestAllowedProbingDepth = (currentValue) => {
    const allValues = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '>12'];
    const disabledValues = getDisabledProbingDepthValues.value;
    const allowedValues = allValues.filter(val => !disabledValues.includes(val));
    
    if (allowedValues.length === 0) return 0;
    
    // If current value is allowed, return it
    if (allowedValues.includes(currentValue)) {
        return currentValue;
    }
    
    // Special case: if gingival margin is '<-12', only 0 is allowed
    if (selectedPosition.value && periodontalData.value[selectedPosition.value]?.gingivalMargin === '<-12') {
        return 0;
    }
    
    // Find the closest allowed value
    let closest = allowedValues[0];
    let minDiff = Math.abs(getAllowedValueIndex(currentValue, allValues) - getAllowedValueIndex(closest, allValues));
    
    for (const allowedValue of allowedValues) {
        const diff = Math.abs(getAllowedValueIndex(currentValue, allValues) - getAllowedValueIndex(allowedValue, allValues));
        if (diff < minDiff) {
            minDiff = diff;
            closest = allowedValue;
        }
    }
    
    return closest;
};

// Helper function to get index of value in array (handles '>12' case)
const getAllowedValueIndex = (value, array) => {
    const index = array.indexOf(value);
    return index !== -1 ? index : array.length;
};

// Function to check if probing depth indicates disease (>= 5)
const isProbingDepthDiseased = (position) => {
    if (!position || !periodontalData.value[position]) return false;
    const depth = periodontalData.value[position].probingDepth;
    return typeof depth === 'number' && depth >= 5;
};

// Function to update selected depth
const updateSelectedDepth = () => {
    if (selectedPosition.value && periodontalData.value[selectedPosition.value]) {
        const currentValue = periodontalData.value[selectedPosition.value][selectedMeasurementType.value];
        selectedDepth.value = currentValue !== null && currentValue !== undefined ? currentValue : (selectedMeasurementType.value === 'probingDepth' ? 2 : 0);
    }
};

// Watch for changes in initial data
watch(
    () => props.initialData,
    (newData) => {
        if (isComponentActive.value) {
            periodontalData.value = JSON.parse(JSON.stringify(newData));
            originalData.value = JSON.parse(JSON.stringify(newData));
            updateSelectedDepth();
        }
    },
    { deep: true }
);

// Watch for changes in periodontal data to adjust disabled probing depths
watch(
    () => periodontalData.value,
    (newData) => {
        // Check all positions for disabled probing depths and adjust them
        Object.keys(newData).forEach(position => {
            if (newData[position] && typeof newData[position].probingDepth !== 'undefined') {
                const currentProbingDepth = newData[position].probingDepth;
                const gingivalMargin = newData[position].gingivalMargin;
                
                // Special case: if gingival margin is '<-12', only allow probing depth of 0
                if (gingivalMargin === '<-12') {
                    if (currentProbingDepth !== 0) {
                        periodontalData.value[position].probingDepth = 0;
                    }
                }
                // If gingival margin is negative, check if probing depth needs adjustment
                else if (gingivalMargin < 0) {
                    const disabledCount = Math.abs(gingivalMargin);
                    const allValues = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '>12'];
                    const disabledValues = allValues.slice(-disabledCount);
                    
                    if (disabledValues.includes(currentProbingDepth)) {
                        const adjustedValue = getClosestAllowedProbingDepthForPosition(currentProbingDepth, disabledValues);
                        periodontalData.value[position].probingDepth = adjustedValue;
                    }
                }
            }
        });
        
        updateSelectedDepth();
    },
    { deep: true }
);

// Helper function to get closest allowed probing depth for a specific position
const getClosestAllowedProbingDepthForPosition = (currentValue, disabledValues) => {
    const allValues = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '>12'];
    const allowedValues = allValues.filter(val => !disabledValues.includes(val));
    
    if (allowedValues.length === 0) return 0;
    
    // If current value is allowed, return it
    if (allowedValues.includes(currentValue)) {
        return currentValue;
    }
    
    // For '<-12' case, only 0 is allowed
    if (allowedValues.length === 1 && allowedValues[0] === 0) {
        return 0;
    }
    
    // Find the closest allowed value
    let closest = allowedValues[0];
    let minDiff = Math.abs(getAllowedValueIndex(currentValue, allValues) - getAllowedValueIndex(closest, allValues));
    
    for (const allowedValue of allowedValues) {
        const diff = Math.abs(getAllowedValueIndex(currentValue, allValues) - getAllowedValueIndex(allowedValue, allValues));
        if (diff < minDiff) {
            minDiff = diff;
            closest = allowedValue;
        }
    }
    
    return closest;
};

// Watch for selected position changes to update selected depth
watch(
    selectedPosition,
    (newPosition) => {
        updateSelectedDepth();
    },
    { immediate: true }
);

// Watch for measurement type changes to update selected depth
watch(
    selectedMeasurementType,
    (newType) => {
        updateSelectedDepth();
    }
);

// Track the currently selected periodontal type
const currentPeriodontalType = ref(null);

// Emits the current periodontal type when it changes
const selectPeriodontalType = (type) => {
    currentPeriodontalType.value = type;
    emit("periodontal-type-changed", type);
};

// Select measurement type (probing depth or recession)
const selectMeasurementType = (type) => {
    selectedMeasurementType.value = type;
    updateSelectedDepth();
};

// Toggle gingival margin mode
const toggleGingivalMarginMode = () => {
    gingivalMarginMode.value = gingivalMarginMode.value === 'negative' ? 'positive' : 'negative';
    // Reset selected depth when switching modes
    updateSelectedDepth();
};

// Select depth value
const selectDepth = (value) => {
    // Don't allow selection of disabled values
    if (isProbingDepthDisabled(value)) {
        return;
    }
    
    selectedDepth.value = value;
    
    // Update the periodontal data for the current position
    if (selectedPosition.value && periodontalData.value[selectedPosition.value]) {
        periodontalData.value[selectedPosition.value][selectedMeasurementType.value] = value;
        
        // If we're setting gingival margin to a negative value, check if current probing depth should be disabled
        if (selectedMeasurementType.value === 'gingivalMargin' && (value < 0 || value === '<-12')) {
            const currentProbingDepth = periodontalData.value[selectedPosition.value].probingDepth;
            
            // Special case: if setting to '<-12', force probing depth to 0
            if (value === '<-12') {
                periodontalData.value[selectedPosition.value].probingDepth = 0;
            } else {
                // Adjust probing depth to closest allowed value if it becomes disabled
                const adjustedProbingDepth = getClosestAllowedProbingDepth(currentProbingDepth);
                periodontalData.value[selectedPosition.value].probingDepth = adjustedProbingDepth;
            }
        }
    }
};

// Toggle periodontal indicators
const toggleIndicator = (indicatorType) => {
    if (selectedPosition.value && periodontalData.value[selectedPosition.value]) {
        periodontalData.value[selectedPosition.value][indicatorType] = 
            !periodontalData.value[selectedPosition.value][indicatorType];
    }
};

// Handle periodontal position selection and navigation
const selectPeriodontalPosition = (position) => {
    try {
        // Validate position parameter
        if (!position || typeof position !== "string") {
            console.error("Invalid position parameter:", position);
            return;
        }

        // Convert position key to route-friendly format
        const routePosition = position.replace(/_/g, "-");

        // Get current route safely
        const currentRoute = router.currentRoute.value;
        if (!currentRoute || !currentRoute.path) {
            console.error("Invalid current route:", currentRoute);
            return;
        }

        // Get the base path without any existing periodontal position
        let basePath = currentRoute.path;

        // Remove any existing periodontal position from the path
        const periodontalPositionPattern =
            /(disto-palatal|palatal|mesio-palatal|disto-buccal|buccal|mesio-buccal)$/;
        if (periodontalPositionPattern.test(basePath)) {
            basePath = basePath.replace(periodontalPositionPattern, "");
        }

        // Remove trailing slash if exists
        basePath = basePath.endsWith("/") ? basePath.slice(0, -1) : basePath;

        // Build new path with the selected position
        const newPath = `${basePath}/${routePosition}`;

        // Navigate to the new route
        router.push(newPath).catch((error) => {
            console.error("Navigation error:", error);
        });
    } catch (error) {
        console.error("Error in selectPeriodontalPosition:", error);
    }
};

// Check if a position is currently selected
const isPositionSelected = (position) => {
    try {
        if (!props.selectedPosition || !position) return false;
        const normalizedPosition = position.replace(/_/g, "-");
        return props.selectedPosition === normalizedPosition;
    } catch (error) {
        console.error("Error in isPositionSelected:", error);
        return false;
    }
};

// Save changes method
const saveChanges = () => {
    // Mark as saved
    hasBeenSaved.value = true;
    
    // Update original data to current data since it's now saved
    originalData.value = JSON.parse(JSON.stringify(periodontalData.value));
    
    emit("saved", periodontalData.value);
    // Close the component after saving
    emit("close");
};

// Function to revert to original state
const revertToOriginal = () => {
    if (!hasBeenSaved.value && isComponentActive.value) {
        periodontalData.value = JSON.parse(JSON.stringify(originalData.value));
        // Don't emit update:data here - just revert local state
    }
};

// Function to manually cancel changes
const cancelChanges = () => {
    periodontalData.value = JSON.parse(JSON.stringify(originalData.value));
    emit("close");
};

// Watch for route changes to detect navigation away from periodontal section
watch(() => route.params, (newParams, oldParams) => {
    // Check if we're leaving the periodontal section
    if (oldParams?.section === 'periodontal' && newParams?.section !== 'periodontal') {
        revertToOriginal();
        isComponentActive.value = false;
    }
    // Check if we're entering the periodontal section
    else if (newParams?.section === 'periodontal' && oldParams?.section !== 'periodontal') { 
        isComponentActive.value = true;
        hasBeenSaved.value = false;
        // Reset original data when entering periodontal section
        originalData.value = JSON.parse(JSON.stringify(periodontalData.value));
    }
}, { deep: true });

// Watch for tooth changes to reset saved state
watch(() => props.toothId, (newToothId, oldToothId) => {
    if (newToothId !== oldToothId) {
        hasBeenSaved.value = false;
        isComponentActive.value = true;
        originalData.value = JSON.parse(JSON.stringify(periodontalData.value));
    }
});

// Handle component unmounting
onBeforeUnmount(() => {
    isComponentActive.value = false;
    revertToOriginal();
});

// Initialize component
onMounted(() => {
    updateSelectedDepth();
    
    // Store original data for reverting if not saved
    originalData.value = JSON.parse(JSON.stringify(periodontalData.value));
    isComponentActive.value = true;
    hasBeenSaved.value = false;
});
</script>

<style scoped>
/* Periodontal Section Styles */
.periodontal-card {
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100px;
    position: relative;
    transition: box-shadow 0.2s;
}

.periodontal-card:hover {
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.periodontal-card-selected {
    border-color: #3b82f6;
    background-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.periodontal-card-selected .periodontal-value,
.periodontal-card-selected .periodontal-label {
    color: white;
}

.periodontal-card-diseased {
    border-color: #ef4444;
    background-color: #ef4444;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.periodontal-card-diseased .periodontal-value,
.periodontal-card-diseased .periodontal-label {
    color: white;
}

/* Selected diseased card should have a different visual indicator */
.periodontal-card-selected.periodontal-card-diseased {
    border-color: #ffffff;
    background-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5), 0 0 0 5px rgba(239, 68, 68, 0.3);
}

.periodontal-card-selected.periodontal-card-diseased .periodontal-value,
.periodontal-card-selected.periodontal-card-diseased .periodontal-label {
    color: white;
    font-weight: 700;
}

.periodontal-card-content {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin-bottom: 0.5rem;
}

.periodontal-indicators {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2px;
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}

.indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #e5e7eb;
    display: inline-block;
}

.indicator-red {
    background: #ef4444;
}

.indicator-yellow {
    background: #f59e42;
}

.indicator-blue {
    background: #3b82f6;
}

.indicator-orange {
    background: #fb923c;
}

.periodontal-value {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0.5rem;
}

.periodontal-value-secondary {
    color: #6b7280;
}

.periodontal-divider {
    width: 1px;
    height: 70%;
    background: #d1d5db;
    margin: 0 1rem;
}

.periodontal-divider-horizontal {
    background: #e5e7eb;
    width: 100%;
    height: 1px;
    margin: 0.25rem 0;
    display: block;
}

.periodontal-label {
    font-weight: 500;
    color: #374151;
    margin-top: 0.5rem;
}

.periodontal-label-blue {
    color: #3b82f6;
}

.periodontal-title {
    margin-bottom: 1rem;
}

.periodontal-indicator-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.periodontal-indicator-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
}

.indicator-circle {
    width: 0.6rem;
    height: 0.6rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}
</style>
