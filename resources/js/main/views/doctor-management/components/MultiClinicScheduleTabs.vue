<template>
    <div class="multi-clinic-schedule-tabs">
        <div v-if="loading" class="text-center p-4">
            <a-spin />
        </div>
        <div v-else-if="clinics.length === 0" class="text-center p-4">
            <a-empty description="No clinics assigned or available." />
        </div>
        <a-tabs v-else v-model:activeKey="activeKey">
            <a-tab-pane
                v-for="clinic in clinics"
                :key="clinic.xid"
                :tab="clinic.name"
            >
                <div>
                    <div class="mb-4">
                        <label class="block mb-2 font-bold"
                            >{{ $t("doctor_schedules.per_patient_time") }} ({{
                                $t("common.minutes")
                            }})</label
                        >
                        <a-input-number
                            v-model:value="
                                clinic.scheduleData.per_patient_time_minutes
                            "
                            :min="5"
                            :max="180"
                            :step="5"
                            style="width: 200px"
                            :addon-after="$t('common.minutes')"
                        />
                    </div>

                    <ScheduleTable
                        :schedule="clinic.schedule"
                        :days="days"
                        :handleDaySwitch="
                            (day, checked) =>
                                handleDaySwitch(clinic.xid, day, checked)
                        "
                        :isDayAvailable="
                            (day) => isDayAvailable(clinic.xid, day)
                        "
                        :getDisabledTimesForDay="
                            (day) => getDisabledTimesForDay(clinic.xid, day)
                        "
                        :rules="{}"
                    />

                    <div class="text-right mt-4">
                        <a-button
                            type="primary"
                            :loading="clinic.saving"
                            @click="saveClinicSchedule(clinic)"
                        >
                            <SaveOutlined /> {{ $t("doctors.save_schedule") }} -
                            {{ clinic.name }}
                        </a-button>
                    </div>
                </div>
            </a-tab-pane>
        </a-tabs>
    </div>
</template>

<script>
import { defineComponent, ref, onMounted } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import { notification } from "ant-design-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import useSchedule from "../components/useSchedule";
import ScheduleTable from "../components/ScheduleTable.vue";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: {
        doctorId: {
            required: true,
        },
    },
    components: {
        SaveOutlined,
        ScheduleTable,
    },
    setup(props) {
        const { clinicLocations } = common(); // Get all available clinics from common
        const { addEditRequestAdmin } = apiAdmin();

        const loading = ref(false);
        const clinics = ref([]);
        const activeKey = ref(null);

        const days = [
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
            "sunday",
        ];

        // Fetch schedules
        const fetchSchedules = () => {
            loading.value = true;
            axiosAdmin
                .get(
                    `doctor-schedules/get-all-schedules?doctor_id=${props.doctorId}`,
                )
                .then((res) => {
                    const schedules =
                        res.data && res.data.data
                            ? res.data.data
                            : Array.isArray(res.data)
                              ? res.data
                              : []; // Handle both wrapped and unwrapped

                    clinicSchedules.value = schedules; // Store raw schedules

                    clinics.value = clinicLocations.value.map((loc) => {
                        // Default structure
                        const defaultSchedule = {
                            monday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                            tuesday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                            wednesday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                            thursday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                            friday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                            saturday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                            sunday: {
                                status: 0,
                                from: "09:00:00",
                                to: "18:00:00",
                            },
                        };

                        let scheduleData = { ...defaultSchedule };
                        let perPatientTime = "00:30:00";

                        const existingSchedule = schedules.find(
                            (s) =>
                                s.x_clinic_id === loc.xid || // Match via Hash ID (Definitive)
                                s.clinic_id === loc.id, // Fallback
                        );

                        if (
                            existingSchedule &&
                            existingSchedule.schedule &&
                            existingSchedule.schedule.length > 0
                        ) {
                            perPatientTime = existingSchedule.per_patient_time;
                            existingSchedule.schedule.forEach((daySch) => {
                                // Map day_of_week (1-7) to name
                                const dayName = days[daySch.day_of_week - 1]; // 1=Monday (index 0)
                                if (dayName) {
                                    scheduleData[dayName] = {
                                        status: daySch.status,
                                        from: daySch.available_from,
                                        to: daySch.available_to,
                                    };
                                }
                            });
                        }

                        return {
                            xid: loc.xid,
                            id: loc.id,
                            name: loc.name,
                            schedule: scheduleData,
                            scheduleData: {
                                // Wrapper for specific fields
                                per_patient_time: perPatientTime,
                                // Convert to minutes for UI
                                per_patient_time_minutes:
                                    timeToMinutes(perPatientTime),
                            },
                            saving: false,
                        };
                    });

                    if (clinics.value.length > 0) {
                        activeKey.value = clinics.value[0].xid;
                    }
                })
                .finally(() => {
                    loading.value = false;
                });
        };

        const handleDaySwitch = (clinicXid, day, checked) => {
            const clinic = clinics.value.find((c) => c.xid === clinicXid);
            if (clinic) {
                clinic.schedule[day].status = checked ? 1 : 0;
            }
        };

        const isDayAvailable = (clinicXid, day) => {
            // Since we don't have the operating hours for every clinic loaded in the frontend,
            // we assume the clinic is open every day to allow the doctor to be scheduled.
            // If we returned 'false', the checkbox would be disabled/hidden.
            return true;
        };

        const getDisabledTimesForDay = (clinicXid, day) => {
            return { disabledHours: () => [], disabledMinutes: () => [] };
        };

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

        const saveClinicSchedule = (clinic) => {
            clinic.saving = true;

            // Format time
            const ppt = minutesToTime(
                clinic.scheduleData.per_patient_time_minutes,
            );

            // Format schedule payload
            const schedulePayload = {};
            days.forEach((day) => {
                schedulePayload[day] = clinic.schedule[day];
            });

            const data = {
                doctor_id: props.doctorId,
                per_patient_time: ppt,
                schedule: schedulePayload,
                company_id: clinic.xid,
                _method: "POST", // Store or update handled by controller
            };

            axiosAdmin
                .post(`doctor-schedules/store`, data)
                .then(() => {
                    notification.success({
                        message: "Success",
                        description: `Schedule for ${clinic.name} updated.`,
                    });
                })
                .catch((err) => {
                    notification.error({
                        message: "Error",
                        description: "Failed to update schedule.",
                    });
                })
                .finally(() => {
                    clinic.saving = false;
                });
        };

        onMounted(() => {
            fetchSchedules();
        });

        return {
            loading,
            clinics,
            activeKey,
            days,
            handleDaySwitch,
            isDayAvailable,
            getDisabledTimesForDay,
            saveClinicSchedule,
        };
    },
});
</script>

<style scoped>
.multi-clinic-schedule-tabs {
    /* Add any custom styles */
}
</style>
