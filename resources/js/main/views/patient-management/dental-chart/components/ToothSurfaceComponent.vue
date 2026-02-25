<template>
    <div class="tooth-surface-container">
        <!-- First Row: Cervical Buccal -->
        <div class="surface-row animate-slide-down mb-15" :style="{ animationDelay: '0ms' }">
            <div 
                :class="[
                    'surface-button large-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('cervical_buccal'),
                        'surface-button--unavailable': !availableSurfaces.includes('cervical_buccal')
                    }
                ]"
                @click="toggleSurface('cervical_buccal')"
            >
                <span v-if="availableSurfaces.includes('cervical_buccal')" class="surface-text">
                    {{ $t('dental_chart.cervical_buccal') }}
                </span>
            </div>
        </div>

        <!-- Second Row: Buccal -->
        <div class="surface-row animate-slide-down" :style="{ animationDelay: '100ms' }">
            <div 
                :class="[
                    'surface-button medium-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('buccal'),
                        'surface-button--unavailable': !availableSurfaces.includes('buccal')
                    }
                ]"
                @click="toggleSurface('buccal')"
            >
                <span v-if="availableSurfaces.includes('buccal')" class="surface-text">
                    {{ $t('dental_chart.buccal') }}
                </span>
            </div>
        </div>

        <!-- Third Row: Mesial, Incisal, Distal -->
        <div class="surface-row three-buttons animate-slide-down" :style="{ animationDelay: '200ms' }">
            <div 
                :class="[
                    'surface-button small-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('mesial'),
                        'surface-button--unavailable': !availableSurfaces.includes('mesial')
                    }
                ]"
                @click="toggleSurface('mesial')"
            >
                <span v-if="availableSurfaces.includes('mesial')" class="surface-text">
                    {{ $t('dental_chart.mesial') }}
                </span>
            </div>
            <div 
                :class="[
                    'surface-button small-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('incisal'),
                        'surface-button--unavailable': !availableSurfaces.includes('incisal')
                    }
                ]"
                @click="toggleSurface('incisal')"
            >
                <span v-if="availableSurfaces.includes('incisal')" class="surface-text">
                    {{ $t('dental_chart.incisal') }}
                </span>
            </div>
            <div 
                :class="[
                    'surface-button small-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('distal'),
                        'surface-button--unavailable': !availableSurfaces.includes('distal')
                    }
                ]"
                @click="toggleSurface('distal')"
            >
                <span v-if="availableSurfaces.includes('distal')" class="surface-text">
                    {{ $t('dental_chart.distal') }}
                </span>
            </div>
        </div>

        <!-- Fourth Row: Palatal -->
        <div class="surface-row animate-slide-down" :style="{ animationDelay: '300ms' }">
            <div 
                :class="[
                    'surface-button medium-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('palatal'),
                        'surface-button--unavailable': !availableSurfaces.includes('palatal')
                    }
                ]"
                @click="toggleSurface('palatal')"
            >
                <span v-if="availableSurfaces.includes('palatal')" class="surface-text">
                    {{ $t('dental_chart.palatal') }}
                </span>
            </div>
        </div>

        <!-- Fifth Row: Cervical Palatal -->
        <div class="surface-row animate-slide-down mt-15" :style="{ animationDelay: '400ms' }">
            <div 
                :class="[
                    'surface-button large-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('cervical_palatal'),
                        'surface-button--unavailable': !availableSurfaces.includes('cervical_palatal')
                    }
                ]"
                @click="toggleSurface('cervical_palatal')"
            >
                <span v-if="availableSurfaces.includes('cervical_palatal')" class="surface-text">
                    {{ $t('dental_chart.cervical_palatal') }}
                </span>
            </div>
        </div>

        <!-- Sixth Row: Class 4 buttons -->
        <div class="surface-row two-buttons animate-slide-down" :style="{ animationDelay: '500ms' }">
            <div 
                :class="[
                    'surface-button class4-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('class4_mesial'),
                        'surface-button--unavailable': !availableSurfaces.includes('class4_mesial')
                    }
                ]"
                @click="toggleSurface('class4_mesial')"
            >
                <span v-if="availableSurfaces.includes('class4_mesial')" class="surface-text">
                    {{ $t('dental_chart.class4_mesial') }}
                </span>
            </div>
            <div 
                :class="[
                    'surface-button class4-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('class4_distal'),
                        'surface-button--unavailable': !availableSurfaces.includes('class4_distal')
                    }
                ]"
                @click="toggleSurface('class4_distal')"
            >
                <span v-if="availableSurfaces.includes('class4_distal')" class="surface-text">
                    {{ $t('dental_chart.class4_distal') }}
                </span>
            </div>
        </div>

        <!-- Seventh Row: Surface buttons -->
        <div class="surface-row two-buttons animate-slide-down" :style="{ animationDelay: '600ms' }">
            <div 
                :class="[
                    'surface-button surface-type-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('buccal_surface'),
                        'surface-button--unavailable': !availableSurfaces.includes('buccal_surface')
                    }
                ]"
                @click="toggleSurface('buccal_surface')"
            >
                <span v-if="availableSurfaces.includes('buccal_surface')" class="surface-text">
                    {{ $t('dental_chart.buccal_surface') }}
                </span>
            </div>
            <div 
                :class="[
                    'surface-button surface-type-button',
                    { 
                        'surface-button--active': selectedSurfaces.includes('palatal_surface'),
                        'surface-button--unavailable': !availableSurfaces.includes('palatal_surface')
                    }
                ]"
                @click="toggleSurface('palatal_surface')"
            >
                <span v-if="availableSurfaces.includes('palatal_surface')" class="surface-text">
                    {{ $t('dental_chart.palatal_surface') }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Props
const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    availableSurfaces: {
        type: Array,
        default: () => []
    },
    pathologyType: {
        type: String,
        default: null
    }
});

// Emits
const emit = defineEmits(['update:modelValue', 'surface-selected']);

// Reactive data
const selectedSurfaces = ref([...props.modelValue]);

// Computed properties
const surfacesBasedOnPathology = computed(() => {
    if (props.pathologyType === 'decay') {
        return [
            'cervical_buccal',
            'buccal', 
            'mesial',
            'incisal',
            'distal',
            'palatal',
            'cervical_palatal',
            'class4_mesial',
            'class4_distal',
            'buccal_surface',
            'palatal_surface'
        ];
    }
    
    // Handle restoration types
    if (props.pathologyType === 'filling') {
        return [
            'cervical_buccal',
            'buccal', 
            'mesial',
            'incisal',
            'distal',
            'palatal',
            'cervical_palatal',
            'class4_mesial',
            'class4_distal',
            'buccal_surface',
            'palatal_surface'
        ];
    }

    // Veneer should only allow buccal and palatal surfaces
    if (props.pathologyType === 'veneer') {
        return [
            'buccal',
            'palatal'
        ];
    }

    // Inlay should only allow mesial, occlusal, and distal surfaces
    if (props.pathologyType === 'inlay') {
        return [
            'mesial',
            'incisal', // Using incisal as occlusal equivalent
            'distal'
        ];
    }

    // Onlay should only allow mesial, distal, buccal, and palatal surfaces
    if (props.pathologyType === 'onlay') {
        return [
            'mesial',
            'distal',
        ];
    }

    // Partial crown should only allow buccal, palatal, buccal_cusp, and palatal_cusp surfaces
    if (props.pathologyType === 'partial_crown') {
        return [
            'mesial',
            'distal',
            'incisal',
            'buccal_surface',
            'palatal_surface'
        ];
    }
    
    // For other pathology types or no pathology, return empty array (all unavailable)
    return [];
});

const availableSurfaces = computed(() => {
    if (props.pathologyType) {
        return surfacesBasedOnPathology.value;
    }
    return props.availableSurfaces;
});

// Methods
const toggleSurface = (surface) => {
    if (!availableSurfaces.value.includes(surface)) {
        return; // Don't allow selection of unavailable surfaces
    }

    const index = selectedSurfaces.value.indexOf(surface);
    
    if (index > -1) {
        // Deselecting the surface
        selectedSurfaces.value.splice(index, 1);
        
        // Handle dependent surfaces when deselecting
        if (surface === 'mesial' && selectedSurfaces.value.includes('class4_mesial')) {
            // If mesial is deselected, also deselect class4_mesial
            const class4MesialIndex = selectedSurfaces.value.indexOf('class4_mesial');
            selectedSurfaces.value.splice(class4MesialIndex, 1);
        }
        
        if (surface === 'distal' && selectedSurfaces.value.includes('class4_distal')) {
            // If distal is deselected, also deselect class4_distal
            const class4DistalIndex = selectedSurfaces.value.indexOf('class4_distal');
            selectedSurfaces.value.splice(class4DistalIndex, 1);
        }
        
        if (surface === 'palatal' && selectedSurfaces.value.includes('palatal_surface')) {
            // If palatal is deselected, also deselect palatal_surface
            const palatalSurfaceIndex = selectedSurfaces.value.indexOf('palatal_surface');
            selectedSurfaces.value.splice(palatalSurfaceIndex, 1);
        }
    } else {
        // Special handling for veneer: only one surface can be selected
        if (props.pathologyType === 'veneer') {
            // Clear all existing selections before adding the new one
            selectedSurfaces.value = [surface];
        } else {
            // Selecting the surface for other pathology types
            selectedSurfaces.value.push(surface);
            
            // Handle dependent surfaces when selecting
            if (surface === 'class4_mesial' && !selectedSurfaces.value.includes('mesial')) {
                // If class4_mesial is selected, also select mesial
                selectedSurfaces.value.push('mesial');
            }
            
            if (surface === 'class4_distal' && !selectedSurfaces.value.includes('distal')) {
                // If class4_distal is selected, also select distal
                selectedSurfaces.value.push('distal');
            }
            
            if (surface === 'palatal_surface' && !selectedSurfaces.value.includes('palatal')) {
                // If palatal_surface is selected, also select palatal
                selectedSurfaces.value.push('palatal');
            }
        }
    }
    
    emit('update:modelValue', [...selectedSurfaces.value]);
    emit('surface-selected', surface, selectedSurfaces.value.includes(surface));
};

// Watch for prop changes
watch(() => props.modelValue, (newValue) => {
    selectedSurfaces.value = [...newValue];
}, { deep: true });

watch(() => props.pathologyType, (newPathologyType) => {
    // Clear selections when pathology type changes
    selectedSurfaces.value = [];
    emit('update:modelValue', []);
});
</script>

<style scoped>
.tooth-surface-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
    padding: 16px;
    max-width: 300px;
    margin: 0 auto;
}

.surface-row {
    display: flex;
    gap: 8px;
    justify-content: center;
    width: 100%;
}

.surface-row.three-buttons {
    gap: 4px;
}

.surface-row.two-buttons {
    gap: 8px;
}

.surface-button {
    cursor: pointer;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    transition: all 0.2s ease-in-out;
    text-align: center;
    
    color: #374151;
    padding: 12px 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
}

.surface-button:hover:not(.surface-button--unavailable) {
    border-color: #3b82f6;
    background-color: #eff6ff;
    box-shadow: 0 2px 6px 0 rgba(59, 130, 246, 0.15);
}

.surface-button--active {
    border-color: #3b82f6 !important;
    background-color: #2563eb !important;
    color: #fff !important;
    box-shadow: 0 4px 12px 0 rgba(37, 99, 235, 0.25);
}

.surface-button--unavailable {
    border: #7a7a7a 1px dashed !important;
    cursor: not-allowed !important;
    opacity: 0.7;
}

/* .surface-button--unavailable:hover {
    background-color: #374151 !important;
    border-color: #374151 !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
} */

.surface-text {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: inherit;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Size variants */
.large-button {
    width: 100%;
    max-width: 200px;
}

.medium-button {
    width: 120px;
}

.small-button {
    width: 60px;
    padding: 8px 4px;
}

.class4-button {
    width: 90px;
}

.surface-type-button {
    width: 90px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .tooth-surface-container {
        max-width: 280px;
        padding: 12px;
    }
    
    .surface-button {
        padding: 10px 6px;
        min-height: 44px;
    }
    
    .surface-text {
        font-size: 0.8125rem;
    }
    
    .large-button {
        max-width: 180px;
    }
    
    .medium-button {
        width: 100px;
    }
    
    .small-button {
        width: 50px;
        padding: 6px 2px;
    }
    
    .class4-button {
        width: 75px;
    }
    
    .surface-type-button {
        width: 75px;
    }
}

/* Animation keyframes and classes */
@keyframes slideDown {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slide-down {
    animation: slideDown 0.4s ease-out forwards;
    opacity: 0;
    animation-fill-mode: both;
}

/* Add a subtle bounce effect for better visual feedback */
@keyframes slideDownBounce {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    60% {
        opacity: 1;
        transform: translateY(5px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Alternative animation class (can be used by changing animate-slide-down to animate-slide-down-bounce) */
.animate-slide-down-bounce {
    animation: slideDownBounce 0.5s ease-out forwards;
    opacity: 0;
    animation-fill-mode: both;
}
</style>
