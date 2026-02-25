<template>
    <div
        :class="[
            'p-0 text-gray-800 bg-white rounded-lg relative transition-all duration-300',
            showEndDate ? 'w-[520px]' : 'w-[450px]',
        ]"
    >
        <!-- Header -->
        <div
            class="p-4 pl-6 pr-12 border-b flex flex-col justify-center relative min-h-[64px]"
        >
            <h2 class="text-[16px] font-medium m-0 text-gray-800 leading-tight">
                {{
                    activeTab === "event"
                        ? $t("calendar.new_event", "New Event")
                        : $t("appointments.new_appointment", "New Appointment")
                }}
            </h2>

            <button
                @click="onClose"
                class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors border-0 bg-transparent cursor-pointer"
            >
                <CloseOutlined class="text-[14px]" />
            </button>
        </div>

        <div class="p-4 sm:p-6 pb-4">
            <!-- Date/Time Display Removed - Handled inside forms -->

            <!-- Form Fields -->
            <div class="pr-2">
                <a-tabs v-model:activeKey="activeTab" class="w-full">
                    <a-tab-pane
                        key="appointment"
                        :tab="$t('appointments.appointment', 'Appointment')"
                    >
                        <AppointmentFormFields
                            :formData="formData"
                            @update:formData="
                                (newData) => Object.assign(formData, newData)
                            "
                            :rules="rules"
                            :autoSelectDoctor="false"
                            :autoSelectPatient="false"
                            :doctorInfo="doctorInfo"
                            :roomsData="roomsData"
                            :treatmentTypesData="treatmentTypesData"
                            :isLoading="isLoading"
                            :showDateTimeRangePicker="true"
                        />
                    </a-tab-pane>
                    <a-tab-pane
                        key="event"
                        :tab="$t('calendar.event', 'Event')"
                    >
                        <EventFormFields
                            :formData="eventFormData"
                            @update:formData="
                                (newData) =>
                                    Object.assign(eventFormData, newData)
                            "
                        />
                    </a-tab-pane>
                </a-tabs>
            </div>
        </div>

        <!-- Footer Actions -->
        <div
            class="px-6 py-4 flex justify-end gap-3 border-t bg-gray-50 rounded-b-lg"
        >
            <a-button
                @click="onClose"
                class="w-[100px] hover:text-gray-900 border-gray-300"
            >
                Cancel
            </a-button>
            <a-button
                type="primary"
                :loading="isSubmitting"
                @click="handleSubmit"
                :disabled="isSubmitDisabled"
                class="bg-gray-900 hover:bg-gray-800 border-0 w-[100px] font-medium shadow-none"
            >
                {{ isSubmitting ? "Saving" : "Create" }}
            </a-button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, computed, inject } from "vue";
import { useI18n } from "vue-i18n";
import { CloseOutlined, ClockCircleOutlined } from "@ant-design/icons-vue";
import { Modal } from "ant-design-vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import AppointmentFormFields from "./AppointmentFormFields.vue";
import EventFormFields from "./EventFormFields.vue";
import moment from "moment";
import dayjs from "dayjs";

const emit = defineEmits(["closed", "addEditSuccess", "warningState"]);

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    formData: {
        type: Object,
        default: () => ({}),
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
});

const { t } = useI18n();
const { addEditRequestAdmin, rules } = apiAdmin();
const isSubmitting = ref(false);
const activeTab = ref("appointment");
const eventFormData = ref({
    title: "",
    patient_id: null,
    description: "",
    color: "#039be5",
    duration_display: 30,
    duration_unit: "minutes",
});

const isSubmitDisabled = computed(() => {
    if (activeTab.value === "appointment") {
        return !props.formData.patient_id || !props.formData.treatment_type_id;
    } else {
        return !eventFormData.value.title;
    }
});

const showEndDate = computed(() => {
    let startStr, endStr;
    if (activeTab.value === "appointment") {
        if (
            !props.formData.selectedDate_full ||
            !props.formData.selectedTimeSlot
        )
            return false;
        startStr = props.formData.selectedDate_full;
        const durationMins =
            props.formData.duration_unit === "hours"
                ? parseInt(props.formData.duration_display || 1) * 60
                : parseInt(props.formData.duration_display || 30);
        endStr = dayjs(`${startStr} ${props.formData.selectedTimeSlot}`)
            .add(durationMins, "minute")
            .format("YYYY-MM-DD");
    } else {
        if (
            !eventFormData.value.selectedDate_full ||
            !eventFormData.value.selectedTimeSlot
        )
            return false;
        startStr = eventFormData.value.selectedDate_full;
        const durationMins =
            eventFormData.value.duration_unit === "hours"
                ? parseInt(eventFormData.value.duration_display || 1) * 60
                : parseInt(eventFormData.value.duration_display || 30);
        endStr = dayjs(`${startStr} ${eventFormData.value.selectedTimeSlot}`)
            .add(durationMins, "minute")
            .format("YYYY-MM-DD");
    }

    return startStr !== endStr;
});

watch(
    () => props.visible,
    (isVisible) => {
        if (isVisible) {
            if (props.formData.form_type) {
                activeTab.value = props.formData.form_type;
            } else {
                activeTab.value = "appointment";
            }
            props.formData.form_type = activeTab.value;

            // Initialize full date
            if (
                props.formData.currentYear !== undefined &&
                props.formData.currentMonth !== undefined &&
                props.formData.selectedDate
            ) {
                props.formData.selectedDate_full = moment()
                    .year(props.formData.currentYear)
                    .month(props.formData.currentMonth)
                    .date(props.formData.selectedDate)
                    .format("YYYY-MM-DD");

                // Initialize end time based on selectedTimeSlot and default duration
                if (
                    props.formData.selectedTimeSlot &&
                    !props.formData.endTime
                ) {
                    const durationMins =
                        props.formData.duration_unit === "hours"
                            ? parseInt(props.formData.duration_display || 1) *
                              60
                            : parseInt(props.formData.duration_display || 30);

                    const isPM = props.formData.selectedTimeSlot
                        .toLowerCase()
                        .includes("pm");
                    const [timePart] = props.formData.selectedTimeSlot
                        .toLowerCase()
                        .split(/am|pm/);
                    const [hours, minutes] = timePart.split(":");
                    let hour = parseInt(hours);
                    if (isPM && hour !== 12) hour += 12;
                    else if (!isPM && hour === 12) hour = 0;

                    const startM = moment()
                        .hour(hour)
                        .minute(minutes || 0);
                    props.formData.endTime = startM
                        .add(durationMins, "minutes")
                        .format("HH:mm");
                    // Reset to standard format
                    props.formData.selectedTimeSlot = moment()
                        .hour(hour)
                        .minute(minutes || 0)
                        .format("HH:mm");
                }

                // Sync to eventFormData as well
                eventFormData.value = {
                    ...eventFormData.value,
                    selectedDate_full: props.formData.selectedDate_full,
                    selectedTimeSlot: props.formData.selectedTimeSlot,
                    endTime: props.formData.endTime,
                };
            }
        }
    },
    { immediate: true },
);

watch(activeTab, (newTab) => {
    props.formData.form_type = newTab;
});

// Removed displayTime and displayDate computed props

const convertTo24HourFormat = (time) => {
    if (!time) return "";
    if (
        !time.toLowerCase().includes("am") &&
        !time.toLowerCase().includes("pm")
    )
        return time;

    const isPM = time.toLowerCase().includes("pm");
    const [timePart] = time.toLowerCase().split(/am|pm/);
    const [hours, minutes] = timePart.split(":");

    let hour = parseInt(hours);
    if (isPM && hour !== 12) hour += 12;
    else if (!isPM && hour === 12) hour = 0;

    return `${hour.toString().padStart(2, "0")}:${minutes || "00"}`;
};

const handleSubmit = () => {
    if (activeTab.value === "event") {
        submitEvent();
    } else {
        submitBooking(false);
    }
};

const submitEvent = () => {
    if (!eventFormData.value.title) return;

    isSubmitting.value = true;

    const finalDuration =
        eventFormData.value.duration_unit === "hours"
            ? parseInt(eventFormData.value.duration_display || 1) * 60
            : parseInt(eventFormData.value.duration_display || 30);

    const bookingData = {
        doctor_id: props.formData.doctor_id,
        patient_id: eventFormData.value.patient_id,
        title: eventFormData.value.title,
        description: eventFormData.value.description,
        color: eventFormData.value.color,
        event_date: eventFormData.value.selectedDate_full,
        event_time: eventFormData.value.selectedTimeSlot,
        duration: finalDuration,
        _method: "POST",
    };

    addEditRequestAdmin({
        url: "calendar-events",
        data: bookingData,
        successMessage: t("calendar.event_created", "Event Created"),
        success: () => {
            isSubmitting.value = false;
            // Reset form
            eventFormData.value = {
                title: "",
                description: "",
                color: "#039be5",
                duration_display: 30,
                duration_unit: "minutes",
            };
            emit("addEditSuccess");
            onClose();
        },
        error: (err) => {
            isSubmitting.value = false;
        },
    });
};

const submitBooking = (force = false) => {
    if (!props.formData.patient_id || !props.formData.treatment_type_id) return;

    isSubmitting.value = true;

    const finalDuration =
        props.formData.duration_unit === "hours"
            ? parseInt(props.formData.duration_display || 1) * 60
            : parseInt(props.formData.duration_display || 30);

    const bookingData = {
        doctor_id: props.formData.doctor_id,
        patient_id: props.formData.patient_id,
        appointment_date: props.formData.selectedDate_full,
        appointment_time: props.formData.selectedTimeSlot,
        duration: finalDuration,
        reason_visit: props.formData.reason_visit || "",
        _method: "POST",
        room_id: props.formData.room_id,
        treatment_type_id: props.formData.treatment_type_id,
        price: props.formData.price || 0,
        force: force,
    };

    addEditRequestAdmin({
        url: "doctor-schedule-days/create-appointment",
        data: bookingData,
        successMessage: t("appointments.created", "Appointment Created"),
        success: () => {
            isSubmitting.value = false;
            emit("addEditSuccess");
            onClose();
        },
        error: (err) => {
            isSubmitting.value = false;

            if (err.data?.is_warning) {
                const buildWarningMessage = (errData) => {
                    if (!errData || !errData.details || !errData.details.type) {
                        return (
                            errData?.message || t("common.warning", "Warning")
                        );
                    }
                    const details = errData.details;
                    let vars = {};

                    if (details.type === "doctor_holiday") {
                        vars = { from: details.from, to: details.to };
                    } else if (details.type === "time_outside_schedule") {
                        vars = {
                            from: details.schedule_start,
                            to: details.schedule_end,
                        };
                    } else if (details.type === "doctor_break_conflict") {
                        vars = {
                            from: details.break_start,
                            to: details.break_end,
                        };
                    } else if (details.type === "appointment_conflict") {
                        vars = {
                            from: details.existing_appointment?.from,
                            to: details.existing_appointment?.to,
                            duration: details.existing_appointment?.duration,
                        };
                    }

                    return t(`appointments.warning_${details.type}`, vars);
                };

                emit("warningState", true);
                Modal.confirm({
                    title: t("common.warning", "Warning"),
                    content: buildWarningMessage(err.data),
                    okText: t("common.proceed_anyway", "Proceed Anyway"),
                    cancelText: t("common.cancel", "Cancel"),
                    zIndex: 10000,
                    onOk: () => {
                        setTimeout(() => emit("warningState", false), 200);
                        submitBooking(true); // Retry with force flag
                    },
                    onCancel: () => {
                        setTimeout(() => emit("warningState", false), 200);
                    },
                });
            }
        },
    });
};

const onClose = () => {
    emit("closed");
};
</script>
