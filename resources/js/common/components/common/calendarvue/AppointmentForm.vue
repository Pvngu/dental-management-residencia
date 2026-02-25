<template>
    <div class="appointment-form">
        <a-form layout="vertical" :model="formData">
            <a-row :gutter="16">
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.patient_name')"
                        name="patient_name"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.patient_name')]) }]"
                    >
                        <a-input
                            v-model:value="formData.patient_name"
                            :placeholder="$t('common.placeholder_default_text', [$t('calendar.patient_name')])"
                        />
                    </a-form-item>
                </a-col>
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.dentist')"
                        name="dentist_id"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.dentist')]) }]"
                    >
                        <a-select
                            v-model:value="formData.dentist_id"
                            :placeholder="$t('common.select_default_text', [$t('calendar.dentist')])"
                            style="width: 100%"
                        >
                            <a-select-option
                                v-for="dentist in dentists"
                                :key="dentist.xid"
                                :value="dentist.xid"
                            >
                                {{ dentist.name }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.date')"
                        name="date"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.date')]) }]"
                    >
                        <a-date-picker
                            v-model:value="formData.date"
                            style="width: 100%"
                            format="YYYY-MM-DD"
                        />
                    </a-form-item>
                </a-col>
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.treatment_type')"
                        name="treatment_type"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.treatment_type')]) }]"
                    >
                        <a-select
                            v-model:value="formData.treatment_type"
                            :placeholder="$t('common.select_default_text', [$t('calendar.treatment_type')])"
                            style="width: 100%"
                        >
                            <a-select-option value="general_checkup">{{ $t('calendar.general_checkup') }}</a-select-option>
                            <a-select-option value="scaling">{{ $t('calendar.scaling') }}</a-select-option>
                            <a-select-option value="bleaching">{{ $t('calendar.bleaching') }}</a-select-option>
                            <a-select-option value="extraction">{{ $t('calendar.extraction') }}</a-select-option>
                            <a-select-option value="tooth_scaling">{{ $t('calendar.tooth_scaling') }}</a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.start_time')"
                        name="start_time"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.start_time')]) }]"
                    >
                        <a-time-picker
                            v-model:value="formData.start_time"
                            style="width: 100%"
                            format="HH:mm A"
                            use12Hours
                        />
                    </a-form-item>
                </a-col>
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.end_time')"
                        name="end_time"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.end_time')]) }]"
                    >
                        <a-time-picker
                            v-model:value="formData.end_time"
                            style="width: 100%"
                            format="HH:mm A"
                            use12Hours
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.status')"
                        name="status"
                        :rules="[{ required: true, message: $t('validation.required', [$t('calendar.status')]) }]"
                    >
                        <a-select
                            v-model:value="formData.status"
                            :placeholder="$t('common.select_default_text', [$t('calendar.status')])"
                            style="width: 100%"
                        >
                            <a-select-option value="registered">{{ $t('calendar.registered') }}</a-select-option>
                            <a-select-option value="finished">{{ $t('calendar.finished') }}</a-select-option>
                            <a-select-option value="waiting_payment">{{ $t('calendar.waiting_payment') }}</a-select-option>
                            <a-select-option value="cancelled">{{ $t('calendar.cancelled') }}</a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :span="12">
                    <a-form-item
                        :label="$t('calendar.patient_phone')"
                        name="patient_phone"
                    >
                        <a-input
                            v-model:value="formData.patient_phone"
                            :placeholder="$t('common.placeholder_default_text', [$t('calendar.patient_phone')])"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :span="24">
                    <a-form-item
                        :label="$t('calendar.notes')"
                        name="notes"
                    >
                        <a-textarea
                            v-model:value="formData.notes"
                            :placeholder="$t('common.placeholder_default_text', [$t('calendar.notes')])"
                            :auto-size="{ minRows: 3, maxRows: 6 }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16" class="form-actions">
                <a-col :span="24" style="text-align: right;">
                    <a-space>
                        <a-button @click="onCancel">
                            {{ $t("common.cancel") }}
                        </a-button>
                        <a-button 
                            type="primary" 
                            @click="onSave"
                            :loading="loading"
                        >
                            <template #icon><SaveOutlined /></template>
                            {{ mode === "add" ? $t("common.create") : $t("common.update") }}
                        </a-button>
                    </a-space>
                </a-col>
            </a-row>
        </a-form>
    </div>
</template>

<script>
import { defineComponent, ref, watch, onMounted } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import moment from "moment";

export default defineComponent({
    name: "AppointmentForm",
    components: {
        SaveOutlined,
    },
    props: {
        appointment: {
            type: Object,
            default: () => ({}),
        },
        dentists: {
            type: Array,
            default: () => [],
        },
        mode: {
            type: String,
            default: "add", // add or edit
        },
    },
    emits: ["save", "cancel"],
    setup(props, { emit }) {
        const { t } = useI18n();
        const loading = ref(false);

        const formData = ref({
            id: null,
            patient_name: "",
            dentist_id: "",
            date: null,
            start_time: null,
            end_time: null,
            treatment_type: "",
            status: "registered",
            patient_phone: "",
            notes: "",
        });

        // Initialize form data
        const initializeForm = () => {
            if (props.mode === "edit" && props.appointment) {
                formData.value = {
                    id: props.appointment.id,
                    patient_name: props.appointment.patient_name || "",
                    dentist_id: props.appointment.dentist_id || "",
                    date: props.appointment.date ? moment(props.appointment.date) : null,
                    start_time: props.appointment.start_time ? moment(props.appointment.start_time, "HH:mm A") : null,
                    end_time: props.appointment.end_time ? moment(props.appointment.end_time, "HH:mm A") : null,
                    treatment_type: props.appointment.treatment_type || "",
                    status: props.appointment.status || "registered",
                    patient_phone: props.appointment.patient_phone || "",
                    notes: props.appointment.notes || "",
                };
            } else {
                // Pre-fill for add mode if appointment data provided (like dentist_id, date, time from slot)
                formData.value = {
                    id: null,
                    patient_name: "",
                    dentist_id: props.appointment?.dentist_id || "",
                    date: props.appointment?.date ? moment(props.appointment.date) : moment(),
                    start_time: props.appointment?.start_time ? moment(props.appointment.start_time, "HH:mm A") : null,
                    end_time: null,
                    treatment_type: "",
                    status: "registered",
                    patient_phone: "",
                    notes: "",
                };
            }
        };

        const validateForm = () => {
            const requiredFields = ['patient_name', 'dentist_id', 'date', 'start_time', 'end_time', 'treatment_type', 'status'];
            
            for (const field of requiredFields) {
                if (!formData.value[field]) {
                    return false;
                }
            }

            // Validate time logic
            if (formData.value.start_time && formData.value.end_time) {
                if (formData.value.end_time.isSameOrBefore(formData.value.start_time)) {
                    return false;
                }
            }

            return true;
        };

        const onSave = async () => {
            if (!validateForm()) {
                return;
            }

            loading.value = true;

            try {
                // Prepare data for saving
                const saveData = {
                    id: formData.value.id,
                    patient_name: formData.value.patient_name,
                    dentist_id: formData.value.dentist_id,
                    date: formData.value.date.format("YYYY-MM-DD"),
                    start_time: formData.value.start_time.format("HH:mm A"),
                    end_time: formData.value.end_time.format("HH:mm A"),
                    treatment_type: formData.value.treatment_type,
                    status: formData.value.status,
                    patient_phone: formData.value.patient_phone,
                    notes: formData.value.notes,
                };

                // Emit save event
                emit("save", saveData);
            } catch (error) {
                console.error("Error saving appointment:", error);
            } finally {
                loading.value = false;
            }
        };

        const onCancel = () => {
            emit("cancel");
        };

        // Watch for prop changes
        watch(
            () => props.appointment,
            () => {
                initializeForm();
            },
            { deep: true }
        );

        watch(
            () => props.mode,
            () => {
                initializeForm();
            }
        );

        onMounted(() => {
            initializeForm();
        });

        return {
            formData,
            loading,
            onSave,
            onCancel,
        };
    },
});
</script>

<style scoped>
.appointment-form {
    padding: 16px 0;
}

.form-actions {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.ant-form-item {
    margin-bottom: 16px;
}

.ant-form-item-label > label {
    font-weight: 500;
}

/* Custom styling for time pickers */
:deep(.ant-picker) {
    width: 100%;
}

/* Custom styling for select dropdowns */
:deep(.ant-select-dropdown) {
    border-radius: 6px;
}

/* Form validation styling */
:deep(.ant-form-item-has-error .ant-input) {
    border-color: #ff4d4f;
}

:deep(.ant-form-item-has-error .ant-select-selector) {
    border-color: #ff4d4f !important;
}

:deep(.ant-form-item-has-error .ant-picker) {
    border-color: #ff4d4f;
}

/* Responsive design */
@media (max-width: 768px) {
    .appointment-form {
        padding: 12px 0;
    }
    
    .ant-form-item {
        margin-bottom: 12px;
    }
    
    .form-actions {
        margin-top: 16px;
        padding-top: 12px;
    }
}
</style>
