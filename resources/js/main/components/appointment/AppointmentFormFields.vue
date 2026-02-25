<template>
    <a-form layout="vertical">
        <a-row v-if="showDateTimeRangePicker">
            <a-col :span="24">
                <DateTimeRangePicker
                    :date="formData.selectedDate_full"
                    :startTime="formData.selectedTimeSlot"
                    :endTime="formData.endTime"
                    :endDate="computedEndDate"
                    @update:date="(val) => handleDateUpdate(val)"
                    @update:startTime="(val) => handleStartTimeUpdate(val)"
                    @update:endTime="(val) => handleEndTimeUpdate(val)"
                />
            </a-col>
        </a-row>
        <a-row>
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item
                    :label="$t('common.treatment_type') + ' *'"
                    required
                    :help="
                        rules.treatment_type_id
                            ? rules.treatment_type_id.message
                            : null
                    "
                    :validateStatus="rules.treatment_type_id ? 'error' : null"
                    class="required"
                >
                    <a-select
                        :value="formData.treatment_type_id"
                        @change="onTreatmentTypeChange"
                        :placeholder="
                            $t('common.select_default_text', [
                                $t('common.treatment_type'),
                            ])
                        "
                        style="width: 100%"
                        :loading="isComponentLoading"
                        :dropdown-match-select-width="false"
                        :getPopupContainer="
                            (triggerNode) => triggerNode.parentNode
                        "
                    >
                        <a-select-option
                            v-for="type in resolvedTreatmentTypesData"
                            :key="type.xid"
                            :value="type.xid"
                        >
                            {{ type.name }}
                            <span
                                class="bg-gray-200 px-2 py-1 rounded text-xs ml-1"
                                >{{ type.duration_minutes }}min</span
                            >
                        </a-select-option>
                    </a-select>

                    <div
                        v-if="formData.treatment_type_id"
                        class="mt-2 flex items-center gap-2 sm:gap-4 text-gray-600 rounded-lg p-2"
                    >
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500"
                                >{{ $t("common.cost", "Cost") }}:</span
                            >
                            <CurrencyInput
                                :value="formData.price"
                                :min="0"
                                @inputNumberChanged="
                                    (val) => updateForm('price', val)
                                "
                            />
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500"
                                >{{ $t("common.duration", "Duration") }}:</span
                            >
                            <div class="flex items-center gap-2">
                                <a-input-number
                                    :value="formData.duration_display"
                                    @change="
                                        (val) =>
                                            updateForm('duration_display', val)
                                    "
                                    :min="1"
                                    :step="5"
                                />
                                <a-select
                                    :value="formData.duration_unit"
                                    @change="
                                        (val) =>
                                            updateForm('duration_unit', val)
                                    "
                                    style="width: 72px"
                                    :getPopupContainer="
                                        (triggerNode) => triggerNode.parentNode
                                    "
                                >
                                    <a-select-option value="mins">{{
                                        $t("common.mins", "mins")
                                    }}</a-select-option>
                                    <a-select-option value="hours">{{
                                        $t("common.hours", "hrs")
                                    }}</a-select-option>
                                </a-select>
                            </div>
                        </div>
                    </div>
                </a-form-item>
            </a-col>
        </a-row>
        <!-- Doctor Information Display (when pre-selected) -->
        <a-row
            v-if="autoSelectDoctor && doctorInfo && doctorInfo.name"
            :gutter="16"
            class="mb-4"
        >
            <a-col :span="24">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <a-avatar
                            :size="48"
                            :src="
                                doctorInfo.profile_image_url ||
                                doctorInfo.user?.profile_image_url
                            "
                            class="flex-shrink-0"
                        >
                            {{
                                (
                                    doctorInfo.name ||
                                    doctorInfo.user?.name ||
                                    "D"
                                ).charAt(0)
                            }}
                        </a-avatar>
                        <div class="flex-1">
                            <div class="text-base font-semibold text-gray-800">
                                {{ doctorInfo.name || doctorInfo.user?.name }}
                            </div>
                            <div
                                v-if="doctorInfo.specialist"
                                class="text-sm text-gray-600"
                            >
                                {{ doctorInfo.specialist }}
                            </div>
                            <div
                                v-if="doctorInfo.department"
                                class="text-xs text-gray-500 mt-1"
                            >
                                {{ doctorInfo.department }}
                            </div>
                        </div>
                    </div>
                </div>
            </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col
                v-if="!autoSelectDoctor"
                :xs="24"
                :sm="24"
                :md="autoSelectPatient ? 24 : 12"
                :lg="autoSelectPatient ? 24 : 12"
            >
                <a-form-item
                    :label="$t('common.doctor')"
                    required
                    :help="rules.doctor_id ? rules.doctor_id.message : null"
                    :validateStatus="rules.doctor_id ? 'error' : null"
                    class="required"
                >
                    <UserSelect
                        @onChange="(id) => updateForm('doctor_id', id)"
                        :value="formData.doctor_id"
                        userType="doctor"
                    />
                </a-form-item>
            </a-col>
            <a-col
                v-if="!autoSelectPatient"
                :xs="24"
                :sm="24"
                :md="autoSelectDoctor ? 24 : 12"
                :lg="autoSelectDoctor ? 24 : 12"
            >
                <a-form-item
                    :label="$t('common.patient')"
                    required
                    :help="rules.patient_id ? rules.patient_id.message : null"
                    :validateStatus="rules.patient_id ? 'error' : null"
                    class="required"
                >
                    <UserSelect
                        @onChange="(id) => updateForm('patient_id', id)"
                        :value="formData.patient_id"
                        userType="patient"
                        showPhone
                    />
                </a-form-item>
            </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                <a-form-item
                    :label="$t('common.room')"
                    :help="rules.room_id ? rules.room_id.message : null"
                    :validateStatus="rules.room_id ? 'error' : null"
                >
                    <a-select
                        :value="formData.room_id"
                        @change="(val) => updateForm('room_id', val)"
                        :placeholder="
                            $t('common.select_default_text', [
                                $t('common.room'),
                            ])
                        "
                        :allowClear="true"
                        style="width: 100%"
                        optionFilterProp="title"
                        show-search
                        :loading="isComponentLoading"
                        :getPopupContainer="
                            (triggerNode) => triggerNode.parentNode
                        "
                    >
                        <a-select-option
                            v-for="room in resolvedRoomsData"
                            :key="room.xid"
                            :title="room.name"
                            :value="room.xid"
                        >
                            {{ room.name }}
                        </a-select-option>
                    </a-select>
                </a-form-item>
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24">
                <a-form-item
                    :label="$t('appointments.reason')"
                    :help="
                        rules.reason_visit ? rules.reason_visit.message : null
                    "
                    :validateStatus="rules.reason_visit ? 'error' : null"
                >
                    <a-textarea
                        :value="formData.reason_visit"
                        @input="
                            (e) => updateForm('reason_visit', e.target.value)
                        "
                        :placeholder="
                            $t('common.placeholder_default_text', [
                                $t('appointments.reason'),
                            ])
                        "
                        auto-size
                    />
                </a-form-item>
            </a-col>
        </a-row>
    </a-form>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import dayjs from "dayjs";
import UserSelect from "../../../common/components/common/select/UserSelect.vue";
import CurrencyInput from "../../../common/components/common/input/CurrencyInput.vue";
import DateTimeRangePicker from "./DateTimeRangePicker.vue";

const props = defineProps({
    formData: {
        type: Object,
        required: true,
    },
    rules: {
        type: Object,
        default: () => ({}),
    },
    autoSelectDoctor: {
        type: Boolean,
        default: false,
    },
    autoSelectPatient: {
        type: Boolean,
        default: false,
    },
    doctorInfo: {
        type: Object,
        default: () => ({}),
    },
    roomsData: {
        type: Array,
        default: () => [],
    },
    treatmentTypesData: {
        type: Array,
        default: () => [],
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    showDateTimeRangePicker: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:formData"]);

const computedEndDate = computed(() => {
    if (!props.formData.selectedDate_full || !props.formData.selectedTimeSlot)
        return null;
    const durationMins =
        props.formData.duration_unit === "hours"
            ? parseInt(props.formData.duration_display || 1) * 60
            : parseInt(props.formData.duration_display || 30);
    const start = dayjs(
        `${props.formData.selectedDate_full} ${props.formData.selectedTimeSlot}`,
    );
    return start.add(durationMins, "minute").format("YYYY-MM-DD");
});

const localRoomsData = ref([]);
const localTreatmentTypesData = ref([]);
const isLoadingLocal = ref(false);

const resolvedRoomsData = computed(() => {
    return props.roomsData.length > 0 ? props.roomsData : localRoomsData.value;
});

const resolvedTreatmentTypesData = computed(() => {
    return props.treatmentTypesData.length > 0
        ? props.treatmentTypesData
        : localTreatmentTypesData.value;
});

const isComponentLoading = computed(
    () => props.isLoading || isLoadingLocal.value,
);

const fetchDataIfNeeded = () => {
    if (props.roomsData.length > 0 && props.treatmentTypesData.length > 0) {
        return Promise.resolve();
    }

    isLoadingLocal.value = true;
    const promises = [];

    if (props.roomsData.length === 0) {
        promises.push(
            window.axiosAdmin
                .get("rooms?fields=id,xid,name")
                .then((response) => {
                    localRoomsData.value = response.data;
                }),
        );
    }

    if (props.treatmentTypesData.length === 0) {
        promises.push(
            window.axiosAdmin
                .get(
                    "treatment_types?fields=id,xid,name,duration_minutes,price",
                )
                .then((response) => {
                    localTreatmentTypesData.value = response.data;
                }),
        );
    }

    return Promise.all(promises)
        .catch((error) => {
            console.error("Error fetching rooms and treatment types:", error);
        })
        .finally(() => {
            isLoadingLocal.value = false;
        });
};

onMounted(() => {
    fetchDataIfNeeded();
});

const updateForm = (key, value) => {
    const updates = { [key]: value };

    if (key === "duration_display" || key === "duration_unit") {
        const tempForm = { ...props.formData, ...updates };
        const durationMins =
            tempForm.duration_unit === "hours"
                ? parseInt(tempForm.duration_display || 1) * 60
                : parseInt(tempForm.duration_display || 30);

        if (tempForm.selectedTimeSlot) {
            const start = dayjs(`2000-01-01 ${tempForm.selectedTimeSlot}`);
            updates.endTime = start.add(durationMins, "minute").format("HH:mm");
        }
    }

    emit("update:formData", { ...props.formData, ...updates });
};

const handleDateUpdate = (dateString) => {
    const d = dayjs(dateString);
    emit("update:formData", {
        ...props.formData,
        selectedDate_full: dateString,
        selectedDate: d.date(),
        currentMonth: d.month(),
        currentYear: d.year(),
    });
};

const handleStartTimeUpdate = (timeString) => {
    const updates = { selectedTimeSlot: timeString };

    // Use existing duration to calculate new end time
    const durationMins =
        props.formData.duration_unit === "hours"
            ? parseInt(props.formData.duration_display || 1) * 60
            : parseInt(props.formData.duration_display || 30);

    const start = dayjs(`2000-01-01 ${timeString}`);
    updates.endTime = start.add(durationMins, "minute").format("HH:mm");

    emit("update:formData", { ...props.formData, ...updates });
};

const handleEndTimeUpdate = (timeString) => {
    const updates = { endTime: timeString };

    // Use existing duration to calculate new start time
    const durationMins =
        props.formData.duration_unit === "hours"
            ? parseInt(props.formData.duration_display || 1) * 60
            : parseInt(props.formData.duration_display || 30);

    const end = dayjs(`2000-01-01 ${timeString}`);
    updates.selectedTimeSlot = end
        .subtract(durationMins, "minute")
        .format("HH:mm");

    emit("update:formData", { ...props.formData, ...updates });
};

const onTreatmentTypeChange = (value) => {
    if (!value) return;

    const updates = { treatment_type_id: value };

    // Find the selected treatment type
    const selectedType = resolvedTreatmentTypesData.value.find(
        (type) => type.xid === value,
    );
    if (selectedType) {
        let mins = selectedType.duration_minutes || 30;
        if (mins >= 60 && mins % 60 === 0) {
            updates.duration_display = mins / 60;
            updates.duration_unit = "hours";
        } else {
            updates.duration_display = mins;
            updates.duration_unit = "mins";
        }
        updates.price = selectedType.price || 0;

        if (props.formData.selectedTimeSlot) {
            const start = dayjs(
                `2000-01-01 ${props.formData.selectedTimeSlot}`,
            );
            updates.endTime = start.add(mins, "minute").format("HH:mm");
        }
    }

    emit("update:formData", { ...props.formData, ...updates });
};
</script>
