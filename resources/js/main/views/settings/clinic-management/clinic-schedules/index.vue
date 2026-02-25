<template>
    <div class="clinic-schedules-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-button type="primary" @click="onSubmit" :loading="loading">
                <SaveOutlined />
                {{ $t("common.save") }}
            </a-button>
        </div>

        <a-row :gutter="[16, 16]">
            <a-col :span="24">
                <a-alert
                    type="info"
                    :message="$t('clinic_schedules.instructions')"
                    :description="
                        $t('clinic_schedules.set_schedule_for_each_day')
                    "
                    show-icon
                    class="mb-20"
                />
            </a-col>
            <a-col :span="24">
                <a-row
                    v-for="(day, index) in daysOfWeek"
                    :key="index"
                    :gutter="[16, 16]"
                    class="mb-10 schedule-row"
                    align="middle"
                >
                    <a-col :xs="24" :sm="8" :md="6" :lg="5" class="day-label">
                        <a-checkbox
                            v-model:checked="schedules[day].isOpen"
                            @change="
                                (e) => handleDayToggle(day, e.target.checked)
                            "
                        >
                            <span class="day-name">{{
                                $t("common.day_" + day)
                            }}</span>
                        </a-checkbox>
                    </a-col>
                    <a-col :xs="24" :sm="16" :md="18" :lg="19">
                        <a-row :gutter="[16, 16]" v-if="schedules[day].isOpen">
                            <a-col
                                :xs="12"
                                :sm="10"
                                :md="12"
                                :lg="12"
                                class="time-label"
                            >
                                <span>{{
                                    $t("clinic_schedules.opening_time")
                                }}</span>
                                <TimePicker
                                    :time="schedules[day].startTime"
                                    @timeChanged="
                                        (time) =>
                                            handleTimeChange(day, 'start', time)
                                    "
                                    :disabled="!schedules[day].isOpen"
                                    class="w-full"
                                />
                            </a-col>
                            <a-col
                                :xs="12"
                                :sm="10"
                                :md="12"
                                :lg="12"
                                class="time-label"
                            >
                                <span>{{
                                    $t("clinic_schedules.closing_time")
                                }}</span>
                                <TimePicker
                                    :time="schedules[day].endTime"
                                    @timeChanged="
                                        (time) =>
                                            handleTimeChange(day, 'end', time)
                                    "
                                    :disabled="!schedules[day].isOpen"
                                    class="w-full"
                                />
                            </a-col>
                        </a-row>
                        <a-row v-else>
                            <a-col :span="24">
                                <a-tag color="red">{{
                                    $t("clinic_schedules.closed")
                                }}</a-tag>
                            </a-col>
                        </a-row>
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { reactive, onMounted } from "vue";
import { SaveOutlined, CheckOutlined } from "@ant-design/icons-vue";
import fields from "./fields";
import apiAdmin from "../../../../../common/composable/apiAdmin";
import TimePicker from "../../../../../common/components/common/calendar/TimePicker.vue";
import { useI18n } from "vue-i18n";

export default {
    components: {
        SaveOutlined,
        CheckOutlined,
        TimePicker,
    },
    setup() {
        const { t } = useI18n();
        const { url, addEditUrl } = fields();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        // Define days of the week as numbers 1-7
        const daysOfWeek = [1, 2, 3, 4, 5, 6, 7];

        // Initialize schedules object
        const schedules = reactive({
            1: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
            2: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
            3: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
            4: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
            5: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
            6: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
            7: { isOpen: false, startTime: "09:00:00", endTime: "17:00:00" },
        });

        // Handle checkbox change for each day
        const handleDayToggle = (day, isChecked) => {
            if (isChecked) {
                // Find the last open day's times (before this day)
                let lastOpenDay = null;
                for (let i = day - 1; i >= 1; i--) {
                    if (schedules[i] && schedules[i].isOpen) {
                        lastOpenDay = i;
                        break;
                    }
                }
                if (lastOpenDay) {
                    schedules[day].startTime = schedules[lastOpenDay].startTime;
                    schedules[day].endTime = schedules[lastOpenDay].endTime;
                }
            }
            schedules[day].isOpen = isChecked;
        };

        // Handle time change for start/end times
        const handleTimeChange = (day, type, time) => {
            if (type === "start") {
                schedules[day].startTime = time;
            } else {
                schedules[day].endTime = time;
            }
        };

        const fetchSchedules = async () => {
            try {
                const response = await axiosAdmin.get(url);
                const scheduleData = response.data;

                // Reset all days to closed first
                daysOfWeek.forEach((day) => {
                    schedules[day].isOpen = false;
                });

                // Update schedules based on data from API
                scheduleData.forEach((schedule) => {
                    const day = Number(schedule.day_of_week);
                    if (schedules[day]) {
                        schedules[day].isOpen = true;
                        schedules[day].startTime = schedule.start_time;
                        schedules[day].endTime = schedule.end_time;
                    }
                });
            } catch (error) {
                console.error("Failed to fetch schedules:", error);
            }
        };

        const onSubmit = () => {
            // Format the schedules data for the API
            const schedulesToSave = [];

            for (const day in schedules) {
                if (schedules[day].isOpen) {
                    schedulesToSave.push({
                        day_of_week: Number(day),
                        start_time: schedules[day].startTime,
                        end_time: schedules[day].endTime,
                    });
                }
            }

            addEditRequestAdmin({
                url: addEditUrl,
                data: {
                    schedules: schedulesToSave,
                },
                successMessage: t("clinic_schedules.updated"),
            });
        };

        onMounted(() => {
            fetchSchedules();
        });

        return {
            loading,
            rules,
            daysOfWeek,
            schedules,
            handleDayToggle,
            handleTimeChange,
            onSubmit,
        };
    },
};
</script>

<style scoped>
.schedule-row {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.schedule-row:last-child {
    border-bottom: none;
}

.day-label {
    font-weight: 500;
}

.day-name {
    margin-left: 8px;
}

.time-label {
    display: flex;
    flex-direction: column;
}

.time-label span {
    margin-bottom: 5px;
    font-size: 12px;
    color: rgba(0, 0, 0, 0.65);
}
</style>
