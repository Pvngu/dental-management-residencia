<template>
    <div class="container mx-auto overflow-hidden size-full grid">
        <div class="flex size-full my-auto h-[calc(100svh-64px)]">
            <div class="size-full mx-4 relative w-9/12">
                <div
                    class="flex size-full gap-4 transition-transform ease-in-out duration-700 relative"
                    :class="{
                        'scale-75 -translate-x-20':
                            $route.params.section === 'pathology' ||
                            $route.params.section === 'restoration',
                    }"
                >
                    <!-- Tooth Selector -->
                    <div
                        class="flex flex-col items-center w-fit px-2 min-w-0 max-h-full overflow-y-auto absolute top-0 left-0 z-10"
                        style="scrollbar-width: none; -ms-overflow-style: none"
                    >
                        <!-- All Teeth in Vertical Layout -->
                        <div
                            class="flex flex-col gap-1.5 w-full max-w-[60px] py-6"
                        >
                            <div
                                v-for="toothNum in allTeeth"
                                :key="toothNum"
                                @click="navigateToTooth(toothNum)"
                                :class="[
                                    'w-8 h-8 rounded-full border-2 flex items-center justify-center text-xs font-medium cursor-pointer transition-all duration-200',
                                    toothNum === toothId
                                        ? 'bg-blue-500 text-white border-blue-500 ring-2 ring-blue-200 scale-110'
                                        : 'bg-white border-gray-300 text-gray-700 hover:bg-blue-50 hover:border-blue-300',
                                ]"
                            >
                                {{ toothNum }}
                            </div>
                        </div>
                    </div>
                    <!-- Left side with tooth visualization -->
                    <div
                        class="relative flex flex-col items-center justify-center lg:w-1/3 gap-10 flex-1 max-w-[400px] mx-auto"
                    >
                        <!-- <h2 class="text-2xl font-semibold mb-6">{{ $t("dental_chart.tooth") }} {{ toothId }}</h2> -->
                        <ToothComponent
                            :tooth="toothData"
                            :type="toothType"
                            view="front"
                            :currentCondition="''"
                            :currentSurface="''"
                            size="44%"
                            style="
                                filter: drop-shadow(
                                    0 0 24px rgba(0, 0, 0, 0.1)
                                );
                            "
                        />
                        <div class="relative w-full flex justify-center">
                            <div
                                v-if="toothData.conditions.to_be_extracted"
                                class="absolute -top-8 left-1/2 -translate-x-1/2 text-red-500 font-bold text-lg"
                            >
                                TO BE EXTRACTED
                            </div>
                            <ToothComponent
                                :tooth="toothData"
                                :type="toothType"
                                view="top"
                                :currentCondition="''"
                                :currentSurface="''"
                                size="44%"
                                style="
                                    filter: drop-shadow(
                                        0 0 24px rgba(0, 0, 0, 0.1)
                                    );
                                "
                            />
                            <div
                                v-if="toothData.conditions.to_be_extracted"
                                class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-red-500 font-bold text-lg"
                            >
                                TO BE EXTRACTED
                            </div>
                        </div>
                        <ToothComponent
                            :tooth="toothData"
                            :type="toothType"
                            view="upside-down"
                            :currentCondition="''"
                            :currentSurface="''"
                            size="44%"
                            style="
                                filter: drop-shadow(
                                    0 0 24px rgba(0, 0, 0, 0.1)
                                );
                            "
                        />
                    </div>
                </div>
                <transition
                    name="fade"
                    mode="out-in"
                    enter-active-class="transition-opacity duration-300"
                    leave-active-class="transition-opacity duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="$route.params.section === 'pathology'"
                        class="absolute top-1/2 transform -translate-y-1/2 right-0"
                    >
                        <ToothSurfaceComponent
                            v-model="currentSelectedSurfaces"
                            :pathology-type="currentPathologyType"
                            @surface-selected="onSurfaceSelected"
                        />
                    </div>
                </transition>
                <transition
                    name="fade"
                    mode="out-in"
                    enter-active-class="transition-opacity duration-300"
                    leave-active-class="transition-opacity duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="$route.params.section === 'restoration'"
                        class="absolute top-1/2 transform -translate-y-1/2 right-0"
                    >
                        <ToothSurfaceComponent
                            v-model="currentSelectedSurfaces"
                            :pathology-type="currentRestorationType"
                            @surface-selected="onSurfaceSelected"
                        />
                    </div>
                </transition>
            </div>

            <!-- Right side with treatment options and conditions -->
            <div
                class="w-full space-y-6 bg-white rounded-lg m-2 px-12 py-10 overflow-y-scroll h-full relative"
            >
                <div class="absolute top-4 right-4">
                    <a-button
                        type="text"
                        @click="showResetConfirm"
                        :aria-label="t('common.reset')"
                    >
                        <template #icon>
                            <RetweetOutlined style="font-size: 20px" />
                        </template>
                    </a-button>
                </div>
                <!-- Pathology Section (Full View) -->
                <div class="size-full" v-if="currentSection === 'pathology'">
                    <div class="border-b mb-6">
                        <router-link
                            :to="{
                                name: 'admin.patients.tooth_detail',
                                params: { id: patientId, toothId: toothId },
                            }"
                        >
                            <div class="flex items-center text-black">
                                <div class="mr-2"><LeftOutlined /></div>
                                <span class="text-3xl">{{
                                    $t("dental_chart.pathology")
                                }}</span>
                            </div>
                        </router-link>
                    </div>
                    <PathologyComponent
                        ref="pathologyComponentRef"
                        :tooth-id="toothId"
                        :patient-id="patientId"
                        :initial-data="toothData.pathology"
                        @close="closeSection"
                        @update:data="updatePathologyData"
                        @saved="onPathologySaved"
                        @pathology-type-changed="onPathologyTypeChanged"
                        @refresh-dental-chart="refreshDentalChart"
                    />
                </div>

                <!-- Restoration Section (Full View) -->
                <div class="size-full" v-if="currentSection === 'restoration'">
                    <div class="border-b mb-6">
                        <router-link
                            :to="{
                                name: 'admin.patients.tooth_detail',
                                params: { id: patientId, toothId: toothId },
                            }"
                        >
                            <div class="flex items-center text-black">
                                <div class="mr-2"><LeftOutlined /></div>
                                <span class="text-3xl">{{
                                    $t("dental_chart.restoration")
                                }}</span>
                            </div>
                        </router-link>
                    </div>
                    <RestorationComponent
                        ref="restorationComponentRef"
                        :tooth-id="toothId"
                        :initial-data="toothData.restoration"
                        @close="closeSection"
                        @update:data="updateRestorationData"
                        @saved="onRestorationSaved"
                        @restoration-type-changed="onRestorationTypeChanged"
                        @refresh-dental-chart="refreshDentalChart"
                    />
                </div>

                <!-- Periodontal Section (Full View) -->
                <div class="size-full" v-if="currentSection === 'periodontal'">
                    <div class="border-b mb-6">
                        <router-link
                            :to="{
                                name: 'admin.patients.tooth_detail',
                                params: { id: patientId, toothId: toothId },
                            }"
                        >
                            <div class="flex items-center text-black">
                                <div class="mr-2"><LeftOutlined /></div>
                                <span class="text-3xl">{{
                                    $t("dental_chart.periodontal")
                                }}</span>
                            </div>
                        </router-link>
                    </div>
                    <PeriodontalComponent
                        ref="periodontalComponentRef"
                        :tooth-id="toothId"
                        :initial-data="toothData.periodontal"
                        :selected-position="currentPosition"
                        @close="closeSection"
                        @saved="onPeriodontalSaved"
                        @periodontal-type-changed="onPeriodontalTypeChanged"
                    />
                </div>

                <!-- Default View -->
                <div v-else-if="!currentSection">
                    <div class="border-b">
                        <router-link
                            :to="{
                                name: 'admin.patients.detail',
                                params: {
                                    id: patientId,
                                    tab: 'clinical-care',
                                    childtab: 'dental-chart',
                                },
                            }"
                        >
                            <div class="flex items-center text-black mb-6">
                                <div class="mr-2"><LeftOutlined /></div>
                                <span class="text-3xl">{{
                                    $t("patients.dental")
                                }}</span>
                            </div>
                        </router-link>
                    </div>

                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <!-- To be Extracted Card -->
                        <div
                            @click="showToBeExtractedConfirm"
                            :class="[
                                'dental-card',
                                toothData.conditions.to_be_extracted
                                    ? 'dental-card-selected'
                                    : 'dental-card-default',
                            ]"
                        >
                            <div
                                :class="[
                                    'dental-card-icon',
                                    toothData.conditions.to_be_extracted
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                <HolderOutlined style="font-size: 28px" />
                            </div>
                            <span
                                :class="[
                                    'dental-card-text',
                                    toothData.conditions.to_be_extracted
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                {{ $t("dental_chart.to_be_extracted") }}
                            </span>
                        </div>

                        <!-- Missing Card -->
                        <div
                            @click="showMissingConfirm"
                            :class="[
                                'dental-card',
                                toothData.conditions.missing
                                    ? 'dental-card-selected'
                                    : 'dental-card-default',
                            ]"
                        >
                            <div
                                :class="[
                                    'dental-card-icon',
                                    toothData.conditions.missing
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                <CloseCircleOutlined style="font-size: 28px" />
                            </div>
                            <span
                                :class="[
                                    'dental-card-text',
                                    toothData.conditions.missing
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                {{ $t("dental_chart.missing") }}
                            </span>
                        </div>

                        <!-- Pathology Card -->
                        <div
                            @click="navigateToSection('pathology')"
                            :class="[
                                'dental-card',
                                currentSection === 'pathology'
                                    ? 'dental-card-selected'
                                    : 'dental-card-default',
                            ]"
                        >
                            <div
                                :class="[
                                    'dental-card-icon',
                                    currentSection === 'pathology'
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                <BugOutlined style="font-size: 28px" />
                            </div>
                            <span
                                :class="[
                                    'dental-card-text',
                                    currentSection === 'pathology'
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                {{ $t("dental_chart.pathology") }}
                            </span>
                        </div>

                        <!-- Restoration Card -->
                        <div
                            @click="navigateToSection('restoration')"
                            :class="[
                                'dental-card',
                                currentSection === 'restoration'
                                    ? 'dental-card-selected'
                                    : 'dental-card-default',
                            ]"
                        >
                            <div
                                :class="[
                                    'dental-card-icon',
                                    currentSection === 'restoration'
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                <ToolOutlined style="font-size: 28px" />
                            </div>
                            <span
                                :class="[
                                    'dental-card-text',
                                    currentSection === 'restoration'
                                        ? 'text-white'
                                        : '',
                                ]"
                            >
                                {{ $t("dental_chart.restoration") }}
                            </span>
                        </div>
                    </div>

                    <!-- Dental Tests Section -->
                    <div class="mt-4">
                        <DentalTestsComponent
                            :test-data="toothData.tests"
                            @update:test-data="updateTestData"
                        />
                    </div>

                    <!-- Periodontal Section -->
                    <div class="mt-4">
                        <h3 class="text-lg font-medium">
                            {{ t("dental_chart.periodontal") }}
                        </h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div
                                v-for="(position, key) in periodontalPositions"
                                :key="key"
                                class="periodontal-card"
                                :class="{
                                    'periodontal-card-selected':
                                        currentPosition ===
                                        key.replace(/_/g, '-'),
                                    'periodontal-card-diseased':
                                        isProbingDepthDiseased(key),
                                }"
                                @click="navigateToPeriodontalPosition(key)"
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
                                                    toothData.periodontal?.[key]
                                                        ?.bleeding,
                                            }"
                                        ></span>
                                        <span
                                            class="indicator"
                                            :class="{
                                                'indicator-yellow':
                                                    toothData.periodontal?.[key]
                                                        ?.plaque,
                                            }"
                                        ></span>
                                        <span
                                            class="indicator"
                                            :class="{
                                                'indicator-blue':
                                                    toothData.periodontal?.[key]
                                                        ?.tartar,
                                            }"
                                        ></span>
                                        <span
                                            class="indicator"
                                            :class="{
                                                'indicator-orange':
                                                    toothData.periodontal?.[key]
                                                        ?.pus,
                                            }"
                                        ></span>
                                    </div>
                                    <span class="periodontal-value">{{
                                        toothData.periodontal?.[key]
                                            ?.probingDepth !== null &&
                                        toothData.periodontal?.[key]
                                            ?.probingDepth !== undefined
                                            ? toothData.periodontal[key]
                                                  .probingDepth || 0
                                            : ""
                                    }}</span>
                                    <span class="periodontal-divider"></span>
                                    <span
                                        class="periodontal-value periodontal-value-secondary"
                                        >{{
                                            toothData.periodontal?.[key]
                                                ?.gingivalMargin !== null &&
                                            toothData.periodontal?.[key]
                                                ?.gingivalMargin !== undefined
                                                ? toothData.periodontal[key]
                                                      .gingivalMargin || 0
                                                : ""
                                        }}</span
                                    >
                                </div>
                                <span
                                    class="periodontal-divider-horizontal"
                                ></span>
                                <span
                                    class="periodontal-label"
                                    :class="{
                                        'periodontal-label-blue':
                                            key === 'disto_palatal',
                                    }"
                                >
                                    {{ t(`dental_chart.${key}`) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Treat and Monitor Section -->
                    <div class="mt-4">
                        <TreatAndMonitorComponent
                            :patient-id="patientId"
                            :treat-monitor-items="treatMonitorItems"
                            :loading="loadingTreatMonitor"
                            @refresh-dental-chart="refreshDentalChart"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import {
    LeftOutlined,
    BugOutlined,
    ToolOutlined,
    RetweetOutlined,
    CloseCircleOutlined,
    LineChartOutlined,
    HolderOutlined,
} from "@ant-design/icons-vue";
import ToothComponent from "../components/ToothComponent.vue";
import DentalTestsComponent from "../components/DentalTestsComponent.vue";
import PathologyComponent from "../components/PathologyComponent.vue";
import RestorationComponent from "../components/RestorationComponent.vue";
import PeriodontalComponent from "../components/PeriodontalComponent.vue";
import ToothSurfaceComponent from "../components/ToothSurfaceComponent.vue";
import TreatAndMonitorComponent from "../components/TreatAndMonitorComponent.vue";
import { Modal, message } from "ant-design-vue";
import useDentalChart from "../composables/useDentalChart.js";
import useToothImageCache from "../composables/useToothImageCache.js";
import apiAdmin from "../../../../../common/composable/apiAdmin";

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

// Use dental chart composable
const {
    dentalChart,
    loadDentalChart,
    saveDentalChartSection,
    getToothData,
    updateToothData,
    hasActiveConditions: hasActiveConditionsHelper,
    resetTooth: resetToothHelper,
    defaultPeriodontal,
    saving,
} = useDentalChart();

// Treat and Monitor Items
const allTreatMonitorItems = ref([]);
// Loading state for treat & monitor fetch
const loadingTreatMonitor = ref(true);

// Computed property to filter treat monitor items for current tooth
const treatMonitorItems = computed(() => {
    const filtered = allTreatMonitorItems.value.filter((item) => {
        const matches =
            item.tooth_number == toothId.value ||
            item.tooth_id == toothId.value ||
            Number(item.tooth_number) === Number(toothId.value) ||
            Number(item.tooth_id) === Number(toothId.value);

        return matches;
    });
    return filtered;
});

const { addEditRequestAdmin } = apiAdmin();

// Use image cache
const { preloadVisibleImages, preloadAllToothImages, preloadingStatus } =
    useToothImageCache();

const patientId = computed(() => route.params.id);
const toothId = computed(() => Number(route.params.toothId));
const currentSection = computed(() => route.params.section);
const currentPosition = computed(() => route.params.position);

const toothType = computed(() => {
    const id = toothId.value;
    return (id >= 11 && id <= 28) || (id >= 21 && id <= 28) ? "upper" : "lower";
});

// Get reactive tooth data from composable
const toothData = computed(() => getToothData(toothId.value));

const allTeeth = [
    18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28, 38, 37, 36,
    35, 34, 33, 32, 31, 41, 42, 43, 44, 45, 46, 47, 48,
];

// Track current selected surfaces for ToothSurfaceComponent
const currentSelectedSurfaces = ref([]);
const currentPathologyType = ref(null);
const currentRestorationType = ref(null);
const pathologyComponentRef = ref(null);
const restorationComponentRef = ref(null);
const periodontalComponentRef = ref(null);

// Store surfaces temporarily when switching pathology types
const tempSurfaceStorage = ref({
    decay: [],
    tooth_wear: [],
});

const periodontalPositions = {
    disto_palatal: true,
    palatal: true,
    mesio_palatal: true,
    disto_buccal: true,
    buccal: true,
    mesio_buccal: true,
};

const updateTestData = async (updatedTests) => {
    try {
        updateToothData(toothId.value, "tests", updatedTests);
        // Only saves tests with actual values (empty/null tests are automatically filtered out)
        await saveDentalChartSection(
            patientId.value,
            toothId.value,
            "tests",
            updatedTests
        );
        message.success(t("dental_chart.test_saved_successfully"));
    } catch (error) {
        console.error("Error saving test data:", error);
        message.error(t("dental_chart.save_error"));
    }
};

const hasActiveConditions = computed(() => {
    return hasActiveConditionsHelper(toothId.value);
});

const loadToothData = async () => {
    try {
        await loadDentalChart(patientId.value);

        // Load treat monitor items
        await loadTreatMonitorItems();

        // After loading, detect and set the initial pathology type if there's existing pathology data
        detectInitialPathologyType();
    } catch (error) {
        console.error("Error loading tooth data:", error);
        message.error(t("dental_chart.load_error"));
    }
};

// Function to load treat monitor items
const loadTreatMonitorItems = async () => {
    loadingTreatMonitor.value = true;
    try {
        const response = await axiosAdmin.get(
            `patients/${patientId.value}/dental-chart`
        );
        allTreatMonitorItems.value = response.data.treat_monitor_items || [];
    } catch (error) {
        console.error("Error loading treat monitor items:", error);
        allTreatMonitorItems.value = [];
    } finally {
        loadingTreatMonitor.value = false;
    }
};

// Function to refresh dental chart data
const refreshDentalChart = async () => {
    await loadToothData();
};

// Function to detect the initial pathology type from loaded data
const detectInitialPathologyType = () => {
    const pathology = toothData.value.pathology;
    const restoration = toothData.value.restoration;

    // Check each pathology type for non-empty/non-default values
    if (
        pathology.decay &&
        (pathology.decay.dentinType ||
            pathology.decay.cavitationType ||
            pathology.decay.pulpType ||
            pathology.decay.classificationType ||
            (pathology.decay.selectedSurfaces &&
                pathology.decay.selectedSurfaces.length > 0))
    ) {
        currentPathologyType.value = "decay";
        currentSelectedSurfaces.value = [
            ...(pathology.decay.selectedSurfaces || []),
        ];
        // Store in temporary storage
        tempSurfaceStorage.value.decay = [
            ...(pathology.decay.selectedSurfaces || []),
        ];
    } else if (
        pathology.fracture &&
        (pathology.fracture.fractureType ||
            pathology.fracture.fractureOrientation)
    ) {
        currentPathologyType.value = "fracture";
    } else if (
        pathology.toothWear &&
        (pathology.toothWear.toothWearType ||
            (pathology.toothWear.toothWearSurfaces &&
                pathology.toothWear.toothWearSurfaces.length > 0))
    ) {
        currentPathologyType.value = "tooth_wear";
        currentSelectedSurfaces.value = [
            ...(pathology.toothWear.toothWearSurfaces || []),
        ];
        // Store in temporary storage
        tempSurfaceStorage.value.tooth_wear = [
            ...(pathology.toothWear.toothWearSurfaces || []),
        ];
    } else if (
        pathology.discoloration &&
        pathology.discoloration.discolorationColor
    ) {
        currentPathologyType.value = "discoloration";
    } else if (
        pathology.apical &&
        (pathology.apical.dentinType ||
            pathology.apical.cavitationType ||
            pathology.apical.pulpType ||
            pathology.apical.classificationType)
    ) {
        currentPathologyType.value = "apical";
    } else if (
        pathology.developmentDisorder &&
        pathology.developmentDisorder.developmentDisorderOption
    ) {
        currentPathologyType.value = "development_disorder";
    }

    // Check restoration types
    if (
        restoration.filling &&
        (restoration.filling.material ||
            restoration.filling.quality ||
            restoration.filling.detail ||
            (restoration.filling.selectedSurfaces &&
                restoration.filling.selectedSurfaces.length > 0))
    ) {
        currentRestorationType.value = "filling";
        // If no pathology surfaces are set, use restoration surfaces
        if (currentSelectedSurfaces.value.length === 0) {
            currentSelectedSurfaces.value = [
                ...(restoration.filling.selectedSurfaces || []),
            ];
        }
    } else if (
        restoration.veneer &&
        (restoration.veneer.material ||
            restoration.veneer.quality ||
            restoration.veneer.detail ||
            (restoration.veneer.selectedSurfaces &&
                restoration.veneer.selectedSurfaces.length > 0))
    ) {
        currentRestorationType.value = "veneer";
        // If no pathology surfaces are set, use restoration surfaces
        if (currentSelectedSurfaces.value.length === 0) {
            currentSelectedSurfaces.value = [
                ...(restoration.veneer.selectedSurfaces || []),
            ];
        }
    } else if (restoration.crown && Object.keys(restoration.crown).length > 0) {
        currentRestorationType.value = "crown";
    } else if (
        restoration.inlay &&
        (restoration.inlay.material ||
            restoration.inlay.quality ||
            restoration.inlay.detail ||
            (restoration.inlay.selectedSurfaces &&
                restoration.inlay.selectedSurfaces.length > 0))
    ) {
        currentRestorationType.value = "inlay";
        if (currentSelectedSurfaces.value.length === 0) {
            currentSelectedSurfaces.value = [
                ...(restoration.inlay.selectedSurfaces || []),
            ];
        }
    } else if (
        restoration.onlay &&
        (restoration.onlay.material ||
            restoration.onlay.quality ||
            restoration.onlay.detail ||
            (restoration.onlay.selectedSurfaces &&
                restoration.onlay.selectedSurfaces.length > 0))
    ) {
        currentRestorationType.value = "onlay";
        if (currentSelectedSurfaces.value.length === 0) {
            currentSelectedSurfaces.value = [
                ...(restoration.onlay.selectedSurfaces || []),
            ];
        }
    } else if (
        restoration.partialCrown &&
        (restoration.partialCrown.material ||
            restoration.partialCrown.quality ||
            restoration.partialCrown.detail ||
            (restoration.partialCrown.selectedSurfaces &&
                restoration.partialCrown.selectedSurfaces.length > 0))
    ) {
        currentRestorationType.value = "partial_crown";
        if (currentSelectedSurfaces.value.length === 0) {
            currentSelectedSurfaces.value = [
                ...(restoration.partialCrown.selectedSurfaces || []),
            ];
        }
    }

    // Set the selectedType in the pathology data for the PathologyComponent
    if (currentPathologyType.value && toothData.value.pathology) {
        toothData.value.pathology.selectedType = currentPathologyType.value;
    }
};

const toggleCondition = async (condition) => {
    if (condition === "missing" || condition === "to_be_extracted") {
        const currentData = toothData.value;
        const newConditions = {
            ...currentData.conditions,
            [condition]: !currentData.conditions[condition],
        };

        try {
            // Update UI immediately for better responsiveness
            updateToothData(toothId.value, "conditions", newConditions);

            // Start image preloading in background (don't await to avoid UI delay)
            preloadVisibleImages("all", [toothId.value]).catch((error) => {
                console.warn("Background image preloading failed:", error);
            });

            // Save to backend
            await saveDentalChartSection(
                patientId.value,
                toothId.value,
                "conditions",
                newConditions
            );
            message.success(t("dental_chart.condition_saved_successfully"));
        } catch (error) {
            console.error("Error saving condition:", error);
            message.error(t("dental_chart.save_error"));
        }
    }
};

const showResetConfirm = () => {
    Modal.confirm({
        title: t("common.confirm"),
        content: t("dental_chart.reset_confirm_message"),
        okText: t("common.yes"),
        cancelText: t("common.no"),
        onOk: () => {
            resetTooth();
        },
    });
};

const showToBeExtractedConfirm = () => {
    Modal.confirm({
        title: t("common.confirm"),
        content: toothData.value.conditions.to_be_extracted
            ? t(
                  "dental_chart.unmark_to_be_extracted_confirm_message",
                  "Are you sure you want to unmark this tooth to be extracted?"
              )
            : t(
                  "dental_chart.mark_to_be_extracted_confirm_message",
                  "Are you sure you want to mark this tooth to be extracted?"
              ),
        okText: t("common.yes"),
        cancelText: t("common.no"),
        onOk: () => {
            toggleCondition("to_be_extracted");
        },
    });
};

const showMissingConfirm = () => {
    Modal.confirm({
        title: t("common.confirm"),
        content: toothData.value.conditions.missing
            ? t("dental_chart.unmark_missing_confirm_message")
            : t("dental_chart.mark_missing_confirm_message"),
        okText: t("common.yes"),
        cancelText: t("common.no"),
        onOk: () => {
            toggleCondition("missing");
        },
    });
};

const updateRestoration = async (type, value) => {
    try {
        const currentData = toothData.value;
        const newRestorations = {
            ...currentData.restorations,
            [type]: value,
        };

        updateToothData(toothId.value, "restorations", newRestorations);
        await saveDentalChartSection(
            patientId.value,
            toothId.value,
            "restorations",
            newRestorations
        );
        message.success(t("dental_chart.restoration_saved_successfully"));
    } catch (error) {
        console.error("Error saving restoration:", error);
        message.error(t("dental_chart.save_error"));
    }
};

const resetTooth = async () => {
    try {
        // Optimistically reset local state
        resetToothHelper(toothId.value);

        // Call API to reset tooth on server
        addEditRequestAdmin({
            url: `patients/${patientId.value}/dental-chart/reset-tooth`,
            data: { tooth_id: toothId.value },
            success: (res) => {
                // Reload dental chart from server to ensure consistency
                loadDentalChart();
                message.success(t("dental_chart.tooth_reset_successfully"));
            },
            error: (err) => {
                console.error("Reset tooth error", err);
                message.error(t("dental_chart.save_error"));
            },
        });
    } catch (error) {
        console.error("Error resetting tooth:", error);
        message.error(t("dental_chart.save_error"));
    }
};

const navigateToTooth = (newToothId) => {
    // Instantly preload all variants for the new tooth to prevent loading delays
    preloadVisibleImages("all", [newToothId]);

    router.push({
        name: route.name,
        params: {
            toothId: newToothId,
        },
    });
};

const navigateToSection = (section) => {
    router.push({
        name: route.name,
        params: {
            ...route.params,
            section: section,
        },
    });
};

const navigateToPeriodontalPosition = (position) => {
    // Convert position key to route-friendly format
    const routePosition = position.replace(/_/g, "-");

    router.push({
        name: route.name,
        params: {
            ...route.params,
            section: "periodontal",
            position: routePosition,
        },
    });
};

const closeSection = () => {
    router.push({
        name: route.name,
        params: {
            id: route.params.id,
            toothId: route.params.toothId,
        },
    });
};

const updatePathologyData = (pathologyData) => {
    updateToothData(toothId.value, "pathology", pathologyData);

    // Sync surfaces with the current pathology type
    if (
        pathologyData.selectedType === "decay" &&
        pathologyData.decay?.selectedSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...pathologyData.decay.selectedSurfaces,
        ];
    } else if (
        pathologyData.selectedType === "tooth_wear" &&
        pathologyData.toothWear?.toothWearSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...pathologyData.toothWear.toothWearSurfaces,
        ];
    }

    // Don't auto-save here - let the PathologyComponent handle saving
};

const onPathologyTypeChanged = (pathologyType) => {
    // Store current surfaces before switching if we have a current type
    if (
        currentPathologyType.value &&
        currentSelectedSurfaces.value.length > 0
    ) {
        if (currentPathologyType.value === "decay") {
            tempSurfaceStorage.value.decay = [...currentSelectedSurfaces.value];
            // Ensure the decay object exists
            if (!toothData.value.pathology.decay) {
                toothData.value.pathology.decay = {};
            }
            toothData.value.pathology.decay.selectedSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        } else if (currentPathologyType.value === "tooth_wear") {
            tempSurfaceStorage.value.tooth_wear = [
                ...currentSelectedSurfaces.value,
            ];
            // Ensure the toothWear object exists
            if (!toothData.value.pathology.toothWear) {
                toothData.value.pathology.toothWear = {};
            }
            toothData.value.pathology.toothWear.toothWearSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        }
    }

    currentPathologyType.value = pathologyType;

    // When pathology type changes, load the corresponding surfaces
    if (pathologyType === "decay") {
        // Try to get surfaces from temporary storage first, then from saved data
        const tempSurfaces = tempSurfaceStorage.value.decay;
        // Ensure the decay object exists before accessing selectedSurfaces
        const savedSurfaces =
            toothData.value.pathology.decay &&
            toothData.value.pathology.decay.selectedSurfaces
                ? toothData.value.pathology.decay.selectedSurfaces
                : [];
        const surfacesToLoad =
            tempSurfaces.length > 0 ? tempSurfaces : savedSurfaces;

        currentSelectedSurfaces.value = [...surfacesToLoad];

        // Also update the pathology component with current surfaces
        if (pathologyComponentRef.value) {
            pathologyComponentRef.value.updateSurfaces([...surfacesToLoad]);
        }
    } else if (pathologyType === "tooth_wear") {
        // Try to get surfaces from temporary storage first, then from saved data
        const tempSurfaces = tempSurfaceStorage.value.tooth_wear;
        // Ensure the toothWear object exists before accessing toothWearSurfaces
        const savedSurfaces =
            toothData.value.pathology.toothWear &&
            toothData.value.pathology.toothWear.toothWearSurfaces
                ? toothData.value.pathology.toothWear.toothWearSurfaces
                : [];
        const surfacesToLoad =
            tempSurfaces.length > 0 ? tempSurfaces : savedSurfaces;

        currentSelectedSurfaces.value = [...surfacesToLoad];

        // Also update the pathology component with current surfaces
        if (pathologyComponentRef.value) {
            pathologyComponentRef.value.updateSurfaces([...surfacesToLoad]);
        }
    } else {
        currentSelectedSurfaces.value = [];
    }
};

const onPathologySaved = async (pathologyData) => {
    try {
        updateToothData(toothId.value, "pathology", pathologyData);
        // Only saves pathology data with actual conditions (empty conditions are filtered out)
        await saveDentalChartSection(
            patientId.value,
            toothId.value,
            "pathology",
            pathologyData
        );
        message.success(t("dental_chart.pathology_saved_successfully"));
    } catch (error) {
        console.error("Error saving pathology data:", error);
        message.error(t("dental_chart.save_error"));
    }
};

const onSurfaceSelected = (surface, isSelected) => {
    // Update the corresponding pathology surfaces based on current type
    if (currentPathologyType.value === "decay") {
        // Ensure the decay object exists
        if (!toothData.value.pathology.decay) {
            toothData.value.pathology.decay = {};
        }
        toothData.value.pathology.decay.selectedSurfaces = [
            ...currentSelectedSurfaces.value,
        ];
        // Also update the pathology component directly
        if (pathologyComponentRef.value) {
            pathologyComponentRef.value.updateSurfaces([
                ...currentSelectedSurfaces.value,
            ]);
        }
    } else if (currentPathologyType.value === "tooth_wear") {
        // Ensure the toothWear object exists
        if (!toothData.value.pathology.toothWear) {
            toothData.value.pathology.toothWear = {};
        }
        toothData.value.pathology.toothWear.toothWearSurfaces = [
            ...currentSelectedSurfaces.value,
        ];
        // Also update the pathology component directly
        if (pathologyComponentRef.value) {
            pathologyComponentRef.value.updateSurfaces([
                ...currentSelectedSurfaces.value,
            ]);
        }
    }

    // Update the corresponding restoration surfaces based on current type
    if (currentRestorationType.value === "filling") {
        // Ensure the filling object exists
        if (!toothData.value.restoration.filling) {
            toothData.value.restoration.filling = {};
        }
        toothData.value.restoration.filling.selectedSurfaces = [
            ...currentSelectedSurfaces.value,
        ];
        // Also update the restoration component directly
        if (restorationComponentRef.value) {
            restorationComponentRef.value.updateSurfaces([
                ...currentSelectedSurfaces.value,
            ]);
        }
    } else if (currentRestorationType.value === "veneer") {
        // Veneer only supports buccal and palatal surfaces
        if (!toothData.value.restoration.veneer) {
            toothData.value.restoration.veneer = {};
        }
        const allowed = currentSelectedSurfaces.value.filter((s) =>
            ["buccal", "palatal"].includes(s)
        );
        toothData.value.restoration.veneer.selectedSurfaces = [...allowed];
        if (restorationComponentRef.value) {
            restorationComponentRef.value.updateSurfaces([...allowed]);
        }
    } else if (currentRestorationType.value === "inlay") {
        // Inlay only supports mesial, incisal (occlusal), and distal surfaces
        if (!toothData.value.restoration.inlay) {
            toothData.value.restoration.inlay = {};
        }
        const allowed = currentSelectedSurfaces.value.filter((s) =>
            ["mesial", "incisal", "distal"].includes(s)
        );
        toothData.value.restoration.inlay.selectedSurfaces = [...allowed];
        if (restorationComponentRef.value) {
            restorationComponentRef.value.updateSurfaces([...allowed]);
        }
    } else if (currentRestorationType.value === "onlay") {
        // Onlay only supports mesial, distal, buccal, and palatal surfaces
        if (!toothData.value.restoration.onlay) {
            toothData.value.restoration.onlay = {};
        }
        const allowed = currentSelectedSurfaces.value.filter((s) =>
            ["mesial", "distal", "buccal", "palatal"].includes(s)
        );
        toothData.value.restoration.onlay.selectedSurfaces = [...allowed];
        if (restorationComponentRef.value) {
            restorationComponentRef.value.updateSurfaces([...allowed]);
        }
    } else if (currentRestorationType.value === "partial_crown") {
        // Partial crown only supports buccal, palatal, buccal_surface (cusp), palatal_surface (cusp)
        if (!toothData.value.restoration.partialCrown) {
            toothData.value.restoration.partialCrown = {};
        }
        const allowed = currentSelectedSurfaces.value.filter((s) =>
            ["buccal", "palatal", "buccal_surface", "palatal_surface"].includes(
                s
            )
        );
        toothData.value.restoration.partialCrown.selectedSurfaces = [
            ...allowed,
        ];
        if (restorationComponentRef.value) {
            restorationComponentRef.value.updateSurfaces([...allowed]);
        }
    }
};

const updateRestorationData = (restorationData) => {
    updateToothData(toothId.value, "restoration", restorationData);

    // Sync current selected surfaces based on the current restoration type
    if (
        currentRestorationType.value === "filling" &&
        restorationData.filling?.selectedSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...restorationData.filling.selectedSurfaces,
        ];
    } else if (
        currentRestorationType.value === "veneer" &&
        restorationData.veneer?.selectedSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...restorationData.veneer.selectedSurfaces,
        ];
    } else if (
        currentRestorationType.value === "inlay" &&
        restorationData.inlay?.selectedSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...restorationData.inlay.selectedSurfaces,
        ];
    } else if (
        currentRestorationType.value === "onlay" &&
        restorationData.onlay?.selectedSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...restorationData.onlay.selectedSurfaces,
        ];
    } else if (
        currentRestorationType.value === "partial_crown" &&
        restorationData.partialCrown?.selectedSurfaces
    ) {
        currentSelectedSurfaces.value = [
            ...restorationData.partialCrown.selectedSurfaces,
        ];
    }

    // Don't auto-save here - let the RestorationComponent handle saving
};

const onRestorationTypeChanged = (restorationType) => {
    // Store current surfaces in the previous restoration type before switching
    if (
        currentRestorationType.value &&
        currentSelectedSurfaces.value.length > 0
    ) {
        if (currentRestorationType.value === "filling") {
            // Update the filling surfaces in tooth data
            if (!toothData.value.restoration.filling) {
                toothData.value.restoration.filling = {};
            }
            toothData.value.restoration.filling.selectedSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        } else if (currentRestorationType.value === "veneer") {
            // Update the veneer surfaces in tooth data
            if (!toothData.value.restoration.veneer) {
                toothData.value.restoration.veneer = {};
            }
            toothData.value.restoration.veneer.selectedSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        } else if (currentRestorationType.value === "inlay") {
            if (!toothData.value.restoration.inlay) {
                toothData.value.restoration.inlay = {};
            }
            toothData.value.restoration.inlay.selectedSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        } else if (currentRestorationType.value === "onlay") {
            if (!toothData.value.restoration.onlay) {
                toothData.value.restoration.onlay = {};
            }
            toothData.value.restoration.onlay.selectedSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        } else if (currentRestorationType.value === "partial_crown") {
            if (!toothData.value.restoration.partialCrown) {
                toothData.value.restoration.partialCrown = {};
            }
            toothData.value.restoration.partialCrown.selectedSurfaces = [
                ...currentSelectedSurfaces.value,
            ];
        }
    }

    currentRestorationType.value = restorationType;

    // When restoration type changes, load the corresponding surfaces
    if (restorationType === "filling") {
        // Ensure the filling object exists before accessing selectedSurfaces
        const selectedSurfaces =
            toothData.value.restoration.filling &&
            toothData.value.restoration.filling.selectedSurfaces
                ? toothData.value.restoration.filling.selectedSurfaces
                : [];
        currentSelectedSurfaces.value = [...selectedSurfaces];
    } else if (restorationType === "veneer") {
        // Load veneer surfaces but only allow buccal/palatal
        const selectedSurfaces =
            toothData.value.restoration.veneer &&
            toothData.value.restoration.veneer.selectedSurfaces
                ? toothData.value.restoration.veneer.selectedSurfaces
                : [];
        currentSelectedSurfaces.value = [
            ...selectedSurfaces.filter((s) =>
                ["buccal", "palatal"].includes(s)
            ),
        ];
    } else if (restorationType === "inlay") {
        // Load inlay surfaces but only allow mesial, occlusal (incisal), distal
        const selectedSurfaces =
            toothData.value.restoration.inlay &&
            toothData.value.restoration.inlay.selectedSurfaces
                ? toothData.value.restoration.inlay.selectedSurfaces
                : [];
        currentSelectedSurfaces.value = [
            ...selectedSurfaces.filter((s) =>
                ["mesial", "incisal", "distal"].includes(s)
            ),
        ];
    } else if (restorationType === "onlay") {
        // Load onlay surfaces but only allow mesial, distal, buccal, palatal
        const selectedSurfaces =
            toothData.value.restoration.onlay &&
            toothData.value.restoration.onlay.selectedSurfaces
                ? toothData.value.restoration.onlay.selectedSurfaces
                : [];
        currentSelectedSurfaces.value = [
            ...selectedSurfaces.filter((s) =>
                ["mesial", "distal", "buccal", "palatal"].includes(s)
            ),
        ];
    } else if (restorationType === "partial_crown") {
        // Load partial crown surfaces but only allow buccal, palatal, buccal_surface (cusp), palatal_surface (cusp)
        const selectedSurfaces =
            toothData.value.restoration.partialCrown &&
            toothData.value.restoration.partialCrown.selectedSurfaces
                ? toothData.value.restoration.partialCrown.selectedSurfaces
                : [];
        currentSelectedSurfaces.value = [
            ...selectedSurfaces.filter((s) =>
                [
                    "buccal",
                    "palatal",
                    "buccal_surface",
                    "palatal_surface",
                ].includes(s)
            ),
        ];
    } else {
        currentSelectedSurfaces.value = [];
    }
};

const onRestorationSaved = async (restorationData) => {
    try {
        updateToothData(toothId.value, "restoration", restorationData);
        await saveDentalChartSection(
            patientId.value,
            toothId.value,
            "restoration",
            restorationData
        );
        message.success(t("dental_chart.restoration_saved_successfully"));
    } catch (error) {
        console.error("Error saving restoration data:", error);
        message.error(t("dental_chart.save_error"));
    }
};

// Track current periodontal type
const currentPeriodontalType = ref(null);

// Handle periodontal type change
const onPeriodontalTypeChanged = (periodontalType) => {
    currentPeriodontalType.value = periodontalType;
};

// Handle periodontal data save
const onPeriodontalSaved = async (periodontalData) => {
    try {
        updateToothData(toothId.value, "periodontal", periodontalData);
        // Only saves periodontal data if it differs from defaults (bleeding=true, measurements≠defaults)
        await saveDentalChartSection(
            patientId.value,
            toothId.value,
            "periodontal",
            periodontalData
        );
        message.success(t("dental_chart.periodontal_saved_successfully"));
    } catch (error) {
        console.error("Error saving periodontal data:", error);
        message.error(t("dental_chart.save_error"));
    }
};

// Function to check if probing depth indicates disease (>= 5)
const isProbingDepthDiseased = (position) => {
    if (!position || !toothData.value.periodontal?.[position]) return false;
    const depth = toothData.value.periodontal[position].probingDepth;
    return typeof depth === "number" && depth >= 5;
};

onMounted(async () => {
    await loadToothData();

    // Aggressive parallel preloading strategy for maximum speed
    const loadingPromises = [
        // 1. Load all variants for current tooth (parallel)
        preloadVisibleImages("all", [toothId.value]),
        // 2. Warm cache with high-priority teeth
        warmImageCache(),
    ];

    // Wait for critical preloading to complete
    await Promise.allSettled(loadingPromises);

    // 3. Preload adjacent teeth variants for quick navigation (parallel in background)
    const adjacentTeeth = getAdjacentTeeth(toothId.value);
    if (adjacentTeeth.length > 0) {
        preloadVisibleImages("all", adjacentTeeth);
    }

    // 4. Finally preload all remaining images in the background
    setTimeout(() => {
        preloadAllToothImages();
    }, 100);
});

// Helper function to get adjacent teeth for preloading priority
const getAdjacentTeeth = (currentToothId) => {
    const adjacent = [];
    const id = Number(currentToothId);

    // Get adjacent teeth in the same quadrant
    if (id > 11 && id <= 18) {
        // Upper right quadrant
        if (id > 11) adjacent.push(id - 1);
        if (id < 18) adjacent.push(id + 1);
    } else if (id >= 21 && id < 28) {
        // Upper left quadrant
        if (id > 21) adjacent.push(id - 1);
        if (id < 28) adjacent.push(id + 1);
    } else if (id > 31 && id <= 38) {
        // Lower right quadrant
        if (id > 31) adjacent.push(id - 1);
        if (id < 38) adjacent.push(id + 1);
    } else if (id >= 41 && id < 48) {
        // Lower left quadrant
        if (id > 41) adjacent.push(id - 1);
        if (id < 48) adjacent.push(id + 1);
    }

    return adjacent;
};

// Smart cache warming - preload the most likely to be used images first
const warmImageCache = async () => {
    // Start loading critical images immediately (all teeth base views)
    preloadVisibleImages("all");

    // Get adjacent teeth for priority loading
    const adjacentTeeth = getAdjacentTeeth(toothId.value);

    // Preload adjacent teeth instantly for smooth navigation
    const promises = adjacentTeeth.map((adjacentToothId) =>
        preloadToothInstant(adjacentToothId)
    );

    // Also preload current tooth if not already loaded
    promises.push(preloadToothInstant(toothId.value));

    // Load all in parallel for instant access
    await Promise.allSettled(promises);
};

watch(
    () => route.params.toothId,
    async (newToothId) => {
        if (newToothId) {
            // Reset current state for new tooth
            currentPathologyType.value = null;
            currentRestorationType.value = null;
            currentSelectedSurfaces.value = [];

            // Reset temporary surface storage for new tooth
            tempSurfaceStorage.value = {
                decay: [],
                tooth_wear: [],
            };

            toothData.value.id = Number(newToothId);
            await loadToothData();

            // Preload variants for the new tooth in background (don't await to avoid navigation delay)
            preloadToothVariantsParallel(Number(newToothId)).catch((error) => {
                console.warn("Background image preloading failed:", error);
            });

            // Also preload adjacent teeth for quick navigation (background)
            const adjacentTeeth = getAdjacentTeeth(Number(newToothId));
            if (adjacentTeeth.length > 0) {
                preloadMultipleToothVariants(adjacentTeeth);
            }
        }
    }
);
</script>

<style scoped>
/* Only minimal non-Tailwind styles needed */
.perio-row {
    display: grid;
    grid-template-columns: 1fr 60px 60px 60px 1fr;
}

.upside-down-container {
    transform: rotate(180deg);
}

/* Dental Card Base Styles */
.dental-card {
    background-color: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 120px;
}

.dental-card-default {
    border-color: rgb(229, 231, 235);
}

.dental-card-default:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dental-card-selected {
    border-color: rgb(59, 130, 246);
    background-color: rgb(59, 130, 246);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.dental-card-selected .dental-card-icon,
.dental-card-selected .dental-card-text {
    color: white;
}

.dental-card-icon {
    margin-bottom: 0.75rem;
    color: rgb(96, 165, 250);
}

.dental-card-text {
    color: rgb(55, 65, 81);
    font-weight: 500;
    text-align: center;
}

/* Fade effects for tooth selector and visualization containers */
.tooth-selector-container {
    position: relative;
}

.tooth-selector-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 30px;
    background: linear-gradient(
        to bottom,
        rgba(245, 245, 245, 1) 0%,
        rgba(245, 245, 245, 0) 100%
    );
    z-index: 10;
    pointer-events: none;
}

.tooth-selector-container::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 30px;
    background: linear-gradient(
        to top,
        rgba(245, 245, 245, 1) 0%,
        rgba(245, 245, 245, 0) 100%
    );
    z-index: 10;
    pointer-events: none;
}

.tooth-visualization-container {
    position: relative;
}

.tooth-visualization-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(
        to bottom,
        rgba(245, 245, 245, 1) 0%,
        rgba(245, 245, 245, 0) 100%
    );
    z-index: 10;
    pointer-events: none;
}

.tooth-visualization-container::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(
        to top,
        rgba(245, 245, 245, 1) 0%,
        rgba(245, 245, 245, 0) 100%
    );
    z-index: 10;
    pointer-events: none;
}

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
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5),
        0 0 0 5px rgba(239, 68, 68, 0.3);
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
</style>
