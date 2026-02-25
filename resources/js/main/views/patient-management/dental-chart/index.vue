<template>
    <a-spin :spinning="loading">
        <div
            class="bg-white p-5 rounded-lg shadow max-w-[1200px] mx-auto relative"
        >
            <!-- Mode Selector Buttons - Top Left -->
            <div class="absolute top-5 left-5 flex gap-2 z-10">
                <a-button
                    :type="selectionMode === 'overview' ? 'primary' : 'default'"
                    @click="setSelectionMode('overview')"
                    size="small"
                >
                    {{ $t("dental_chart.overview") }}
                </a-button>
                <a-button
                    :type="
                        selectionMode === 'quick_select' ? 'primary' : 'default'
                    "
                    @click="setSelectionMode('quick_select')"
                    size="small"
                >
                    {{ $t("dental_chart.quick_select") }}
                </a-button>

                <!-- Quick Select Actions -->
                <template
                    v-if="
                        selectionMode === 'quick_select' &&
                        selectedTeeth.length > 0
                    "
                >
                    <a-button
                        type="primary"
                        danger
                        @click="showMarkMissingConfirm"
                        size="small"
                    >
                        {{ $t("dental_chart.mark_missing") }} ({{
                            selectedTeeth.length
                        }})
                    </a-button>
                    <a-button @click="clearSelection" size="small">
                        {{ $t("dental_chart.clear_selection") }}
                    </a-button>
                </template>
            </div>

            <!-- Dentition Type Tabs - Top Center -->
            <!-- <div class="absolute top-5 left-1/2 transform -translate-x-1/2 z-10">
                <a-radio-group
                    v-model:value="dentitionType"
                    button-style="solid"
                    size="small"
                >
                    <a-radio-button value="adult">
                        {{ $t('dental_chart.adult_teeth') }}
                    </a-radio-button>
                    <a-radio-button value="kid">
                        {{ $t('dental_chart.kid_teeth') }}
                    </a-radio-button>
                </a-radio-group>
            </div> -->

            <!-- View Selector Buttons -->
            <div class="absolute top-5 right-5 flex flex-col gap-2 z-10">
                <a-button
                    :type="teethView === 'upper' ? 'primary' : 'default'"
                    @click="teethView = 'upper'"
                    size="small"
                >
                    {{ $t("dental_chart.upper_teeth") }}
                </a-button>
                <a-button
                    :type="teethView === 'all' ? 'primary' : 'default'"
                    @click="teethView = 'all'"
                    size="small"
                >
                    {{ $t("dental_chart.all_teeth") }}
                </a-button>
                <a-button
                    :type="teethView === 'lower' ? 'primary' : 'default'"
                    @click="teethView = 'lower'"
                    size="small"
                >
                    {{ $t("dental_chart.lower_teeth") }}
                </a-button>
            </div>

            <!-- Upper Teeth -->
            <div v-if="teethView === 'upper' || teethView === 'all'">
                <!-- front -->
                <div class="flex flex-col mb-5 items-center relative">
                    <div
                        class="flex justify-center flex-nowrap gap-0.5 relative w-full max-w-[960px] items-end"
                    >
                        <tooth-component
                            v-for="tooth in upperTeeth"
                            :key="`front-${tooth.id}`"
                            :tooth="tooth"
                            :current-condition="currentCondition"
                            :current-surface="currentSurface"
                            :size="60"
                            type="upper"
                            view="front"
                            :invert-lower-views="false"
                            @tooth-clicked="handleToothClick"
                        ></tooth-component>
                    </div>
                </div>

                <!-- top view -->
                <div class="flex flex-col mb-5 items-center relative">
                    <div
                        class="flex justify-center flex-nowrap gap-0.5 relative w-full max-w-[960px] items-start"
                    >
                        <tooth-component
                            v-for="tooth in upperTeeth"
                            :key="`top-${tooth.id}`"
                            :tooth="tooth"
                            :current-condition="currentCondition"
                            :current-surface="currentSurface"
                            :size="60"
                            type="upper"
                            view="top"
                            :invert-lower-views="false"
                            @tooth-clicked="handleToothClick"
                        ></tooth-component>
                    </div>
                </div>

                <!-- upside-down view - only show when viewing upper teeth only -->
                <div
                    v-if="teethView === 'upper'"
                    class="flex flex-col mb-5 items-center relative"
                >
                    <div
                        class="flex justify-center flex-nowrap gap-0.5 relative w-full max-w-[960px] items-start"
                    >
                        <tooth-component
                            v-for="tooth in upperTeeth"
                            :key="`upside-down-${tooth.id}`"
                            :tooth="tooth"
                            :current-condition="currentCondition"
                            :current-surface="currentSurface"
                            :size="60"
                            type="upper"
                            view="upside-down"
                            :invert-lower-views="false"
                            @tooth-clicked="handleToothClick"
                        ></tooth-component>
                    </div>
                </div>

                <!-- Tooth numbers -->
                <div
                    class="flex justify-center my-2 w-full max-w-[960px] mx-auto"
                >
                    <span
                        v-for="tooth in upperTeeth"
                        :key="tooth.id"
                        :class="['text-center font-medium text-xs w-[60px]']"
                    >
                        {{ tooth.id }}
                    </span>
                </div>
            </div>

            <!-- Lower Teeth -->
            <div v-if="teethView === 'lower' || teethView === 'all'">
                <!-- upside-down view - only show when viewing lower teeth only -->
                <div
                    v-if="teethView === 'lower'"
                    class="flex flex-col mb-5 items-center relative"
                >
                    <div
                        class="flex justify-center flex-nowrap gap-0.5 relative w-full max-w-[960px] mt-2 items-end"
                    >
                        <tooth-component
                            v-for="tooth in lowerTeeth"
                            :key="`upside-down-${tooth.id}`"
                            :tooth="tooth"
                            :current-condition="currentCondition"
                            :current-surface="currentSurface"
                            :size="60"
                            type="lower"
                            view="upside-down"
                            :invert-lower-views="false"
                            @tooth-clicked="handleToothClick"
                        ></tooth-component>
                    </div>
                </div>

                <!-- top view -->
                <div class="flex flex-col mb-5 items-center relative">
                    <div
                        class="flex justify-center flex-nowrap gap-0.5 relative w-full max-w-[960px] mt-2 items-start"
                    >
                        <tooth-component
                            v-for="tooth in lowerTeeth"
                            :key="`top-${tooth.id}`"
                            :tooth="tooth"
                            :current-condition="currentCondition"
                            :current-surface="currentSurface"
                            :size="60"
                            type="lower"
                            view="top"
                            :invert-lower-views="false"
                            @tooth-clicked="handleToothClick"
                        ></tooth-component>
                    </div>
                </div>

                <!-- Front View -->
                <div class="flex flex-col mb-5 items-center relative">
                    <div
                        class="flex justify-center flex-nowrap gap-0.5 relative w-full max-w-[960px] items-start"
                    >
                        <tooth-component
                            v-for="tooth in lowerTeeth"
                            :key="`front-${tooth.id}`"
                            :tooth="tooth"
                            :current-condition="currentCondition"
                            :current-surface="currentSurface"
                            :size="60"
                            type="lower"
                            view="front"
                            :invert-lower-views="false"
                            @tooth-clicked="handleToothClick"
                        ></tooth-component>
                    </div>
                </div>
                <!-- Tooth numbers at bottom for lower teeth -->
                <div
                    class="flex justify-center my-2 w-full max-w-[960px] mx-auto"
                >
                    <span
                        v-for="tooth in lowerTeeth"
                        :key="`label-${tooth.id}`"
                        :class="['text-center font-medium text-xs w-[60px]']"
                    >
                        {{ tooth.id }}
                    </span>
                </div>
            </div>
        </div>
    </a-spin>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { message, Modal } from "ant-design-vue";
import ToothComponent from "./components/ToothComponent.vue";
import useToothImageCache from "./composables/useToothImageCache.js";
import useDentalChart from "./composables/useDentalChart.js";

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const patientId = computed(() => route.params.id);
const loading = ref(false);

// Selection mode: 'overview' or 'quick_select'
const selectionMode = ref("overview");
const selectedTeeth = ref([]);

// Teeth view state: 'upper', 'lower', or 'all'
const teethView = ref("all");

// Dentition type: 'adult' or 'kid'
const dentitionType = ref("adult");

// Initialize image cache
const { preloadVisibleImages, preloadingStatus } = useToothImageCache();

// Use dental chart composable
const {
    loadDentalChart,
    saveDentalChartSection,
    getToothData,
    updateToothData,
} = useDentalChart();

// Create computed properties for teeth using dental chart data
const upperTeeth = computed(() => {
    const adultUpperTeethIds = [
        18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28,
    ];
    const kidUpperTeethIds = [16, 55, 54, 53, 52, 51, 61, 62, 63, 64, 65, 26];

    const teethIds =
        dentitionType.value === "adult" ? adultUpperTeethIds : kidUpperTeethIds;

    return teethIds.map((id) => {
        const toothData = getToothData(id);
        return {
            id,
            ...toothData,
            selected:
                selectionMode.value === "quick_select" &&
                selectedTeeth.value.includes(id),
        };
    });
});

const lowerTeeth = computed(() => {
    const adultLowerTeethIds = [
        48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38,
    ];
    const kidLowerTeethIds = [46, 85, 84, 83, 82, 81, 71, 72, 73, 74, 75, 36];

    const teethIds =
        dentitionType.value === "adult" ? adultLowerTeethIds : kidLowerTeethIds;

    return teethIds.map((id) => {
        const toothData = getToothData(id);
        return {
            id,
            ...toothData,
            selected:
                selectionMode.value === "quick_select" &&
                selectedTeeth.value.includes(id),
        };
    });
});

const currentCondition = ref(""); // 'missing', 'pus', 'inflammation'
const currentSurface = ref(""); // 'buccal', 'lingual', 'mesial', 'distal'
const selectedTooth = ref(null);

// Methods
const setSelectionMode = (mode) => {
    selectionMode.value = mode;
    if (mode === "overview") {
        selectedTeeth.value = [];
    }
};

const clearSelection = () => {
    selectedTeeth.value = [];
};

const showMarkMissingConfirm = () => {
    const count = selectedTeeth.value.length;
    if (count === 0) return;

    Modal.confirm({
        title: t("common.confirm"),
        content: t("dental_chart.mark_multiple_teeth_missing_confirm", {
            count: count,
        }),
        okText: t("common.yes"),
        cancelText: t("common.no"),
        onOk: () => {
            markSelectedAsMissing();
        },
    });
};

const markSelectedAsMissing = async () => {
    if (selectedTeeth.value.length === 0) return;

    loading.value = true;
    try {
        const promises = selectedTeeth.value.map(async (toothId) => {
            const currentData = getToothData(toothId);
            const newConditions = {
                ...currentData.conditions,
                missing: true,
            };

            // Update UI immediately
            updateToothData(toothId, "conditions", newConditions);

            // Save to backend
            return saveDentalChartSection(
                patientId.value,
                toothId,
                "conditions",
                newConditions
            );
        });

        await Promise.all(promises);

        message.success(
            t("dental_chart.teeth_marked_missing_successfully", {
                count: selectedTeeth.value.length,
            })
        );

        // Clear selection after successful save
        selectedTeeth.value = [];
    } catch (error) {
        console.error("Error marking teeth as missing:", error);
        message.error(t("dental_chart.save_error"));
    } finally {
        loading.value = false;
    }
};

const setCurrentCondition = (condition) => {
    currentCondition.value =
        condition === currentCondition.value ? "" : condition;
    // Reset surface when switching condition
    if (condition !== "pus" && condition !== "inflammation") {
        currentSurface.value = "";
    } else if (currentSurface.value === "") {
        currentSurface.value = "buccal"; // Default surface
    }
};

const handleToothClick = (tooth) => {
    // Quick select mode - toggle tooth selection
    if (selectionMode.value === "quick_select") {
        const index = selectedTeeth.value.indexOf(tooth.id);
        if (index > -1) {
            selectedTeeth.value.splice(index, 1);
        } else {
            selectedTeeth.value.push(tooth.id);
        }
        return;
    }

    // Overview mode - original behavior
    // If we have a condition selected, apply it
    if (currentCondition.value) {
        if (currentCondition.value === "missing") {
            updateToothCondition(
                tooth.id,
                "missing",
                !tooth.conditions?.missing
            );
        } else if (
            ["pus", "inflammation"].includes(currentCondition.value) &&
            currentSurface.value
        ) {
            const currentValue =
                tooth.conditions?.[currentCondition.value]?.[
                    currentSurface.value
                ] || false;
            updateToothSurface(
                tooth.id,
                currentCondition.value,
                currentSurface.value,
                !currentValue
            );
        }
    } else {
        // Navigate to the tooth detail view
        router.push({
            name: "admin.patients.tooth_detail",
            params: { id: patientId.value, toothId: tooth.id },
        });
    }
};

const updateToothCondition = async (toothId, condition, value) => {
    try {
        // Update local data immediately for UI feedback
        const conditionsData = { [condition]: value };
        updateToothData(toothId, "conditions", conditionsData);

        // Save to backend
        await saveDentalChartSection(patientId.value, toothId, "conditions", {
            [condition]: value,
        });
        message.success(t("dental_chart.condition_saved_successfully"));
    } catch (error) {
        console.error("Error saving tooth condition:", error);
        message.error(t("dental_chart.save_error"));
    }
};

const updateToothSurface = async (toothId, condition, surface, value) => {
    try {
        // Update local data immediately for UI feedback
        const conditionsData = {
            [condition]: {
                [surface]: value,
            },
        };
        updateToothData(toothId, "conditions", conditionsData);

        // Save to backend
        await saveDentalChartSection(
            patientId.value,
            toothId,
            "conditions",
            conditionsData
        );
        message.success(t("dental_chart.condition_saved_successfully"));
    } catch (error) {
        console.error("Error saving tooth surface condition:", error);
        message.error(t("dental_chart.save_error"));
    }
};

// Load patient's dental chart data
const loadPatientDentalChart = async () => {
    loading.value = true;
    try {
        await loadDentalChart(patientId.value);
    } catch (error) {
        console.error("Error loading patient dental chart:", error);
        message.error(t("dental_chart.load_error"));
    } finally {
        loading.value = false;
    }
};

// Watch for view changes to prioritize loading visible teeth
watch(teethView, (newView) => {
    preloadVisibleImages(newView);
});

// Initialize
onMounted(async () => {
    await loadPatientDentalChart();
    // Start preloading images for the current view
    preloadVisibleImages(teethView.value);
});
</script>
