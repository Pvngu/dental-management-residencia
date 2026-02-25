<template>
    <div class="relative border-t border-gray-200 bg-white">
        <div
            ref="scrollContainer"
            class="min-w-full overflow-x-scroll max-h-[calc(100vh-130px)] overflow-y-auto"
        >
            <div class="flex flex-col w-max min-w-full">
                <!-- Unified Sticky Header Row -->
                <div class="sticky top-0 z-[100] flex">
                    <!-- Corner GMT Header (Sticky Top & Left) -->
                    <div
                        class="sticky left-0 z-[110] bg-white border-b-2 border-r border-gray-200 flex items-center justify-center text-[10px] text-gray-400 font-medium shadow-sm transition-all flex-shrink-0"
                        :class="
                            viewMode === 'week'
                                ? 'w-[60px] h-[40px]'
                                : 'w-[60px] h-[88px]'
                        "
                    >
                        <span>{{ timezoneOffset }}</span>
                    </div>

                    <!-- Column Headers (Sync with columns below) -->
                    <div
                        class="flex flex-auto items-stretch bg-white border-b-2 border-gray-200 shadow-sm"
                    >
                        <template v-if="viewMode === 'day'">
                            <div
                                v-for="dentist in dentists"
                                :key="`header-${dentist.xid}`"
                                class="min-w-[200px] flex-auto p-3 flex justify-between items-start border-r border-gray-200"
                            >
                                <div class="flex gap-3">
                                    <a-avatar
                                        :src="dentist?.image"
                                        :alt="dentist?.name"
                                    >
                                        {{ dentist?.name?.charAt(0) }}
                                    </a-avatar>
                                    <div
                                        class="dentist-details overflow-hidden"
                                    >
                                        <h4
                                            class="m-0 text-sm font-semibold text-gray-800 leading-tight truncate"
                                        >
                                            {{ dentist?.name }}
                                        </h4>
                                        <p
                                            class="mt-1 text-[11px] text-gray-400 flex flex-col whitespace-nowrap"
                                        >
                                            <span class="mb-0.5">
                                                {{
                                                    $t(
                                                        "calendar.todays_appointment",
                                                    )
                                                }}
                                            </span>
                                            <strong class="text-blue-500">
                                                {{
                                                    getDentistAppointmentCount(
                                                        dentist?.xid,
                                                    )
                                                }}
                                                {{
                                                    $t("calendar.patient_count")
                                                }}
                                            </strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-if="viewMode === 'week'">
                            <div
                                v-for="day in weekDays"
                                :key="`header-${day.date}`"
                                class="min-w-[150px] flex-auto h-[40px] flex items-center justify-center border-r border-gray-200"
                                :class="
                                    day.date ===
                                    currentDate.format('YYYY-MM-DD')
                                        ? 'bg-blue-50 text-blue-600'
                                        : 'bg-white text-gray-800'
                                "
                            >
                                <h4
                                    class="m-0 text-sm font-semibold leading-tight capitalize text-gray-500"
                                >
                                    {{ day.label }}
                                </h4>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Content Row (Time Slots + Columns) -->
                <div class="flex relative w-max min-w-full">
                    <!-- Realtime Current Time Indicator -->
                    <div
                        v-if="isTodayVisible"
                        class="absolute left-0 right-0 z-[95] flex items-center pointer-events-none"
                        :style="{ top: currentTimeTopPosition + 'px' }"
                    >
                        <!-- Sidebar Label Background -->
                        <div
                            class="w-[60px] h-[30px] flex items-center justify-center bg-gray-50 flex-shrink-0 relative"
                        >
                            <!-- The black label box -->
                            <div
                                class="bg-gray-900 text-white text-[11px] font-bold px-1.5 py-0.5 rounded leading-none absolute z-10"
                            >
                                {{ formattedCurrentTime }}
                            </div>
                        </div>
                        <!-- The Horizontal Line -->
                        <div class="flex-auto h-[1px] bg-gray-900 relative">
                            <!-- Small dot at the start of the line, attached to the timeline -->
                            <div
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-3 bg-gray-900"
                            ></div>
                        </div>
                    </div>

                    <!-- Time Slots Sidebar (Sticky Left) -->
                    <div
                        class="w-[60px] sticky left-0 z-[90] bg-gray-50 flex-shrink-0 border-r border-gray-200"
                    >
                        <!-- Time Slots Vertical List -->
                        <div
                            v-for="(time, index) in timeSlots"
                            :key="time"
                            class="h-[30px] border-b border-gray-100 flex items-center justify-center text-[11px] text-gray-500 relative box-border"
                            :class="{
                                'bg-[#fffbe6]':
                                    viewMode === 'day' && isBreakTime(time),
                                'bg-white':
                                    viewMode === 'week' || !isBreakTime(time),
                            }"
                        >
                            <!-- Hour Label with negative margin to sit on line, except first one -->
                            <span
                                v-if="isHourly(time)"
                                class="relative px-[2px]"
                                :class="{
                                    'top-0 text-gray-800 font-medium':
                                        index === 0,
                                    '-top-[15px]': index !== 0,
                                }"
                            >
                                {{ time }}</span
                            >
                        </div>
                    </div>

                    <!-- Day/Dentist Columns -->
                    <div class="flex flex-auto items-stretch">
                        <template v-if="viewMode === 'day'">
                            <CalendarReservationsDayColumn
                                v-for="dentist in dentists"
                                :key="dentist.xid"
                                viewMode="day"
                                :dentist="dentist"
                                :timeSlots="timeSlots"
                                :currentDate="currentDate"
                                :appointmentSlots="appointmentSlots"
                                :appointments="appointments"
                                :doctorHolidays="doctorHolidays"
                                :isDoctorOnBreak="isDoctorOnBreak"
                                :isSlotUnavailable="isSlotUnavailable"
                                :isDoctorOnHoliday="isDoctorOnHoliday"
                                :isSlotOccupied="isSlotOccupied"
                                :isHourly="isHourly"
                                :draggedAppointmentId="draggedAppointmentId"
                                :resizingAppointmentId="resizingAppointmentId"
                                :isPopoverOpen="isPopoverOpen"
                                :isOutsideSchedule="isOutsideSchedule"
                                @toggle-menu="
                                    $emit('toggle-dentist-menu', $event)
                                "
                                @slot-click="
                                    (dId, time, event, dateStr) =>
                                        $emit(
                                            'slot-click',
                                            dId,
                                            time,
                                            event,
                                            dateStr,
                                        )
                                "
                                @appointment-click="
                                    (apt) => $emit('appointment-click', apt)
                                "
                                @appointment-move="
                                    (evt, dId, time, dateStr) =>
                                        $emit(
                                            'appointment-move',
                                            evt,
                                            dId,
                                            time,
                                            dateStr,
                                        )
                                "
                                @drag-start="$emit('drag-start', $event)"
                                @drag-end="$emit('drag-end')"
                                @drag-move="$emit('drag-move', $event)"
                                @resize-start="$emit('resize-start', $event)"
                                @resize-end="$emit('resize-end', $event)"
                            />
                        </template>
                        <template v-if="viewMode === 'week'">
                            <CalendarReservationsDayColumn
                                v-for="day in weekDays"
                                :key="day.date"
                                viewMode="week"
                                :weekDay="day"
                                :timeSlots="timeSlots"
                                :currentDate="currentDate"
                                :appointmentSlots="appointmentSlots"
                                :appointments="appointments"
                                :doctorHolidays="doctorHolidays"
                                :isDoctorOnBreak="isDoctorOnBreak"
                                :isSlotUnavailable="isSlotUnavailable"
                                :isDoctorOnHoliday="isDoctorOnHoliday"
                                :isSlotOccupied="isSlotOccupied"
                                :isHourly="isHourly"
                                :draggedAppointmentId="draggedAppointmentId"
                                :resizingAppointmentId="resizingAppointmentId"
                                :isPopoverOpen="isPopoverOpen"
                                :isOutsideSchedule="isOutsideSchedule"
                                @slot-click="
                                    (dId, time, event, dateStr) =>
                                        $emit(
                                            'slot-click',
                                            dId,
                                            time,
                                            event,
                                            dateStr,
                                        )
                                "
                                @appointment-click="
                                    (apt) => $emit('appointment-click', apt)
                                "
                                @appointment-move="
                                    (evt, dId, time, dateStr) =>
                                        $emit(
                                            'appointment-move',
                                            evt,
                                            dId,
                                            time,
                                            dateStr,
                                        )
                                "
                                @drag-start="$emit('drag-start', $event)"
                                @drag-end="$emit('drag-end')"
                                @drag-move="$emit('drag-move', $event)"
                                @resize-start="$emit('resize-start', $event)"
                                @resize-end="$emit('resize-end', $event)"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from "vue";
import moment from "moment";
import CalendarReservationsDayColumn from "./CalendarReservationsDayColumn.vue";

const scrollContainer = ref(null);

const props = defineProps({
    viewMode: { type: String, default: "day" },
    weekDays: { type: Array, default: () => [] },
    timeSlots: Array,
    dentists: Array,
    currentDate: Object,
    appointmentSlots: Object,
    appointments: Array,
    doctorHolidays: Array,
    timezoneOffset: String,
    draggedAppointmentId: { type: [String, Number], default: null },
    resizingAppointmentId: { type: [String, Number], default: null },
    isPopoverOpen: { type: Boolean, default: false },
    isOutsideSchedule: { type: Function, required: true },
    // Helpers
    isBreakTime: Function,
    isDoctorOnBreak: Function,
    isSlotUnavailable: Function,
    isDoctorOnHoliday: Function,
    isSlotOccupied: Function,
    isHourly: Function,
});

defineEmits([
    "toggle-dentist-menu",
    "slot-click",
    "appointment-click",
    "appointment-move",
    "drag-start",
    "drag-end",
    "drag-move",
    "resize-start",
    "resize-end",
]);

const getDentistAppointmentCount = (dentistId) => {
    let count = 0;
    Object.keys(props.appointmentSlots).forEach((key) => {
        if (key.startsWith(`${dentistId}-`)) {
            count += props.appointmentSlots[key].length;
        }
    });
    return count;
};

const scrollToClinicOpen = () => {
    nextTick(() => {
        if (!scrollContainer.value) return;

        const firstActiveIndex = props.timeSlots.findIndex(
            (t) => !props.isOutsideSchedule(t),
        );

        if (firstActiveIndex > -1) {
            // Scroll to the exact slot position (each slot is 30px high)
            // We subtract 1 so that there's a small padding above it if > 0
            const offsetIndex = Math.max(0, firstActiveIndex - 1);
            scrollContainer.value.scrollTo({
                top: offsetIndex * 30,
                behavior: "smooth",
            });
        }
    });
};

// --- Realtime Indicator Logic ---
const currentTime = ref(moment());
let timeInterval = null;

const startRealtimeInterval = () => {
    // Update every minute, on the minute
    const msUntilNextMinute = (60 - moment().seconds()) * 1000;
    setTimeout(() => {
        currentTime.value = moment();
        timeInterval = setInterval(() => {
            currentTime.value = moment();
        }, 60000);
    }, msUntilNextMinute);
};

const isTodayVisible = computed(() => {
    const todayStr = moment().format("YYYY-MM-DD");
    if (props.viewMode === "day") {
        return props.currentDate.format("YYYY-MM-DD") === todayStr;
    } else {
        return props.weekDays.some((day) => day.date === todayStr);
    }
});

const formattedCurrentTime = computed(() => {
    return currentTime.value.format("h:mm");
});

const currentTimeTopPosition = computed(() => {
    // Top position corresponds exactly to where this minute falls in the slots
    // 1 hour = 4 slots = 120 pixels (since each slot is 30px high)
    // 1 minute = 120 / 60 = 2 pixels
    const hour = currentTime.value.hour();
    const minute = currentTime.value.minute();
    return hour * 120 + minute * 2;
});

watch(
    [() => props.timeSlots, () => props.dentists, () => props.weekDays],
    scrollToClinicOpen,
);

onMounted(() => {
    scrollToClinicOpen();
    startRealtimeInterval();
});

onUnmounted(() => {
    if (timeInterval) clearInterval(timeInterval);
});
</script>
