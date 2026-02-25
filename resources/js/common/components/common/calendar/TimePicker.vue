<template>
    <a-time-range-picker
        v-if="isRange"
        v-model:value="timeRangeValue"
        :format="format"
        :placeholder="rangePlaceholder"
        style="width: 100%"
        @change="timeRangeChanged"
        :disabled="disabled"
        :minute-step="minuteStep"
        :second-step="secondStep"
    :disabledTime="computedDisabledTime"
    />
    <a-time-picker
        v-else
        v-model:value="timeValue"
        :format="format"
        :placeholder="$t('common.select_time')"
        style="width: 100%"
        @change="timeChanged"
        :disabled="disabled"
        :minute-step="minuteStep"
        :second-step="secondStep"
    :disabledTime="computedDisabledTime"
    />
</template>

<script>
import { defineComponent, onMounted, ref, watch } from "vue";
import common from "../../../composable/common";

export default defineComponent({
    props: {
        disableOutsideSchedule: {
            type: Boolean,
            default: false,
        },
        time: {
            default: undefined,
        },
        timeRange: {
            type: Array,
            default: undefined,
        },
        isRange: {
            type: Boolean,
            default: false,
        },
        disabled: {
            default: false,
        },
        format: {
            type: String,
            default: "HH:mm",
        },
        minuteStep: {
            type: Number,
            default: 1,
        },
        secondStep: {
            type: Number,
            default: 1,
        },
        disabledTime: {
            type: Function,
            default: undefined,
        },
    },
    emits: ["timeChanged", "timeRangeChanged"],
    setup(props, { emit }) {
        const timeValue = ref(undefined);
        const timeRangeValue = ref([]);
        const { dayjs, appSetting } = common();

        // Helper: returns schedule for given dayOfWeek (0=Sun..6=Sat) matching appSetting.schedules format
        const getScheduleForDay = (day) => {
            if (!appSetting || !appSetting.value || !appSetting.value.schedules) return null;
            // incoming schedules use day_of_week 1=Monday..7=Sunday per provided data; convert
            return appSetting.value.schedules.find((s) => {
                const sDay = Number(s.day_of_week);
                // convert to 0-6 where 0 is Sunday
                const sDay0 = sDay % 7; // 7 -> 0
                return sDay0 === day;
            }) || null;
        };

        // Build a disabledTime function that respects company schedules if disableOutsideSchedule is true
        const mergedDisabledTime = (type) => {
            // If user provided a custom disabledTime prop, call it first to preserve behavior
            const userDT = props.disabledTime;

            // If not requested to enforce schedule, just return user's result
            if (!props.disableOutsideSchedule) {
                return typeof userDT === 'function' ? userDT(type) : {};
            }

            // Determine day to check: use today for single time picker, or for range use today as well
            const now = dayjs();
            const day = now.day(); // 0 (Sun) - 6 (Sat)

            const schedule = getScheduleForDay(day);

            // No schedule means fully disabled
            if (!schedule) {
                // disable all hours
                return {
                    disabledHours: () => Array.from({ length: 24 }, (_, i) => i),
                    disabledMinutes: () => Array.from({ length: 60 }, (_, i) => i),
                    disabledSeconds: () => Array.from({ length: 60 }, (_, i) => i),
                };
            }

            // Parse schedule times (HH:mm:ss)
            const startParts = schedule.start_time.split(':').map(Number);
            const endParts = schedule.end_time.split(':').map(Number);
            const startHour = startParts[0];
            const startMinute = startParts[1] || 0;
            const endHour = endParts[0];
            const endMinute = endParts[1] || 0;

            const disabledHours = () => {
                const hours = [];
                for (let h = 0; h < 24; h++) {
                    if (h < startHour || h > endHour) hours.push(h);
                }
                return hours;
            };

            const disabledMinutes = (hour) => {
                const minutes = [];
                if (hour === startHour) {
                    for (let m = 0; m < startMinute; m++) minutes.push(m);
                }
                if (hour === endHour) {
                    for (let m = endMinute + 1; m < 60; m++) minutes.push(m);
                }
                return minutes;
            };

            const disabledSeconds = (hour, minute) => {
                // Only restrict seconds at the boundary minutes
                const seconds = [];
                if (hour === startHour && minute < startMinute) {
                    return Array.from({ length: 60 }, (_, i) => i);
                }
                if (hour === endHour && minute > endMinute) {
                    return Array.from({ length: 60 }, (_, i) => i);
                }
                return seconds;
            };

            const scheduleDT = {
                disabledHours,
                disabledMinutes,
                disabledSeconds,
            };

            if (typeof userDT === 'function') {
                // merge user and schedule: user disabled should also be applied
                const userRes = userDT(type) || {};
                return {
                    disabledHours: () => Array.from(new Set([...(userRes.disabledHours ? userRes.disabledHours() : []), ...scheduleDT.disabledHours()])),
                    disabledMinutes: (h) => Array.from(new Set([...(userRes.disabledMinutes ? userRes.disabledMinutes(h) : []), ...scheduleDT.disabledMinutes(h)])),
                    disabledSeconds: (h, m) => Array.from(new Set([...(userRes.disabledSeconds ? userRes.disabledSeconds(h, m) : []), ...scheduleDT.disabledSeconds(h, m)])),
                };
            }

            return scheduleDT;
        };

        const rangePlaceholder = ['Start time', 'End time'];

        onMounted(() => {
            if (props.isRange && props.timeRange && props.timeRange.length === 2) {
                timeRangeValue.value = [
                    dayjs(props.timeRange[0], props.format),
                    dayjs(props.timeRange[1], props.format)
                ];
            } else if (!props.isRange && props.time) {
                timeValue.value = dayjs(props.time, props.format);
            }
        });

        const timeChanged = (newValue) => {
            const emitValue = newValue
                ? newValue.format(props.format)
                : undefined;
            emit("timeChanged", emitValue);
        };

        const timeRangeChanged = (newValue) => {
            const emitValue = newValue && newValue.length === 2
                ? [
                    newValue[0].format(props.format),
                    newValue[1].format(props.format)
                ]
                : [];
            emit("timeRangeChanged", emitValue);
        };

        // Expose a computed disabledTime function for the template to use
        const computedDisabledTime = (type) => {
            return mergedDisabledTime(type);
        };

        watch(
            () => props.time,
            (newValue) => {
                if (!props.isRange) {
                    if (newValue) {
                        timeValue.value = dayjs(newValue, props.format);
                    } else {
                        timeValue.value = undefined;
                    }
                }
            }
        );

        watch(
            () => props.timeRange,
            (newValue) => {
                if (props.isRange) {
                    if (newValue && newValue.length === 2) {
                        timeRangeValue.value = [
                            dayjs(newValue[0], props.format),
                            dayjs(newValue[1], props.format)
                        ];
                    } else {
                        timeRangeValue.value = [];
                    }
                }
            }
        );

        return {
            timeValue,
            timeRangeValue,
            timeChanged,
            timeRangeChanged,
            rangePlaceholder,
            appSetting,
            computedDisabledTime,
        };
    },
});
</script>
