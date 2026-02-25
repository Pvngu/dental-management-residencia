<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <a-form-item
                    label="Doctor:"
                    required
                    :help="rules.doctor_id ? rules.doctor_id.message : null"
                    :validateStatus="rules.doctor_id ? 'error' : null"
                    class="required"
                >
                    <UserSelect
                        @onChange="
                            (id) => {
                                formData.doctor_id = id;
                            }
                        "
                        :value="formData.doctor_id"
                        userType="doctor"
                        :mode="addEditType === 'add' ? 'multiple' : undefined"
                    />
                    <a-alert
                        v-if="addEditType === 'add'"
                        message="Note: If any selected doctor already has a schedule in the CURRENT CLINIC, it will be replaced."
                        type="warning"
                        show-icon
                        class="mt-2"
                    />
                </a-form-item>

                <a-form-item
                    label="Per Patient Time:"
                    required
                    :help="
                        rules.per_patient_time
                            ? rules.per_patient_time.message
                            : null
                    "
                    :validateStatus="rules.per_patient_time ? 'error' : null"
                    class="required"
                >
                    <a-input-number
                        v-model:value="formData.per_patient_time_minutes"
                        :min="5"
                        :max="180"
                        :step="5"
                        placeholder="Enter minutes"
                        style="width: 100%"
                        addon-after="minutes"
                    />
                </a-form-item>
            </div>

            <ScheduleTable
                :schedule="schedule"
                :days="days"
                :handleDaySwitch="handleDaySwitch"
                :isDayAvailable="isDayAvailable"
                :getDisabledTimesForDay="getDisabledTimesForDay"
                :rules="rules"
            />
        </a-form>
        <template #footer>
            <a-button
                type="primary"
                @click="onSubmit"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, watch } from "vue";
import { SaveOutlined, CopyOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import UserSelect from "../../../../common/components/common/select/UserSelect.vue";
import ScheduleTable from "../components/ScheduleTable.vue";
import fields from "./fields";
import useSchedule from "../components/useSchedule";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "statuses",
    ],
    components: {
        SaveOutlined,
        CopyOutlined,
        UserSelect,
        ScheduleTable,
    },
    emits: ["addEditSuccess", "closed"],
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { scheduleColumns } = fields();
        const {
            days,
            schedule,
            isDayAvailable,
            getDisabledTimesForDay,
            handleDaySwitch,
            initializeSchedule,
            resetSchedule,
            getScheduleDataObject,
        } = useSchedule();

        // Helper functions for time conversion
        const timeToMinutes = (timeString) => {
            if (!timeString) return 0;
            const [hours, minutes] = timeString.split(":").map(Number);
            return hours * 60 + minutes;
        };

        const minutesToTime = (minutes) => {
            if (!minutes || minutes === 0) return "00:00:00";
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours.toString().padStart(2, "0")}:${mins.toString().padStart(2, "0")}:00`;
        };

        // Watch for edit mode and populate schedule
        watch(
            () => props.visible,
            (visible) => {
                if (
                    visible &&
                    props.addEditType == "edit" &&
                    props.formData &&
                    props.formData.schedule
                ) {
                    initializeSchedule(props.formData.schedule);
                    // Convert time to minutes for the input
                    if (props.formData.per_patient_time) {
                        props.formData.per_patient_time_minutes = timeToMinutes(
                            props.formData.per_patient_time,
                        );
                    }
                } else if (visible) {
                    // Set default value for new records
                    props.formData.per_patient_time_minutes = 30;
                }
                // Reset on close
                if (!visible) {
                    resetSchedule();
                }
            },
        );

        const onSubmit = () => {
            // Convert minutes to time format before submission
            if (props.formData.per_patient_time_minutes) {
                props.formData.per_patient_time = minutesToTime(
                    props.formData.per_patient_time_minutes,
                );
            }

            // Process schedule data
            props.formData.schedule = getScheduleDataObject();

            // Remove the minutes field before sending
            const filteredFormData = { ...props.formData };
            delete filteredFormData.per_patient_time_minutes;

            addEditRequestAdmin({
                url:
                    props.url +
                    (props.addEditType === "add" ? "/store" : "/update"),
                data: filteredFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    console.log(res);
                    emit("addEditSuccess");
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
            days,
            schedule,
            handleDaySwitch,
            isDayAvailable,
            getDisabledTimesForDay,
        };
    },
});
</script>
