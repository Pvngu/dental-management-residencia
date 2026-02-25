import { ref, reactive } from "vue";
import common from "../../../../common/composable/common";

export default function useSchedule() {
    const days = [1, 2, 3, 4, 5, 6, 7];
    const schedule = reactive(
        days.reduce((acc, day) => {
            acc[day] = {
                from: null,
                to: null,
                status: 0,
            };
            return acc;
        }, {})
    );
    const { clinicSchedules } = common();

    const isDayAvailable = (day) => {
        return clinicSchedules.value
            ? clinicSchedules.value.some((item) => item.day_of_week === day)
            : false;
    };

    const getFirstClinicSchedule = () => {
        if (clinicSchedules.value && clinicSchedules.value.length > 0) {
            return clinicSchedules.value[0];
        }
        return null;
    };

    const getClinicScheduleForDay = (day) => {
        if (!clinicSchedules.value) return null;
        return (
            clinicSchedules.value.find(
                (item) => item.day_of_week === day
            ) || null
        );
    };

    const getDisabledTimesForDay = (day) => {
        return (now) => {
            const clinicDay = getClinicScheduleForDay(day);
            if (!clinicDay) {
                return {
                    disabledHours: () => Array.from({ length: 24 }, (_, i) => i),
                    disabledMinutes: () => Array.from({ length: 60 }, (_, i) => i),
                    disabledSeconds: () => Array.from({ length: 60 }, (_, i) => i),
                };
            }
            const startHour = parseInt(clinicDay.start_time.split(":")[0]);
            const endHour = parseInt(clinicDay.end_time.split(":")[0]);
            const startMinute = parseInt(clinicDay.start_time.split(":")[1]);
            const endMinute = parseInt(clinicDay.end_time.split(":")[1]);
            return {
                disabledHours: () => {
                    const disabledHours = [];
                    for (let i = 0; i < 24; i++) {
                        if (i < startHour || i > endHour) {
                            disabledHours.push(i);
                        }
                    }
                    return disabledHours;
                },
                disabledMinutes: (selectedHour) => {
                    const disabledMinutes = [];
                    if (selectedHour === startHour) {
                        for (let i = 0; i < startMinute; i++) {
                            disabledMinutes.push(i);
                        }
                    }
                    if (selectedHour === endHour) {
                        for (let i = endMinute + 1; i < 60; i++) {
                            disabledMinutes.push(i);
                        }
                    }
                    return disabledMinutes;
                },
                disabledSeconds: () => {
                    return Array.from({ length: 60 }, (_, i) => i !== 0 ? i : null).filter(i => i !== null);
                },
            };
        };
    };

    const handleDaySwitch = (day, isChecked) => {
        if (isChecked) {
            let lastOpenDay = null;
            for (let i = day - 1; i >= 1; i--) {
                if (schedule[i] && schedule[i].status) {
                    lastOpenDay = i;
                    break;
                }
            }
            if (lastOpenDay) {
                schedule[day].from = schedule[lastOpenDay].from;
                schedule[day].to = schedule[lastOpenDay].to;
            } else {
                const clinicDay =
                    getClinicScheduleForDay(day) ||
                    getFirstClinicSchedule();
                if (clinicDay) {
                    schedule[day].from = clinicDay.start_time;
                    schedule[day].to = clinicDay.end_time;
                }
            }
        }
        schedule[day].status = isChecked ? 1 : 0;
    };

    const copyFromPreviousDay = (currentDay) => {
        const dayIndex = days.indexOf(currentDay);
        if (dayIndex > 0) {
            const previousDay = days[dayIndex - 1];
            schedule[currentDay].from = schedule[previousDay].from;
            schedule[currentDay].to = schedule[previousDay].to;
            schedule[currentDay].status = schedule[previousDay].status;
        }
    };

    const initializeSchedule = (scheduleData) => {
        if (scheduleData) {
            days.forEach((day) => {
                const src = scheduleData.find(
                    (item) => item.day_of_week == day
                );
                if (src) {
                    schedule[day].from = src.available_from ?? null;
                    schedule[day].to = src.available_to ?? null;
                    schedule[day].status = src.status ?? 0;
                } else {
                    schedule[day].from = null;
                    schedule[day].to = null;
                    schedule[day].status = 0;
                }
            });
        }
    };

    const resetSchedule = () => {
        days.forEach((day) => {
            schedule[day].from = null;
            schedule[day].to = null;
            schedule[day].status = 0;
        });
    };

    const getScheduleDataObject = () => {
        const scheduleDataObj = {};
        days.forEach((day) => {
            scheduleDataObj[day] = {
                from: schedule[day].from,
                to: schedule[day].to,
                status: schedule[day].status,
            };
        });
        return scheduleDataObj;
    };

    return {
        days,
        schedule,
        clinicSchedules,
        isDayAvailable,
        getFirstClinicSchedule,
        getClinicScheduleForDay,
        getDisabledTimesForDay,
        handleDaySwitch,
        copyFromPreviousDay,
        initializeSchedule,
        resetSchedule,
        getScheduleDataObject,
    };
}
