<template>
    <div
        class="calendar-month-view w-full h-full bg-white flex flex-col relative max-h-[calc(100vh-130px)] overflow-hidden"
    >
        <!-- Weekdays Header -->
        <div class="grid grid-cols-7 border-b border-gray-200 bg-white">
            <div
                v-for="(day, index) in weekDays"
                :key="index"
                class="py-2 text-center text-sm font-semibold text-gray-500 capitalize border-r border-gray-100 last:border-r-0"
            >
                {{ day }}
            </div>
        </div>

        <!-- Month Grid -->
        <div
            class="flex-1 overflow-y-auto grid grid-cols-7 auto-rows-fr bg-gray-100 gap-[1px]"
        >
            <div
                v-for="(dayObj, index) in calendarDays"
                :key="index"
                class="bg-white min-h-[120px] p-2 hover:bg-gray-50 transition-colors flex flex-col relative"
                :class="{
                    'opacity-50': !dayObj.isCurrentMonth,
                    'bg-blue-50/30': dayObj.isToday,
                }"
                @click="onDayClick(dayObj.date, $event)"
            >
                <!-- Date Header -->
                <div class="flex items-center justify-between mb-1">
                    <span
                        class="text-sm font-medium w-6 h-6 flex items-center justify-center rounded-full"
                        :class="
                            dayObj.isToday
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-700'
                        "
                    >
                        {{ dayObj.date.format("D") }}
                        <!-- Show month name for the 1st of the month -->
                        <span
                            v-if="dayObj.date.format('D') === '1'"
                            class="ml-1 text-xs"
                        >
                            {{ dayObj.date.format("MMMM") }}
                        </span>
                    </span>
                </div>

                <!-- Appointments List -->
                <div class="flex-1 overflow-hidden pointer-events-none">
                    <div class="flex flex-col gap-1 mt-1 pointer-events-auto">
                        <template
                            v-for="(apt, aptIndex) in getAppointmentsForDay(
                                dayObj.date,
                            )"
                            :key="apt.id || aptIndex"
                        >
                            <div
                                v-if="aptIndex < maxVisibleAppointments"
                                class="text-[11px] truncate px-1.5 py-0.5 rounded flex items-center gap-1 cursor-pointer hover:opacity-80 transition-opacity"
                                :style="{
                                    backgroundColor:
                                        (apt.color || '#039be5') + '15',
                                }"
                                @click.stop="$emit('appointment-click', apt)"
                            >
                                <div
                                    class="w-2 h-2 rounded-full flex-shrink-0"
                                    :style="{
                                        backgroundColor: apt.color || '#039be5',
                                    }"
                                ></div>
                                <span
                                    class="font-medium truncate text-gray-700"
                                    :class="{
                                        'line-through opacity-50':
                                            apt.status === 'canceled',
                                    }"
                                >
                                    {{ formatTime(apt.start_time) }}
                                    {{ getAppointmentTitle(apt) }}
                                </span>
                            </div>
                        </template>

                        <!-- +X more badge -->
                        <div
                            v-if="
                                getAppointmentsForDay(dayObj.date).length >
                                maxVisibleAppointments
                            "
                            class="text-[10px] text-gray-500 font-medium px-1 cursor-pointer hover:text-gray-700 mt-0.5"
                            @click.stop="openDayView(dayObj.date)"
                        >
                            {{
                                getAppointmentsForDay(dayObj.date).length -
                                maxVisibleAppointments
                            }}
                            more
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import moment from "moment";
import { useI18n } from "vue-i18n";

const props = defineProps({
    currentDate: {
        type: Object, // moment
        required: true,
    },
    appointments: {
        type: Array,
        default: () => [],
    },
    dentists: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["appointment-click", "day-click", "change-view"]);
const { t } = useI18n();

const maxVisibleAppointments = 4;
const DATE_FORMAT_DB = "YYYY-MM-DD";
const TIME_FORMAT_DISPLAY = "hh:mm A";

// Generate days of the week (Mon-Sun or Sun-Sat based on locale)
const weekDays = computed(() => {
    const days = [];
    const startOfWeek = moment().startOf("isoWeek");
    for (let i = 0; i < 7; i++) {
        days.push(startOfWeek.clone().add(i, "days").format("ddd"));
    }
    return days;
});

// Generate 42 days (6 weeks) to fill the grid
const calendarDays = computed(() => {
    const startOfMonth = props.currentDate.clone().startOf("month");
    const startOfGrid = startOfMonth.clone().startOf("isoWeek");
    const endOfMonth = props.currentDate.clone().endOf("month");
    const endOfGrid = endOfMonth.clone().endOf("isoWeek");

    // Ensure 6 rows (42 days) are always shown for consistent height
    if (endOfGrid.diff(startOfGrid, "days") < 41) {
        endOfGrid.add(1, "week");
    }

    const days = [];
    let current = startOfGrid.clone();

    while (current.isBefore(endOfGrid) || current.isSame(endOfGrid, "day")) {
        days.push({
            date: current.clone(),
            isCurrentMonth: current.month() === props.currentDate.month(),
            isToday: current.isSame(moment(), "day"),
        });
        current.add(1, "day");
    }

    return days;
});

const getAppointmentsForDay = (dateObj) => {
    const dateStr = dateObj.format(DATE_FORMAT_DB);
    return props.appointments
        .filter((apt) => apt.appointment_date === dateStr)
        .sort((a, b) => {
            const timeA = moment(a.start_time, TIME_FORMAT_DISPLAY);
            const timeB = moment(b.start_time, TIME_FORMAT_DISPLAY);
            if (timeA.isBefore(timeB)) return -1;
            if (timeA.isAfter(timeB)) return 1;
            return 0;
        });
};

const formatTime = (timeStr) => {
    return moment(timeStr, TIME_FORMAT_DISPLAY).format("h:mm A");
};

const getAppointmentTitle = (apt) => {
    if (apt.type === "event") {
        return apt.title || t("calendar.new_event", "New Event");
    }
    return (
        apt.patient_name ||
        apt.patient?.name ||
        t("appointments.new_appointment", "Appointment")
    );
};

const onDayClick = (date, event) => {
    emit("day-click", date, event);
};

const openDayView = (date) => {
    // Parent should handle switching mode to 'day' and setting current date
    emit("change-view", { mode: "day", date });
};
</script>
