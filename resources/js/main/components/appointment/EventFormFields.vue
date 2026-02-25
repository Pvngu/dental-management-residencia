<template>
    <a-form layout="vertical" class="mt-4">
        <!-- Date / Time Range -->
        <a-row>
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
        <a-form-item :label="$t('common.title', 'Title')" required>
            <a-row :gutter="16">
                <a-col :span="5">
                    <ColorInput
                        :value="formData.color"
                        @colorChanged="(val) => updateForm('color', val)"
                        :showCustomColor="false"
                    />
                </a-col>
                <a-col :span="19">
                    <a-input
                        :value="formData.title"
                        @input="(e) => updateForm('title', e.target.value)"
                        :placeholder="$t('common.title', 'Title')"
                    />
                </a-col>
            </a-row>
        </a-form-item>

        <a-row :gutter="16">
            <a-col :span="12">
                <a-form-item
                    :label="$t('appointments.duration', 'Duration')"
                    required
                >
                    <a-input-number
                        :value="formData.duration_display"
                        @change="(val) => updateForm('duration_display', val)"
                        :min="15"
                        :step="15"
                        class="w-full"
                    />
                </a-form-item>
            </a-col>
            <a-col :span="12">
                <a-form-item label="Unit" required>
                    <a-select
                        :value="formData.duration_unit"
                        @change="(val) => updateForm('duration_unit', val)"
                        class="w-full"
                        :getPopupContainer="
                            (triggerNode) => triggerNode.parentNode
                        "
                    >
                        <a-select-option value="minutes">{{
                            $t("common.minutes", "Minutes")
                        }}</a-select-option>
                        <a-select-option value="hours">{{
                            $t("common.hours", "Hours")
                        }}</a-select-option>
                    </a-select>
                </a-form-item>
            </a-col>
        </a-row>

        <a-row :gutter="16">
            <a-col v-if="!autoSelectDoctor" :xs="24" :sm="24" :md="12" :lg="12">
                <a-form-item :label="$t('common.doctor', 'Doctor')" required>
                    <UserSelect
                        @onChange="(id) => updateForm('doctor_id', id)"
                        :value="formData.doctor_id"
                        userType="doctor"
                    />
                </a-form-item>
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="autoSelectDoctor ? 24 : 12"
                :lg="autoSelectDoctor ? 24 : 12"
            >
                <a-form-item :label="$t('common.patient')">
                    <UserSelect
                        @onChange="(id) => updateForm('patient_id', id)"
                        :value="formData.patient_id"
                        userType="patient"
                        showPhone
                    />
                </a-form-item>
            </a-col>
        </a-row>

        <a-form-item :label="$t('common.description', 'Description')">
            <a-textarea
                :value="formData.description"
                @input="(e) => updateForm('description', e.target.value)"
                :rows="3"
            />
        </a-form-item>
    </a-form>
</template>

<script setup>
import { computed } from "vue";
import dayjs from "dayjs";
import ColorInput from "../../../common/components/common/input/ColorInput.vue";
import UserSelect from "../../../common/components/common/select/UserSelect.vue";
import DateTimeRangePicker from "./DateTimeRangePicker.vue";

const props = defineProps({
    formData: {
        type: Object,
        required: true,
    },
    autoSelectDoctor: {
        type: Boolean,
        default: false,
    },
    doctorInfo: {
        type: Object,
        default: () => ({}),
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
</script>
