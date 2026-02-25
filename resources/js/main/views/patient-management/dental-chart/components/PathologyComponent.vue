<template>
    <div class="flex flex-col gap-4 space-y-4 relative h-[95%]">
        <!-- First Row: Main Pathology Types -->
        <div class="grid grid-cols-3 gap-4">
            <div 
                v-for="type in pathologyTypes"
                :key="type.key"
                @click="selectPathologyType(type.key)"
                :class="[
                    'pathology-button',
                    pathologyData.selectedType === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

        <!-- Second Row: Fracture Types (shows when fracture is selected) -->
        <div v-if="pathologyData.selectedType === 'fracture'" class="grid grid-cols-2 gap-4">
            <div 
                v-for="type in fractureTypes"
                :key="type.key"
                @click="selectFractureType(type.key)"
                :class="[
                    'pathology-button',
                    pathologyData.fracture.fractureType === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

        <!-- Second Row: Fracture Orientation (shows when fracture type is selected) -->
        <div v-if="pathologyData.selectedType === 'fracture' && pathologyData.fracture.fractureType" class="grid grid-cols-2 gap-4">
            <div 
                v-for="type in fractureOrientationTypes"
                :key="type.key"
                @click="selectFractureOrientation(type.key)"
                :class="[
                    'pathology-button',
                    pathologyData.fracture.fractureOrientation === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

        <!-- Second Row: Tooth Wear Types (shows when tooth wear is selected) -->
        <div v-if="pathologyData.selectedType === 'tooth_wear'" class="grid grid-cols-2 gap-4">
            <div 
                v-for="type in toothWearTypes"
                :key="type.key"
                @click="selectToothWearType(type.key)"
                :class="[
                    'pathology-button',
                    pathologyData.toothWear.toothWearType === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

        <!-- Third Row: Tooth Wear Surfaces (shows when tooth wear type is selected, allows multiple selection) -->
        <div v-if="pathologyData.selectedType === 'tooth_wear' && pathologyData.toothWear.toothWearType" class="grid grid-cols-2 gap-4">
            <div 
                v-for="surface in toothWearSurfaces"
                :key="surface.key"
                @click="toggleToothWearSurface(surface.key)"
                :class="[
                    'pathology-button',
                    pathologyData.toothWear.toothWearSurfaces && pathologyData.toothWear.toothWearSurfaces.includes(surface.key) ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${surface.key}`) }}
                </span>
            </div>
        </div>

        <!-- Second Row: Discoloration Colors (shows when discoloration is selected) -->
        <div v-if="pathologyData.selectedType === 'discoloration'" class="grid grid-cols-3 gap-4">
            <div 
                v-for="color in discolorationColors"
                :key="color.key"
                @click="selectDiscolorationColor(color.key)"
                :class="[
                    'pathology-button',
                    pathologyData.discoloration.discolorationColor === color.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${color.key}`) }}
                </span>
            </div>
        </div>

        <!-- Second Row: Development Disorder Options (shows when development disorder is selected) -->
        <div v-if="pathologyData.selectedType === 'development_disorder'" class="grid grid-cols-2 gap-4">
            <div 
                v-for="option in developmentDisorderOptions"
                :key="option.key"
                @click="selectDevelopmentDisorderOption(option.key)"
                :class="[
                    'pathology-button',
                    pathologyData.developmentDisorder.developmentDisorderOption === option.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${option.key}`) }}
                </span>
            </div>
        </div>

        <!-- Second Row: Apical Development Disorder Options (shows when apical is selected) -->
        <div v-if="pathologyData.selectedType === 'apical'" class="grid grid-cols-2 gap-4">
            <div 
                v-for="option in developmentDisorderOptions"
                :key="option.key + '_apical'"
                @click="selectApicalDevelopmentOption(option.key)"
                :class="[
                    'pathology-button',
                    pathologyData.apical.developmentDisorderOption === option.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${option.key}`) }}
                </span>
            </div>
        </div>

    <!-- Second Row: Dentin Row (shows when decay pathology type is selected) -->
    <div v-if="pathologyData.selectedType === 'decay'" class="grid grid-cols-2 gap-4">
            <div 
                v-for="type in dentinTypes"
                :key="type.key"
                @click="selectDentinType(type.key)"
                :class="[
                    'pathology-button',
                    (pathologyData.selectedType === 'decay' ? pathologyData.decay.dentinType : pathologyData.apical.dentinType) === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

    <!-- Third Row: Cavitation Row (shows when dentin is selected for decay) -->
    <div v-if="(pathologyData.selectedType === 'decay' && (pathologyData.decay.dentinType === 'dentin' || pathologyData.decay.dentinType === 'enamel'))" class="grid grid-cols-2 gap-4">
            <div 
                v-for="type in cavitationTypes"
                :key="type.key"
                @click="selectCavitationType(type.key)"
                :class="[
                    'pathology-button',
                    (pathologyData.selectedType === 'decay' ? pathologyData.decay.cavitationType : pathologyData.apical.cavitationType) === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

    <!-- Fourth Row: Pulp Row (shows when both dentin and cavitation are selected for decay) -->
    <div v-if="(pathologyData.selectedType === 'decay' && pathologyData.decay.dentinType === 'dentin' && pathologyData.decay.cavitationType === 'cavitation')" class="grid grid-cols-2 gap-4">
            <div 
                v-for="type in pulpTypes"
                :key="type.key"
                @click="selectPulpType(type.key)"
                :class="[
                    'pathology-button',
                    (pathologyData.selectedType === 'decay' ? pathologyData.decay.pulpType : pathologyData.apical.pulpType) === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

        <!-- Fifth Row: Classification Row (shows when conditions are met for decay) -->
        <div v-if="
            (pathologyData.selectedType === 'decay' && (
                (pathologyData.decay.dentinType === 'enamel' && (pathologyData.decay.cavitationType === 'cavitation' || pathologyData.decay.cavitationType === 'no_cavitations')) ||
                (pathologyData.decay.dentinType === 'dentin' && ((pathologyData.decay.cavitationType === 'no_cavitations') || (pathologyData.decay.cavitationType === 'cavitation' && (pathologyData.decay.pulpType === 'pulp_involved' || pathologyData.decay.pulpType === 'pulp_not_involved'))))
            ))
        " class="grid grid-cols-4 gap-4">
            <div 
                v-for="type in classificationTypes"
                :key="type.key"
                @click="selectClassificationType(type.key)"
                :class="[
                    'pathology-button',
                    (pathologyData.selectedType === 'decay' ? pathologyData.decay.classificationType : pathologyData.apical.classificationType) === type.key ? 'pathology-button--active' : ''
                ]"
            >
                <span class="pathology-button__text">
                    {{ type.key.toUpperCase() }}
                </span>
            </div>
        </div>

        <!-- Save and Cancel Buttons -->
        <div class="grid grid-cols-3 gap-4 absolute bottom-0 left-0 right-0 p-4 mb-6">
            <div
                class="pathology-button"
                @click="createMonitorItem"
            >
                <span class="pathology-button__text">
                    {{ $t("dental_chart.monitor") }}
                </span>
            </div>
            <div
                class="pathology-button"
                @click="createTestItem"
            >
                <span class="pathology-button__text">
                    {{ $t("dental_chart.test") }}
                </span>
            </div>
            <div
                class="pathology-button"
                @click="savePathology"
            >
                <span class="pathology-button__text">
                    {{ $t("common.save") }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { message } from 'ant-design-vue';
import apiAdmin from '../../../../../common/composable/apiAdmin';

// Props
const props = defineProps({
    toothId: {
        type: Number,
        required: true,
    },
    initialData: {
        type: Object,
        default: () => ({}),
    },
    patientId: {
        type: String,
        required: true,
    },
});

// Emits
const emit = defineEmits(['close', 'update:data', 'saved', 'pathology-type-changed', 'refresh-dental-chart']);

const route = useRoute();

// Initialize composables at top level
const { t } = useI18n();
const { addEditRequestAdmin } = apiAdmin();

// Define the different types arrays
const pathologyTypes = [
    { key: 'decay' },
    { key: 'fracture' },
    { key: 'tooth_wear' },
    { key: 'discoloration' },
    { key: 'apical' },
    { key: 'development_disorder' },
];

const dentinTypes = [
    { key: 'dentin' },
    { key: 'enamel' },
];

const fractureTypes = [
    { key: 'crown_fracture' },
    { key: 'root_fracture' },
];

const fractureOrientationTypes = [
    { key: 'vertical' },
    { key: 'horizontal' },
];

const toothWearTypes = [
    { key: 'abrasion' },
    { key: 'erosion' },
];

const toothWearSurfaces = [
    { key: 'buccal' },
    { key: 'palatal' },
];

const discolorationColors = [
    { key: 'gray' },
    { key: 'red' },
    { key: 'yellow' },
];

const developmentDisorderOptions = [
    { key: 'yes' },
    { key: 'no' },
];

const cavitationTypes = [
    { key: 'cavitation' },
    { key: 'no_cavitations' },
];

const pulpTypes = [
    { key: 'pulp_involved' },
    { key: 'pulp_not_involved' },
];

const classificationTypes = [
    { key: 'c1' },
    { key: 'c2' },
    { key: 'c3' },
    { key: 'c4' },
];

// Reactive data
const pathologyData = ref({
    selectedType: null, // Only one pathology type can be selected (UI state only)
    
    // Decay pathology options
    decay: {
        dentinType: null,   // dentin or enamel
        cavitationType: null, // cavitation or no_cavitations
        pulpType: null,     // pulp_involved or pulp_not_involved
        classificationType: null, // c1, c2, c3, c4
        selectedSurfaces: [], // array of selected tooth surfaces
    },
    
    // Fracture pathology options
    fracture: {
        fractureType: null, // crown_fracture or root_fracture
        fractureOrientation: null, // vertical or horizontal
    },
    
    // Tooth wear pathology options
    toothWear: {
        toothWearType: null, // abrasion or erosion
        toothWearSurfaces: [], // array of selected surfaces: buccal, palatal (can select multiple)
    },
    
    // Discoloration pathology options
    discoloration: {
        discolorationColor: null, // gray, red, yellow
    },
    
    // Apical pathology options - only yes/no
    apical: {
        developmentDisorderOption: null, // yes or no (same as development disorder)
    },
    
    // Development disorder pathology options
    developmentDisorder: {
        developmentDisorderOption: null, // yes or no
    },
});

// Store original data to revert if not saved
const originalData = ref({});
const hasBeenSaved = ref(false);

// Helper function to emit data with selectedType included
const emitUpdateData = () => {
    const updateData = { ...pathologyData.value };
    // Keep selectedType for proper state management and restoration
    emit('update:data', updateData);
};

// Method to update surfaces from parent component
const updateSurfaces = (surfaces) => {
    if (pathologyData.value.selectedType === 'decay') {
        pathologyData.value.decay.selectedSurfaces = [...surfaces];
    } else if (pathologyData.value.selectedType === 'tooth_wear') {
        pathologyData.value.toothWear.toothWearSurfaces = [...surfaces];
    }
    emitUpdateData();
};

// Expose the updateSurfaces method to parent
defineExpose({
    updateSurfaces
});

// Methods
const selectPathologyType = (type) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.selectedType === type) {
        pathologyData.value.selectedType = null;
        emit('pathology-type-changed', null);
    } else {
        pathologyData.value.selectedType = type;
        emit('pathology-type-changed', type);
    }
    emitUpdateData();
};

const selectFractureType = (type) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.fracture.fractureType === type) {
        pathologyData.value.fracture.fractureType = null;
        pathologyData.value.fracture.fractureOrientation = null;
    } else {
        pathologyData.value.fracture.fractureType = type;
        // Reset orientation selection when changing fracture type
        pathologyData.value.fracture.fractureOrientation = null;
    }
    emitUpdateData();
};

const selectFractureOrientation = (type) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.fracture.fractureOrientation === type) {
        pathologyData.value.fracture.fractureOrientation = null;
    } else {
        pathologyData.value.fracture.fractureOrientation = type;
    }
    emitUpdateData();
};

const selectToothWearType = (type) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.toothWear.toothWearType === type) {
        pathologyData.value.toothWear.toothWearType = null;
        pathologyData.value.toothWear.toothWearSurfaces = [];
    } else {
        pathologyData.value.toothWear.toothWearType = type;
        // Reset surfaces selection when changing tooth wear type
        pathologyData.value.toothWear.toothWearSurfaces = [];
    }
    emitUpdateData();
};

const toggleToothWearSurface = (surface) => {
    if (!pathologyData.value.toothWear.toothWearSurfaces) {
        pathologyData.value.toothWear.toothWearSurfaces = [];
    }
    
    const index = pathologyData.value.toothWear.toothWearSurfaces.indexOf(surface);
    if (index > -1) {
        // Remove if already selected
        pathologyData.value.toothWear.toothWearSurfaces.splice(index, 1);
    } else {
        // Add if not selected
        pathologyData.value.toothWear.toothWearSurfaces.push(surface);
    }
    emitUpdateData();
};

const selectDiscolorationColor = (color) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.discoloration.discolorationColor === color) {
        pathologyData.value.discoloration.discolorationColor = null;
    } else {
        pathologyData.value.discoloration.discolorationColor = color;
    }
    emitUpdateData();
};

const selectDevelopmentDisorderOption = (option) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.developmentDisorder.developmentDisorderOption === option) {
        pathologyData.value.developmentDisorder.developmentDisorderOption = null;
    } else {
        pathologyData.value.developmentDisorder.developmentDisorderOption = option;
    }
    emitUpdateData();
};

const selectApicalDevelopmentOption = (option) => {
    // Toggle selection - if already selected, deselect it
    if (pathologyData.value.apical.developmentDisorderOption === option) {
        pathologyData.value.apical.developmentDisorderOption = null;
    } else {
        pathologyData.value.apical.developmentDisorderOption = option;
    }
    emitUpdateData();
};

const selectDentinType = (type) => {
    const currentPathology = pathologyData.value.decay;
    
    // Toggle selection - if already selected, deselect it
    if (currentPathology.dentinType === type) {
        currentPathology.dentinType = null;
        currentPathology.cavitationType = null;
        currentPathology.pulpType = null;
        currentPathology.classificationType = null;
    } else {
        currentPathology.dentinType = type;
        // Reset subsequent selections when changing dentin type
        currentPathology.cavitationType = null;
        currentPathology.pulpType = null;
        currentPathology.classificationType = null;
    }
    emit('update:data', { ...pathologyData.value });
};

const selectCavitationType = (type) => {
    const currentPathology = pathologyData.value.decay;
    
    // Toggle selection - if already selected, deselect it
    if (currentPathology.cavitationType === type) {
        currentPathology.cavitationType = null;
        currentPathology.pulpType = null;
        currentPathology.classificationType = null;
    } else {
        currentPathology.cavitationType = type;
        // Reset pulp and classification selection when changing cavitation type
        currentPathology.pulpType = null;
        currentPathology.classificationType = null;
    }
    emit('update:data', { ...pathologyData.value });
};

const selectPulpType = (type) => {
    const currentPathology = pathologyData.value.decay;
    
    // Toggle selection - if already selected, deselect it
    if (currentPathology.pulpType === type) {
        currentPathology.pulpType = null;
        currentPathology.classificationType = null;
    } else {
        currentPathology.pulpType = type;
        // Reset classification when changing pulp type
        currentPathology.classificationType = null;
    }
    emit('update:data', { ...pathologyData.value });
};

const selectClassificationType = (type) => {
    const currentPathology = pathologyData.value.decay;
    
    // Toggle selection - if already selected, deselect it
    if (currentPathology.classificationType === type) {
        currentPathology.classificationType = null;
    } else {
        currentPathology.classificationType = type;
    }
    emit('update:data', { ...pathologyData.value });
};

const savePathology = () => {
    // Mark as saved
    hasBeenSaved.value = true;
    
    // Update original data to current data since it's now saved
    originalData.value = JSON.parse(JSON.stringify(pathologyData.value));
    
    // Create data object with selectedType included for saving (this helps with restoration)
    const saveData = { ...pathologyData.value };
    
    // Emit saved event with data (including selectedType for proper restoration)
    emit('saved', saveData);
    
    // Emit close event to hide the component
    emit('close');
};

// Function to create monitor item from pathology data
const createMonitorItem = async () => {
    if (!pathologyData.value.selectedType) {
        message.warning(t('dental_chart.select_pathology_first'));
        return;
    }
    
    try {
        const pathologyDescription = generatePathologyDescription();
        
        const payload = {
            tooth_number: props.toothId.toString(),
            type: 'important', // Default priority for pathology monitoring
            content: `${t('dental_chart.pathology_monitor')}: ${pathologyDescription}`,
            comment: t('dental_chart.pathology_monitor_comment')
        };

        await addEditRequestAdmin({
            url: `patients/${props.patientId}/treat-monitor`,
            data: payload,
            successMessage: t('dental_chart.monitor_item_created')
        });
        
        emit('refresh-dental-chart');
        message.success(t('dental_chart.monitor_item_created'));
        savePathology();
    } catch (error) {
        console.error('Error creating monitor item:', error);
        message.error(t('dental_chart.error_creating_monitor'));
    }
};

// Function to create test item from pathology data
const createTestItem = async () => {
    if (!pathologyData.value.selectedType) {
        message.warning(t('dental_chart.select_pathology_first'));
        return;
    }
    
    try {
        const pathologyDescription = generatePathologyDescription();
        
        const payload = {
            tooth_number: props.toothId.toString(),
            type: 'urgent', // Default priority for pathology testing
            content: `${t('dental_chart.pathology_test')}: ${pathologyDescription}`,
            comment: t('dental_chart.pathology_test_comment')
        };

        await addEditRequestAdmin({
            url: `patients/${props.patientId}/treat-monitor`,
            data: payload,
            successMessage: t('dental_chart.test_item_created')
        });
        
        emit('refresh-dental-chart');
        message.success(t('dental_chart.test_item_created'));
        savePathology();
    } catch (error) {
        console.error('Error creating test item:', error);
        message.error(t('dental_chart.error_creating_test'));
    }
};

// Helper function to generate pathology description
const generatePathologyDescription = () => {
    const pathology = pathologyData.value;
    
    if (!pathology.selectedType) return '';
    
    let description = t(`dental_chart.${pathology.selectedType}`);
    
    if (pathology.selectedType === 'decay') {
        const decay = pathology.decay;
        if (decay.dentinType) description += ` - ${t(`dental_chart.${decay.dentinType}`)}`;
        if (decay.cavitationType) description += ` - ${t(`dental_chart.${decay.cavitationType}`)}`;
        if (decay.pulpType) description += ` - ${t(`dental_chart.${decay.pulpType}`)}`;
        if (decay.classificationType) description += ` - ${t(`dental_chart.${decay.classificationType}`)}`;
    } else if (pathology.selectedType === 'fracture') {
        const fracture = pathology.fracture;
        if (fracture.fractureType) description += ` - ${t(`dental_chart.${fracture.fractureType}`)}`;
        if (fracture.fractureOrientation) description += ` - ${t(`dental_chart.${fracture.fractureOrientation}`)}`;
    } else if (pathology.selectedType === 'tooth_wear') {
        const toothWear = pathology.toothWear;
        if (toothWear.toothWearType) description += ` - ${t(`dental_chart.${toothWear.toothWearType}`)}`;
    } else if (pathology.selectedType === 'discoloration') {
        const discoloration = pathology.discoloration;
        if (discoloration.discolorationColor) description += ` - ${t(`dental_chart.${discoloration.discolorationColor}`)}`;
    }
    
    return description;
};

// Function to revert to original state
const revertToOriginal = () => {
    if (!hasBeenSaved.value) {
        pathologyData.value = JSON.parse(JSON.stringify(originalData.value));
        emit('update:data', { ...pathologyData.value });
    }
};

// Function to manually cancel changes
const cancelChanges = () => {
    pathologyData.value = JSON.parse(JSON.stringify(originalData.value));
    
    // Create data object without selectedType
    const updateData = { ...pathologyData.value };
    delete updateData.selectedType;
    
    emit('update:data', updateData);
    emit('close');
};

// Watch for route changes to detect navigation away
watch(() => route.params, (newParams, oldParams) => {
    // If user navigates away from pathology section without saving, revert changes
    if (oldParams?.section === 'pathology' && newParams?.section !== 'pathology') {
        revertToOriginal();
    }
}, { deep: true });

// Watch for changes in initialData prop (when surfaces are updated from parent)
watch(() => props.initialData, (newInitialData) => {
    if (newInitialData && Object.keys(newInitialData).length > 0) {
        // Deep merge the new data to preserve any unsaved changes to pathology selections
        const mergedData = { ...pathologyData.value };
        
        // Update decay surfaces if they exist in the new data
        if (newInitialData.decay && newInitialData.decay.selectedSurfaces) {
            mergedData.decay.selectedSurfaces = [...newInitialData.decay.selectedSurfaces];
        }
        
        // Update tooth wear surfaces if they exist in the new data
        if (newInitialData.toothWear && newInitialData.toothWear.toothWearSurfaces) {
            mergedData.toothWear.toothWearSurfaces = [...newInitialData.toothWear.toothWearSurfaces];
        }
        
        // Update other pathology data while preserving UI state
        Object.keys(newInitialData).forEach(key => {
            if (key !== 'selectedType' && newInitialData[key]) {
                mergedData[key] = { ...mergedData[key], ...newInitialData[key] };
            }
        });
        
        pathologyData.value = mergedData;
        emitUpdateData();
    }
}, { deep: true });

// Watch for tooth changes to reset saved state
watch(() => props.toothId, (newToothId, oldToothId) => {
    if (newToothId !== oldToothId) {
        hasBeenSaved.value = false;
        // Reset data for new tooth
        if (props.initialData && Object.keys(props.initialData).length > 0) {
            pathologyData.value = { ...pathologyData.value, ...props.initialData };
            // Update original data for new tooth
            originalData.value = JSON.parse(JSON.stringify(pathologyData.value));
        }
    }
});

// Handle component unmounting
onBeforeUnmount(() => {
    revertToOriginal();
});

// Initialize data from props
onMounted(() => {
    if (props.initialData && Object.keys(props.initialData).length > 0) {
        pathologyData.value = { ...pathologyData.value, ...props.initialData };
    }
    
    // Emit initial pathology type if it exists
    if (pathologyData.value.selectedType) {
        emit('pathology-type-changed', pathologyData.value.selectedType);
    }
    
    // Store original data for reverting if not saved
    originalData.value = JSON.parse(JSON.stringify(pathologyData.value));
});
</script>

<style scoped>
/* Container */
.pathology-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Grid Layout */
.pathology-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 640px) {
    .pathology-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Pathology Button Styles */
.pathology-button {
    cursor: pointer;
    border-radius: 1rem;
    border: 2px solid #e5e7eb;
    transition: all 0.2s ease-in-out;
    text-align: center;
    background-color: #fff;
    color: #374151;
    padding-block: 0.8rem;
    box-shadow: 0 2px 8px 0 rgba(60, 120, 255, 0.04);
}

.pathology-button:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 16px 0 rgba(60, 120, 255, 0.08);
}

.pathology-button--active {
    border-color: #3b82f6 !important;
    background-color: #2563eb !important;
    color: #fff !important;
    box-shadow: 0 4px 16px 0 rgba(60, 120, 255, 0.12);
}

.pathology-button__text {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1.5rem;
    margin-top: 1.2rem;
    color: inherit;
}

/* Responsive adjustments */
@media (max-width: 639px) {
    .pathology-button {
        padding: 1rem;
        min-height: 3.5rem;
    }
    
    .pathology-button__text {
        font-size: 0.9rem;
    }
}
</style>
