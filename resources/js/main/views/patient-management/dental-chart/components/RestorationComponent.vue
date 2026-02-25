<template>
    <div class="flex flex-col gap-4 space-y-4 relative h-full">
        <!-- First Row: Restoration Types -->
        <div class="grid grid-cols-3 gap-4">
            <div 
                v-for="type in restorationTypes"
                :key="type.key"
                @click="selectRestorationType(type.key)"
                :class="[
                    'restoration-button',
                    restorationData.selectedType === type.key ? 'restoration-button--active' : ''
                ]"
            >
                <span class="restoration-button__text">
                    {{ $t(`dental_chart.${type.key}`) }}
                </span>
            </div>
        </div>

    <!-- Second Row: Material Types (shows when filling or veneer is selected and a surface is chosen, or when crown is selected, or when inlay/onlay/partial_crown is selected) -->
    <div v-if="(restorationData.selectedType === 'filling' && (restorationData.filling?.selectedSurfaces?.length > 0)) || (restorationData.selectedType === 'veneer' && (restorationData.veneer?.selectedSurfaces?.length > 0)) || (restorationData.selectedType === 'crown') || (restorationData.selectedType === 'inlay' && (restorationData.inlay?.selectedSurfaces?.length > 0)) || (restorationData.selectedType === 'onlay' && (restorationData.onlay?.selectedSurfaces?.length > 0)) || (restorationData.selectedType === 'partial_crown' && (restorationData.partialCrown?.selectedSurfaces?.length > 0))" class="space-y-2">
            <h4 class="text-lg font-medium text-gray-700">{{ $t('dental_chart.material') }}</h4>
            <div class="grid grid-cols-2 gap-4">
                <div 
                    v-for="material in visibleMaterials"
                    :key="material.key"
                    @click="selectMaterial(material.key)"
                    :class="[
                        'restoration-button',
                        ((restorationData.selectedType === 'filling' && restorationData.filling?.material === material.key) || (restorationData.selectedType === 'veneer' && restorationData.veneer?.material === material.key) || (restorationData.selectedType === 'crown' && restorationData.crown?.material === material.key)) ? 'restoration-button--active' : ''
                    ]"
                >
                    <span class="restoration-button__text">
                        {{ $t(`dental_chart.${material.key}`) }}
                    </span>
                </div>
            </div>
        </div>

    <!-- Third Row: Crown Type (shows when crown material is selected) -->
    <div v-if="restorationData.selectedType === 'crown' && restorationData.crown?.material" class="space-y-2">
        <h4 class="text-lg font-medium text-gray-700">{{ $t('dental_chart.crown_type') }}</h4>
        <div class="grid grid-cols-3 gap-4">
            <div 
                v-for="crownType in crownTypes"
                :key="crownType.key"
                @click="selectCrownType(crownType.key)"
                :class="[
                    'restoration-button',
                    restorationData.crown?.crownType === crownType.key ? 'restoration-button--active' : ''
                ]"
            >
                <span class="restoration-button__text">
                    {{ $t(`dental_chart.${crownType.key}`) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Fourth Row: Crown Base (shows when crown type is selected) -->
    <div v-if="restorationData.selectedType === 'crown' && restorationData.crown?.crownType" class="space-y-2">
        <h4 class="text-lg font-medium text-gray-700">{{ $t('dental_chart.crown_base') }}</h4>
        <div class="grid grid-cols-2 gap-4">
            <div 
                v-for="crownBase in crownBases"
                :key="crownBase.key"
                @click="selectCrownBase(crownBase.key)"
                :class="[
                    'restoration-button',
                    restorationData.crown?.crownBase === crownBase.key ? 'restoration-button--active' : ''
                ]"
            >
                <span class="restoration-button__text">
                    {{ $t(`dental_chart.${crownBase.key}`) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Fifth Row: Implant Type (shows when crown base is implant) -->
    <div v-if="restorationData.selectedType === 'crown' && restorationData.crown?.crownBase === 'implant'" class="space-y-2">
        <h4 class="text-lg font-medium text-gray-700">{{ $t('dental_chart.implant_type') }}</h4>
        <div class="grid grid-cols-2 gap-4">
            <div 
                v-for="implantType in implantTypes"
                :key="implantType.key"
                @click="selectImplantType(implantType.key)"
                :class="[
                    'restoration-button',
                    restorationData.crown?.implantType === implantType.key ? 'restoration-button--active' : ''
                ]"
            >
                <span class="restoration-button__text">
                    {{ $t(`dental_chart.${implantType.key}`) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Quality Types (shows when material is selected for filling/veneer/inlay/onlay/partial_crown) -->
    <div v-if="(restorationData.selectedType === 'filling' && restorationData.filling?.material) || (restorationData.selectedType === 'veneer' && restorationData.veneer?.material) || (restorationData.selectedType === 'inlay' && restorationData.inlay?.material) || (restorationData.selectedType === 'onlay' && restorationData.onlay?.material) || (restorationData.selectedType === 'partial_crown' && restorationData.partialCrown?.material)" class="space-y-2">
            <h4 class="text-lg font-medium text-gray-700">{{ $t('dental_chart.quality') }}</h4>
            <div class="grid grid-cols-3 gap-4">
                <div 
                    v-for="quality in qualityTypes"
                    :key="quality.key"
                    @click="selectQuality(quality.key)"
                    :class="[
            'restoration-button',
            ((restorationData.selectedType === 'filling' && restorationData.filling?.quality === quality.key) || (restorationData.selectedType === 'veneer' && restorationData.veneer?.quality === quality.key) || (restorationData.selectedType === 'inlay' && restorationData.inlay?.quality === quality.key) || (restorationData.selectedType === 'onlay' && restorationData.onlay?.quality === quality.key) || (restorationData.selectedType === 'partial_crown' && restorationData.partialCrown?.quality === quality.key)) ? 'restoration-button--active' : ''
                    ]"
                >
                    <span class="restoration-button__text">
                        {{ $t(`dental_chart.${quality.key}`) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Detail Types (shows when quality is selected for filling/veneer/inlay/onlay/partial_crown) -->
        <div v-if="(restorationData.selectedType === 'filling' && restorationData.filling?.quality) || (restorationData.selectedType === 'veneer' && restorationData.veneer?.quality) || (restorationData.selectedType === 'inlay' && restorationData.inlay?.quality) || (restorationData.selectedType === 'onlay' && restorationData.onlay?.quality) || (restorationData.selectedType === 'partial_crown' && restorationData.partialCrown?.quality)" class="space-y-2">
            <h4 class="text-lg font-medium text-gray-700">{{ $t('dental_chart.detail') }}</h4>
            <div class="grid grid-cols-3 gap-4">
                <div 
                    v-for="detail in detailTypes"
                    :key="detail.key"
                    @click="selectDetail(detail.key)"
                    :class="[
                        'restoration-button',
                        ((restorationData.selectedType === 'filling' && restorationData.filling?.detail === detail.key) || (restorationData.selectedType === 'veneer' && restorationData.veneer?.detail === detail.key) || (restorationData.selectedType === 'inlay' && restorationData.inlay?.detail === detail.key) || (restorationData.selectedType === 'onlay' && restorationData.onlay?.detail === detail.key) || (restorationData.selectedType === 'partial_crown' && restorationData.partialCrown?.detail === detail.key)) ? 'restoration-button--active' : ''
                    ]"
                >
                    <span class="restoration-button__text">
                        {{ $t(`dental_chart.${detail.key}`) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="grid grid-cols-3 gap-4 absolute bottom-0 left-0 right-0 p-4 mb-6">
            <div
                class="restoration-button"
                @click="createMonitorItem"
            >
                <span class="restoration-button__text">
                    {{ $t("dental_chart.monitor") }}
                </span>
            </div>
            <div
                class="restoration-button"
                @click="createTreatItem"
            >
                <span class="restoration-button__text">
                    {{ $t("dental_chart.treat") }}
                </span>
            </div>
            <div
                class="restoration-button"
                @click="saveRestoration"
            >
                <span class="restoration-button__text">
                    {{ $t("common.save") }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { message } from 'ant-design-vue';
import apiAdmin from '../../../../../common/composable/apiAdmin';

// Props
const props = defineProps({
    toothId: {
        required: true,
    },
    initialData: {
        default: () => ({}),
    },
    patientId: {
        type: String,
        required: true,
    },
});

// Emits
const emit = defineEmits(['close', 'update:data', 'saved', 'restoration-type-changed', 'crown-type-changed', 'refresh-dental-chart']);

const route = useRoute();

// Initialize composables at top level
const { t } = useI18n();
const { addEditRequestAdmin } = apiAdmin();

// Define the different types arrays
const restorationTypes = computed(() => {
    const toothId = props.toothId;
    
    // For teeth 18-16, show additional restoration options
    if (toothId >= 16 && toothId <= 18 || toothId >= 26 && toothId <= 28 || toothId >= 36 && toothId <= 38 || toothId >= 46 && toothId <= 48) {
        return [
            { key: 'filling' },
            { key: 'inlay' },
            { key: 'onlay' },
            { key: 'partial_crown' },
            { key: 'crown' },
        ];
    }

    // For teeth 14-15, show additional restoration options
    if (toothId >= 14 && toothId <= 15 || toothId >= 24 && toothId <= 25 || toothId >= 34 && toothId <= 35 || toothId >= 44 && toothId <= 45) {
        return [
            { key: 'filling' },
            { key: 'inlay' },
            { key: 'onlay' },
            { key: 'veneer' },
            { key: 'partial_crown' },
            { key: 'crown' },
        ];
    }
    
    // For other teeth, show standard options
    return [
        { key: 'filling' },
        { key: 'veneer' },
        { key: 'crown' },
    ];
});

const materialTypes = [
    { key: 'composite' },
    { key: 'ceramic' },
    { key: 'amalgam' },
    { key: 'gold' },
    { key: 'non_precious_metal' },
    { key: 'temporary' },
];

const qualityTypes = [
    { key: 'sufficient' },
    { key: 'uncertain' },
    { key: 'insufficient' },
];

const detailTypes = [
    { key: 'overhang' },
    { key: 'flush' },
    { key: 'shortfall' },
];

const crownTypes = [
    { key: 'single_crown' },
    { key: 'abutment' },
    { key: 'pontic' },
];

const crownBases = [
    { key: 'natural' },
    { key: 'implant' },
];

const implantTypes = [
    { key: 'bone_level' },
    { key: 'tissue_level' },
];

// When veneer is selected we only show these materials

// Reactive data
const restorationData = ref({
    selectedType: null, // Only one restoration type can be selected (UI state only)
    
        // Filling restoration options
        filling: {
            selectedSurfaces: [], // array of selected tooth surfaces
            material: null, // composite, ceramic, amalgam, gold, non_precious_metal, temporary
            quality: null, // sufficient, uncertain, insufficient
            detail: null, // overhang, flush, shortfall
        },

        // Veneer restoration options (behave similar to filling)
        veneer: {
            selectedSurfaces: [],
            material: null,
            quality: null,
            detail: null,
        },

        // Crown restoration options
        crown: {
            selectedSurfaces: [],
            material: null,
            crownType: null,
            crownBase: null,
            implantType: null,
        },

        // Inlay restoration options
        inlay: {
            selectedSurfaces: [], // mesial, occlusal, distal
            material: null, // composite, ceramic, gold, non_precious_metal
            quality: null, // sufficient, uncertain, insufficient
            detail: null, // overhang, flush, shortfall
        },

        // Onlay restoration options
        onlay: {
            selectedSurfaces: [], // mesial, distal, buccal, palatal
            material: null, // composite, ceramic, gold, non_precious_metal
            quality: null, // sufficient, uncertain, insufficient
            detail: null, // overhang, flush, shortfall
        },

        // Partial crown restoration options
        partialCrown: {
            selectedSurfaces: [], // buccal, palatal, buccal_cusp, palatal_cusp
            material: null, // composite, ceramic, gold, non_precious_metal
            quality: null, // sufficient, uncertain, insufficient
            detail: null, // overhang, flush, shortfall
        },
});

// When veneer is selected we only show these materials
const visibleMaterials = computed(() => {
    if (restorationData.value.selectedType === 'veneer') {
        const allowed = ['composite', 'ceramic', 'gold', 'non_precious_metal'];
        return materialTypes.filter(m => allowed.includes(m.key));
    }
    
    if (restorationData.value.selectedType === 'crown') {
        const allowed = ['composite', 'ceramic', 'gold', 'non_precious_metal'];
        return materialTypes.filter(m => allowed.includes(m.key));
    }

    if (restorationData.value.selectedType === 'inlay') {
        const allowed = ['composite', 'ceramic', 'gold', 'non_precious_metal'];
        return materialTypes.filter(m => allowed.includes(m.key));
    }

    if (restorationData.value.selectedType === 'onlay') {
        const allowed = ['composite', 'ceramic', 'gold', 'non_precious_metal'];
        return materialTypes.filter(m => allowed.includes(m.key));
    }

    if (restorationData.value.selectedType === 'partial_crown') {
        const allowed = ['composite', 'ceramic', 'gold', 'non_precious_metal'];
        return materialTypes.filter(m => allowed.includes(m.key));
    }

    return materialTypes;
});

// Store original data to revert if not saved
const originalData = ref({});
const hasBeenSaved = ref(false);

// Computed property to check if data can be saved
const canSave = computed(() => {
    if (restorationData.value.selectedType === 'filling') {
        return restorationData.value.filling?.selectedSurfaces?.length > 0;
    }
    if (restorationData.value.selectedType === 'veneer') {
        return restorationData.value.veneer?.selectedSurfaces?.length > 0;
    }
    if (restorationData.value.selectedType === 'crown') {
        // Crown doesn't require surface selection, only material selection
        return restorationData.value.crown?.material;
    }
    if (restorationData.value.selectedType === 'inlay') {
        return restorationData.value.inlay?.selectedSurfaces?.length > 0;
    }
    if (restorationData.value.selectedType === 'onlay') {
        return restorationData.value.onlay?.selectedSurfaces?.length > 0;
    }
    if (restorationData.value.selectedType === 'partial_crown') {
        return restorationData.value.partialCrown?.selectedSurfaces?.length > 0;
    }
    return false;
});

// Helper function to emit data without selectedType
const emitUpdateData = () => {
    const updateData = { ...restorationData.value };
    delete updateData.selectedType;
    emit('update:data', updateData);
};

// Methods
const selectRestorationType = (type) => {
    // Allow switching to any restoration type, but preserve data for each type
    restorationData.value.selectedType = type;

    // Emit the restoration type change to parent component
    emit('restoration-type-changed', type);
    
    // Get the surfaces for the newly selected restoration type and emit them
    let surfacesToEmit = [];
    if (type === 'filling') {
        surfacesToEmit = restorationData.value.filling?.selectedSurfaces || [];
    } else if (type === 'veneer') {
        surfacesToEmit = restorationData.value.veneer?.selectedSurfaces || [];
    } else if (type === 'crown') {
        surfacesToEmit = restorationData.value.crown?.selectedSurfaces || [];
    } else if (type === 'inlay') {
        surfacesToEmit = restorationData.value.inlay?.selectedSurfaces || [];
    } else if (type === 'onlay') {
        surfacesToEmit = restorationData.value.onlay?.selectedSurfaces || [];
    } else if (type === 'partial_crown') {
        surfacesToEmit = restorationData.value.partialCrown?.selectedSurfaces || [];
    }
    
    // Update the restoration data with current surfaces for the selected type
    emitUpdateData();
};

const selectMaterial = (material) => {
    // Toggle behavior: clicking the same material again will deselect it
    if (restorationData.value.selectedType === 'filling') {
        if (!restorationData.value.filling) restorationData.value.filling = {};
        if (restorationData.value.filling.material === material) {
            // Deselect
            restorationData.value.filling.material = null;
            restorationData.value.filling.quality = null;
            restorationData.value.filling.detail = null;
        } else {
            // Select new material and reset dependent fields
            restorationData.value.filling.material = material;
            restorationData.value.filling.quality = null;
            restorationData.value.filling.detail = null;
        }
    } else if (restorationData.value.selectedType === 'veneer') {
        if (!restorationData.value.veneer) restorationData.value.veneer = {};
        if (restorationData.value.veneer.material === material) {
            restorationData.value.veneer.material = null;
            restorationData.value.veneer.quality = null;
            restorationData.value.veneer.detail = null;
        } else {
            restorationData.value.veneer.material = material;
            restorationData.value.veneer.quality = null;
            restorationData.value.veneer.detail = null;
        }
    } else if (restorationData.value.selectedType === 'crown') {
        if (!restorationData.value.crown) restorationData.value.crown = {};
        if (restorationData.value.crown.material === material) {
            restorationData.value.crown.material = null;
            restorationData.value.crown.crownType = null;
            restorationData.value.crown.crownBase = null;
            restorationData.value.crown.implantType = null;
        } else {
            restorationData.value.crown.material = material;
            restorationData.value.crown.crownType = null;
            restorationData.value.crown.crownBase = null;
            restorationData.value.crown.implantType = null;
        }
    } else if (restorationData.value.selectedType === 'inlay') {
        if (!restorationData.value.inlay) restorationData.value.inlay = {};
        if (restorationData.value.inlay.material === material) {
            restorationData.value.inlay.material = null;
            restorationData.value.inlay.quality = null;
            restorationData.value.inlay.detail = null;
        } else {
            restorationData.value.inlay.material = material;
            restorationData.value.inlay.quality = null;
            restorationData.value.inlay.detail = null;
        }
    } else if (restorationData.value.selectedType === 'onlay') {
        if (!restorationData.value.onlay) restorationData.value.onlay = {};
        if (restorationData.value.onlay.material === material) {
            restorationData.value.onlay.material = null;
            restorationData.value.onlay.quality = null;
            restorationData.value.onlay.detail = null;
        } else {
            restorationData.value.onlay.material = material;
            restorationData.value.onlay.quality = null;
            restorationData.value.onlay.detail = null;
        }
    } else if (restorationData.value.selectedType === 'partial_crown') {
        if (!restorationData.value.partialCrown) restorationData.value.partialCrown = {};
        if (restorationData.value.partialCrown.material === material) {
            restorationData.value.partialCrown.material = null;
            restorationData.value.partialCrown.quality = null;
            restorationData.value.partialCrown.detail = null;
        } else {
            restorationData.value.partialCrown.material = material;
            restorationData.value.partialCrown.quality = null;
            restorationData.value.partialCrown.detail = null;
        }
    }
    emitUpdateData();
};

const selectCrownType = (crownType) => {
    if (!restorationData.value.crown) restorationData.value.crown = {};
    restorationData.value.crown.crownType = crownType;
    // Reset dependent fields
    restorationData.value.crown.crownBase = null;
    restorationData.value.crown.implantType = null;
    
    // Emit the crown type change for visual effects (e.g., hide root for pontic)
    emit('crown-type-changed', {
        toothId: props.toothId,
        crownType: crownType
    });
    
    emitUpdateData();
};

const selectCrownBase = (crownBase) => {
    if (!restorationData.value.crown) restorationData.value.crown = {};
    restorationData.value.crown.crownBase = crownBase;
    // Reset dependent fields
    if (crownBase !== 'implant') {
        restorationData.value.crown.implantType = null;
    }
    emitUpdateData();
};

const selectImplantType = (implantType) => {
    if (!restorationData.value.crown) restorationData.value.crown = {};
    restorationData.value.crown.implantType = implantType;
    emitUpdateData();
};

const selectQuality = (quality) => {
    if (restorationData.value.selectedType === 'filling') {
        if (!restorationData.value.filling) restorationData.value.filling = {};
        restorationData.value.filling.quality = quality;
        // Reset detail selection when changing quality
        restorationData.value.filling.detail = null;
    } else if (restorationData.value.selectedType === 'veneer') {
        if (!restorationData.value.veneer) restorationData.value.veneer = {};
        restorationData.value.veneer.quality = quality;
        restorationData.value.veneer.detail = null;
    } else if (restorationData.value.selectedType === 'inlay') {
        if (!restorationData.value.inlay) restorationData.value.inlay = {};
        restorationData.value.inlay.quality = quality;
        restorationData.value.inlay.detail = null;
    } else if (restorationData.value.selectedType === 'onlay') {
        if (!restorationData.value.onlay) restorationData.value.onlay = {};
        restorationData.value.onlay.quality = quality;
        restorationData.value.onlay.detail = null;
    } else if (restorationData.value.selectedType === 'partial_crown') {
        if (!restorationData.value.partialCrown) restorationData.value.partialCrown = {};
        restorationData.value.partialCrown.quality = quality;
        restorationData.value.partialCrown.detail = null;
    }
    emitUpdateData();
};

const selectDetail = (detail) => {
    if (restorationData.value.selectedType === 'filling') {
        if (!restorationData.value.filling) restorationData.value.filling = {};
        restorationData.value.filling.detail = detail;
    } else if (restorationData.value.selectedType === 'veneer') {
        if (!restorationData.value.veneer) restorationData.value.veneer = {};
        restorationData.value.veneer.detail = detail;
    } else if (restorationData.value.selectedType === 'inlay') {
        if (!restorationData.value.inlay) restorationData.value.inlay = {};
        restorationData.value.inlay.detail = detail;
    } else if (restorationData.value.selectedType === 'onlay') {
        if (!restorationData.value.onlay) restorationData.value.onlay = {};
        restorationData.value.onlay.detail = detail;
    } else if (restorationData.value.selectedType === 'partial_crown') {
        if (!restorationData.value.partialCrown) restorationData.value.partialCrown = {};
        restorationData.value.partialCrown.detail = detail;
    }
    emitUpdateData();
};

const saveRestoration = () => {
    if (!canSave.value) {
        return;
    }
    
    // Mark as saved
    hasBeenSaved.value = true;
    
    // Update original data to current data since it's now saved
    originalData.value = JSON.parse(JSON.stringify(restorationData.value));
    
    // Create data object without selectedType for saving
    const saveData = { ...restorationData.value };
    delete saveData.selectedType;
    
    // Emit saved event with data (excluding selectedType)
    emit('saved', saveData);
    
    // Emit close event to hide the component
    emit('close');
};

// Function to create monitor item from restoration data
const createMonitorItem = async () => {
    if (!restorationData.value.selectedType) {
        message.warning(t('dental_chart.select_restoration_first'));
        return;
    }
    
    try {
        const restorationDescription = generateRestorationDescription();
        
        const payload = {
            tooth_number: props.toothId.toString(),
            type: 'normal', // Default priority for restoration monitoring
            content: `${t('dental_chart.restoration_monitor')}: ${restorationDescription}`,
            comment: t('dental_chart.restoration_monitor_comment')
        };

        await addEditRequestAdmin({
            url: `patients/${props.patientId}/treat-monitor`,
            data: payload,
            successMessage: t('dental_chart.monitor_item_created')
        });
        
        emit('refresh-dental-chart');
        message.success(t('dental_chart.monitor_item_created'));
    } catch (error) {
        console.error('Error creating monitor item:', error);
        message.error(t('dental_chart.error_creating_monitor'));
    }
};

// Function to create treat item from restoration data
const createTreatItem = async () => {
    if (!restorationData.value.selectedType) {
        message.warning(t('dental_chart.select_restoration_first'));
        return;
    }
    
    try {
        const restorationDescription = generateRestorationDescription();
        
        const payload = {
            tooth_number: props.toothId.toString(),
            type: 'important', // Default priority for restoration treatment
            content: `${t('dental_chart.restoration_treat')}: ${restorationDescription}`,
            comment: t('dental_chart.restoration_treat_comment')
        };

        await addEditRequestAdmin({
            url: `patients/${props.patientId}/treat-monitor`,
            data: payload,
            successMessage: t('dental_chart.treat_item_created')
        });
        
        emit('refresh-dental-chart');
        message.success(t('dental_chart.treat_item_created'));
    } catch (error) {
        console.error('Error creating treat item:', error);
        message.error(t('dental_chart.error_creating_treat'));
    }
};

// Helper function to generate restoration description
const generateRestorationDescription = () => {
    const restoration = restorationData.value;
    
    if (!restoration.selectedType) return '';
    
    let description = t(`dental_chart.${restoration.selectedType}`);
    
    if (restoration.selectedType === 'filling') {
        const filling = restoration.filling;
        if (filling.material) description += ` - ${t(`dental_chart.${filling.material}`)}`;
        if (filling.quality) description += ` - ${t(`dental_chart.${filling.quality}`)}`;
        if (filling.detail) description += ` - ${t(`dental_chart.${filling.detail}`)}`;
    } else if (restoration.selectedType === 'veneer') {
        const veneer = restoration.veneer;
        if (veneer.material) description += ` - ${t(`dental_chart.${veneer.material}`)}`;
        if (veneer.quality) description += ` - ${t(`dental_chart.${veneer.quality}`)}`;
        if (veneer.detail) description += ` - ${t(`dental_chart.${veneer.detail}`)}`;
    } else if (restoration.selectedType === 'crown') {
        const crown = restoration.crown;
        if (crown.material) description += ` - ${t(`dental_chart.${crown.material}`)}`;
        if (crown.crownType) description += ` - ${t(`dental_chart.${crown.crownType}`)}`;
        if (crown.crownBase) description += ` - ${t(`dental_chart.${crown.crownBase}`)}`;
        if (crown.implantType) description += ` - ${t(`dental_chart.${crown.implantType}`)}`;
    } else if (restoration.selectedType === 'inlay') {
        const inlay = restoration.inlay;
        if (inlay.material) description += ` - ${t(`dental_chart.${inlay.material}`)}`;
        if (inlay.quality) description += ` - ${t(`dental_chart.${inlay.quality}`)}`;
        if (inlay.detail) description += ` - ${t(`dental_chart.${inlay.detail}`)}`;
    } else if (restoration.selectedType === 'onlay') {
        const onlay = restoration.onlay;
        if (onlay.material) description += ` - ${t(`dental_chart.${onlay.material}`)}`;
        if (onlay.quality) description += ` - ${t(`dental_chart.${onlay.quality}`)}`;
        if (onlay.detail) description += ` - ${t(`dental_chart.${onlay.detail}`)}`;
    } else if (restoration.selectedType === 'partial_crown') {
        const partialCrown = restoration.partialCrown;
        if (partialCrown.material) description += ` - ${t(`dental_chart.${partialCrown.material}`)}`;
        if (partialCrown.quality) description += ` - ${t(`dental_chart.${partialCrown.quality}`)}`;
        if (partialCrown.detail) description += ` - ${t(`dental_chart.${partialCrown.detail}`)}`;
    }
    
    return description;
};

// Function to revert to original state
const revertToOriginal = () => {
    if (!hasBeenSaved.value) {
        restorationData.value = JSON.parse(JSON.stringify(originalData.value));
        
        // Create data object without selectedType
        const updateData = { ...restorationData.value };
        delete updateData.selectedType;
        
        emit('update:data', updateData);
    }
};

// Function to manually cancel changes
const cancelChanges = () => {
    restorationData.value = JSON.parse(JSON.stringify(originalData.value));
    
    // Create data object without selectedType
    const updateData = { ...restorationData.value };
    delete updateData.selectedType;
    
    emit('update:data', updateData);
    emit('close');
};

// Watch for route changes to detect navigation away
watch(() => route.params, (newParams, oldParams) => {
    // If user navigates away from restoration section without saving, revert changes
    if (oldParams?.section === 'restoration' && newParams?.section !== 'restoration') {
        revertToOriginal();
    }
}, { deep: true });

// Watch for tooth changes to reset saved state
watch(() => props.toothId, (newToothId, oldToothId) => {
    if (newToothId !== oldToothId) {
        hasBeenSaved.value = false;
        originalData.value = JSON.parse(JSON.stringify(restorationData.value));
    }
});

// Watch for initialData changes to update component state
watch(() => props.initialData, (newInitialData) => {
    if (newInitialData && Object.keys(newInitialData).length > 0) {
        restorationData.value = { 
            selectedType: restorationData.value.selectedType, // Preserve current selectedType
            ...newInitialData 
        };
        
        // Re-detect restoration type if selectedType is not set
        if (!restorationData.value.selectedType) {
            detectRestorationTypeFromData();
        }
    }
}, { deep: true });

// Watch for restoration type changes to emit the correct surfaces
watch(() => restorationData.value.selectedType, (newType, oldType) => {
    if (newType && newType !== oldType) {
        // When restoration type changes, emit an update with the correct surfaces for this type
        setTimeout(() => {
            emitUpdateData();
        }, 0);
    }
});

// Handle component unmounting
onBeforeUnmount(() => {
    revertToOriginal();
});

// Initialize data from props
onMounted(() => {
    if (props.initialData && Object.keys(props.initialData).length > 0) {
        // Merge initial data with default structure to ensure all objects exist
        restorationData.value = { 
            selectedType: null, // UI state, not from props
            filling: {
                selectedSurfaces: [],
                material: null,
                quality: null,
                detail: null,
                ...props.initialData.filling
            },
            veneer: {
                selectedSurfaces: [],
                material: null,
                quality: null,
                detail: null,
                ...props.initialData.veneer
            },
            crown: {
                selectedSurfaces: [],
                material: null,
                crownType: null,
                crownBase: null,
                implantType: null,
                ...props.initialData.crown
            }
        };
        
        // Auto-detect which restoration type should be selected based on available data
        detectRestorationTypeFromData();
    }
    
    originalData.value = JSON.parse(JSON.stringify(restorationData.value));
});

// Function to detect restoration type from loaded data
const detectRestorationTypeFromData = () => {
    // Check if filling has data (surfaces, material, quality, or detail)
    if (restorationData.value.filling && (
        (restorationData.value.filling.selectedSurfaces && restorationData.value.filling.selectedSurfaces.length > 0) ||
        restorationData.value.filling.material ||
        restorationData.value.filling.quality ||
        restorationData.value.filling.detail
    )) {
        restorationData.value.selectedType = 'filling';
        // Emit the restoration type change to parent component
        emit('restoration-type-changed', 'filling');
        // Emit initial data to parent to sync surfaces
        emitUpdateData();
        return;
    }
    
    // Check if veneer has data
    if (restorationData.value.veneer && (
        (restorationData.value.veneer.selectedSurfaces && restorationData.value.veneer.selectedSurfaces.length > 0) ||
        restorationData.value.veneer.material ||
        restorationData.value.veneer.quality ||
        restorationData.value.veneer.detail
    )) {
        restorationData.value.selectedType = 'veneer';
        // Emit the restoration type change to parent component
        emit('restoration-type-changed', 'veneer');
        // Emit initial data to parent to sync surfaces
        emitUpdateData();
        return;
    }
    
    // Check if crown has data
    if (restorationData.value.crown && (
        (restorationData.value.crown.selectedSurfaces && restorationData.value.crown.selectedSurfaces.length > 0) ||
        restorationData.value.crown.material ||
        restorationData.value.crown.crownType ||
        restorationData.value.crown.crownBase ||
        restorationData.value.crown.implantType
    )) {
        restorationData.value.selectedType = 'crown';
        // Emit the restoration type change to parent component
        emit('restoration-type-changed', 'crown');
        // Emit initial data to parent to sync surfaces
        emitUpdateData();
        return;
    }
    
    // Check if inlay has data
    if (restorationData.value.inlay && (
        (restorationData.value.inlay.selectedSurfaces && restorationData.value.inlay.selectedSurfaces.length > 0) ||
        restorationData.value.inlay.material ||
        restorationData.value.inlay.quality ||
        restorationData.value.inlay.detail
    )) {
        restorationData.value.selectedType = 'inlay';
        emit('restoration-type-changed', 'inlay');
        emitUpdateData();
        return;
    }
    
    // Check if onlay has data
    if (restorationData.value.onlay && (
        (restorationData.value.onlay.selectedSurfaces && restorationData.value.onlay.selectedSurfaces.length > 0) ||
        restorationData.value.onlay.material ||
        restorationData.value.onlay.quality ||
        restorationData.value.onlay.detail
    )) {
        restorationData.value.selectedType = 'onlay';
        emit('restoration-type-changed', 'onlay');
        emitUpdateData();
        return;
    }
    
    // Check if partial crown has data
    if (restorationData.value.partialCrown && (
        (restorationData.value.partialCrown.selectedSurfaces && restorationData.value.partialCrown.selectedSurfaces.length > 0) ||
        restorationData.value.partialCrown.material ||
        restorationData.value.partialCrown.quality ||
        restorationData.value.partialCrown.detail
    )) {
        restorationData.value.selectedType = 'partial_crown';
        emit('restoration-type-changed', 'partial_crown');
        emitUpdateData();
        return;
    }
};

// Expose method to update surfaces from parent
defineExpose({
    updateSurfaces: (surfaces) => {
        if (restorationData.value.selectedType === 'filling') {
            if (!restorationData.value.filling) restorationData.value.filling = {};
            restorationData.value.filling.selectedSurfaces = [...surfaces];
            emitUpdateData();
        } else if (restorationData.value.selectedType === 'veneer') {
            if (!restorationData.value.veneer) restorationData.value.veneer = {};
            // Veneer should only allow buccal and palatal surfaces, and only one at a time
            const allowed = surfaces.filter(s => ['buccal', 'palatal'].includes(s));
            // Take only the last selected surface (most recent selection)
            restorationData.value.veneer.selectedSurfaces = allowed.length > 0 ? [allowed[allowed.length - 1]] : [];
            emitUpdateData();
        } else if (restorationData.value.selectedType === 'crown') {
            if (!restorationData.value.crown) restorationData.value.crown = {};
            // Crown can work without surface selection, but we'll store it if provided
            restorationData.value.crown.selectedSurfaces = [...surfaces];
            emitUpdateData();
        } else if (restorationData.value.selectedType === 'inlay') {
            if (!restorationData.value.inlay) restorationData.value.inlay = {};
            // Inlay allows mesial, occlusal, and distal surfaces
            const allowed = surfaces.filter(s => ['mesial', 'occlusal', 'distal'].includes(s));
            restorationData.value.inlay.selectedSurfaces = [...allowed];
            emitUpdateData();
        } else if (restorationData.value.selectedType === 'onlay') {
            if (!restorationData.value.onlay) restorationData.value.onlay = {};
            // Onlay allows mesial, distal, buccal, and palatal surfaces
            const allowed = surfaces.filter(s => ['mesial', 'distal', 'buccal', 'palatal'].includes(s));
            restorationData.value.onlay.selectedSurfaces = [...allowed];
            emitUpdateData();
        } else if (restorationData.value.selectedType === 'partial_crown') {
            if (!restorationData.value.partialCrown) restorationData.value.partialCrown = {};
            // Partial crown allows buccal, palatal, buccal_cusp, and palatal_cusp surfaces
            const allowed = surfaces.filter(s => ['buccal', 'palatal', 'buccal_cusp', 'palatal_cusp'].includes(s));
            restorationData.value.partialCrown.selectedSurfaces = [...allowed];
            emitUpdateData();
        }
    },
    
    // Method to get current surfaces for the selected restoration type
    getCurrentSurfaces: () => {
        if (restorationData.value.selectedType === 'filling') {
            return restorationData.value.filling?.selectedSurfaces || [];
        } else if (restorationData.value.selectedType === 'veneer') {
            return restorationData.value.veneer?.selectedSurfaces || [];
        } else if (restorationData.value.selectedType === 'crown') {
            return restorationData.value.crown?.selectedSurfaces || [];
        } else if (restorationData.value.selectedType === 'inlay') {
            return restorationData.value.inlay?.selectedSurfaces || [];
        } else if (restorationData.value.selectedType === 'onlay') {
            return restorationData.value.onlay?.selectedSurfaces || [];
        } else if (restorationData.value.selectedType === 'partial_crown') {
            return restorationData.value.partialCrown?.selectedSurfaces || [];
        }
        return [];
    }
});
</script>

<style scoped>
/* Container */
.restoration-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Grid Layout */
.restoration-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 640px) {
    .restoration-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* Restoration Button Styles */
.restoration-button {
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

.restoration-button:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 16px 0 rgba(60, 120, 255, 0.08);
}

.restoration-button--active {
    border-color: #3b82f6 !important;
    background-color: #2563eb !important;
    color: #fff !important;
    box-shadow: 0 4px 16px 0 rgba(60, 120, 255, 0.12);
}

.restoration-button__text {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1.5rem;
    margin-top: 1.2rem;
    color: inherit;
}

/* Responsive adjustments */
@media (max-width: 639px) {
    .restoration-button {
        padding: 1rem;
        min-height: 3.5rem;
    }
    
    .restoration-button__text {
        font-size: 0.9rem;
    }
}
</style>
