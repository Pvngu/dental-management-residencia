<template>
    <a-modal
        :open="visible"
        :title="$t('calendar.new_event', 'New Event')"
        :width="500"
        :footer="null"
        :maskClosable="false"
        :onCancel="handleClose"
        centered
    >
        <div class="p-4 pt-2">
            <EventFormFields
                :formData="formData"
                :autoSelectDoctor="autoSelectDoctor"
                :doctorInfo="doctorInfo"
                @update:formData="(newData) => Object.assign(formData, newData)"
            />

            <div class="mt-6 flex justify-end gap-3">
                <a-button @click="handleClose">
                    {{ $t("common.cancel") }}
                </a-button>
                <a-button
                    type="primary"
                    :loading="isSubmitting"
                    :disabled="isSubmitDisabled"
                    @click="handleSubmit"
                >
                    {{ $t("common.submit") }}
                </a-button>
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { ref, watch, reactive, computed } from "vue";
import EventFormFields from "./EventFormFields.vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import { useI18n } from "vue-i18n";
import dayjs from "dayjs";

const { t } = useI18n();

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    initialData: {
        type: Object,
        default: () => ({}),
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

const emit = defineEmits(["closed", "addEditSuccess"]);

const { addEditRequestAdmin } = apiAdmin();

const isSubmitting = ref(false);

const formData = reactive({
    title: "",
    duration_display: 30,
    duration_unit: "minutes",
    patient_id: null,
    doctor_id: null,
    color: "#039be5",
    description: "",
});

const eventDate = ref(null);
const eventTime = ref(null);

watch(
    () => props.visible,
    (isVisible) => {
        if (isVisible) {
            // Reset form
            formData.title = "";
            formData.duration_display = 30;
            formData.duration_unit = "minutes";
            formData.patient_id = null;
            formData.doctor_id = props.autoSelectDoctor
                ? props.initialData.doctor_id
                : null;
            formData.color = "#039be5";
            formData.description = "";

            if (props.initialData) {
                if (
                    props.initialData.currentYear !== undefined &&
                    props.initialData.currentMonth !== undefined &&
                    props.initialData.selectedDate !== undefined
                ) {
                    const startM = dayjs(
                        new Date(
                            props.initialData.currentYear,
                            props.initialData.currentMonth,
                            props.initialData.selectedDate,
                        ),
                    );
                    formData.selectedDate_full = startM.format("YYYY-MM-DD");
                } else {
                    formData.selectedDate_full = dayjs().format("YYYY-MM-DD");
                }
            } else {
                formData.selectedDate_full = dayjs().format("YYYY-MM-DD");
            }

            const now = dayjs().startOf("hour");
            formData.selectedTimeSlot = now.format("HH:mm");
            formData.endTime = now.add(30, "minute").format("HH:mm");
        }
    },
);

const isSubmitDisabled = computed(() => {
    return (
        !formData.title ||
        !formData.selectedDate_full ||
        !formData.selectedTimeSlot ||
        (!props.autoSelectDoctor && !formData.doctor_id)
    );
});

const handleClose = () => {
    emit("closed");
};

const handleSubmit = () => {
    if (isSubmitDisabled.value) return;

    isSubmitting.value = true;

    const finalDuration =
        formData.duration_unit === "hours"
            ? parseInt(formData.duration_display || 1) * 60
            : parseInt(formData.duration_display || 30);

    const bookingData = {
        doctor_id: props.autoSelectDoctor
            ? props.initialData.doctor_id
            : formData.doctor_id,
        patient_id: formData.patient_id,
        title: formData.title,
        description: formData.description,
        color: formData.color,
        event_date: formData.selectedDate_full,
        event_time: formData.selectedTimeSlot,
        duration: finalDuration,
        _method: "POST",
    };

    addEditRequestAdmin({
        url: "calendar-events",
        data: bookingData,
        successMessage: t(
            "calendar.event_created",
            "Event created successfully",
        ),
        success: (res) => {
            isSubmitting.value = false;
            emit("addEditSuccess", res);
            handleClose();
        },
        error: (err) => {
            isSubmitting.value = false;
        },
    });
};
</script>
