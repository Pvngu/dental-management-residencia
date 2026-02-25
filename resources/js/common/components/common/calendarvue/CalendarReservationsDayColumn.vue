<template>
    <div
        class="flex-auto border-r border-gray-200 flex flex-col"
        :class="viewMode === 'week' ? 'min-w-[150px]' : 'min-w-[200px]'"
    >
        <!-- Appointment Slots -->
        <div class="flex-1 relative bg-white">
            <CalendarReservationsSlot
                v-for="time in timeSlots"
                :key="columnSlotKey(time)"
                :time="time"
                :dentistId="columnDentistId"
                :appointments="appointmentSlots[columnSlotKey(time)] || []"
                :isBreak="isDoctorOnBreak(columnDentistId, time, columnDateStr)"
                :isUncleared="false"
                :isUnavailable="
                    isSlotUnavailable(columnDentistId, time, columnDateStr)
                "
                :isHoliday="isDoctorOnHoliday(columnDentistId, columnDateStr)"
                :isOccupied="
                    isSlotOccupied(columnDentistId, time, columnDateStr)
                "
                :isHourly="isHourly(time)"
                :draggedAppointmentId="draggedAppointmentId"
                @click="
                    ($event) =>
                        $emit(
                            'slot-click',
                            columnDentistId,
                            time,
                            $event,
                            columnDateStr,
                        )
                "
                @add-click="
                    ($event) =>
                        $emit(
                            'slot-click',
                            columnDentistId,
                            time,
                            $event,
                            columnDateStr,
                        )
                "
                @appointment-click="(apt) => $emit('appointment-click', apt)"
                @appointment-move="
                    (payload) =>
                        $emit(
                            'appointment-move',
                            payload.evt,
                            columnDentistId,
                            payload.time,
                            columnDateStr,
                        )
                "
                @drag-start="$emit('drag-start', $event)"
                @drag-end="$emit('drag-end')"
                @drag-move="$emit('drag-move', $event)"
                @resize-start="$emit('resize-start', $event)"
                @resize-end="
                    ($event) =>
                        $emit('resize-end', {
                            ...$event,
                            dateStr: columnDateStr,
                        })
                "
                :resizingAppointmentId="resizingAppointmentId"
                :isPopoverOpen="isPopoverOpen"
                :isOutsideSchedule="isOutsideSchedule(time, columnDateStr)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import moment from "moment";
import CalendarReservationsSlot from "./CalendarReservationsSlot.vue";

const props = defineProps({
    viewMode: { type: String, default: "day" },
    dentist: { type: Object, default: null },
    weekDay: { type: Object, default: null },
    timeSlots: { type: Array, required: true },
    currentDate: { type: Object, required: true }, // Moment object
    appointmentSlots: { type: Object, required: true },
    appointments: { type: Array, required: true }, // Needed for count and helpers
    doctorHolidays: { type: Array, default: () => [] },
    // Helpers passed as props or imported?
    // It's cleaner to keep logic pure/utils but specific business logic is in parent.
    // For now we will accept helper functions as props or inject them, OR implement them here.
    // Implementing them here requires access to specific complex data structures.
    isDoctorOnBreak: { type: Function, required: true },
    isSlotUnavailable: { type: Function, required: true },
    isDoctorOnHoliday: { type: Function, required: true },
    isSlotOccupied: { type: Function, required: true },
    isHourly: { type: Function, required: true },
    draggedAppointmentId: { type: [String, Number], default: null },
    resizingAppointmentId: { type: [String, Number], default: null },
    isPopoverOpen: { type: Boolean, default: false },
    isOutsideSchedule: { type: Function, required: true },
});

defineEmits([
    "toggle-menu",
    "slot-click",
    "appointment-click",
    "appointment-move",
    "drag-start",
    "drag-end",
    "drag-move",
    "resize-start",
    "resize-end",
]);

const columnDentistId = computed(() =>
    props.viewMode === "day" ? props.dentist?.xid : "week-view",
);

const columnDateStr = computed(() => {
    return props.viewMode === "day"
        ? props.currentDate.format("YYYY-MM-DD")
        : props.weekDay?.date;
});

const columnSlotKey = (time) => {
    return props.viewMode === "day"
        ? `${props.dentist?.xid}-${time}`
        : `${props.weekDay?.date}-${time}`;
};

const isToday = computed(() => {
    if (props.viewMode === "week") {
        return props.weekDay?.date === moment().format("YYYY-MM-DD");
    }
    return false;
});

const getDentistAppointmentCount = (dentistId) => {
    if (!dentistId) return 0;
    const dateStr = props.currentDate.format("YYYY-MM-DD");
    return props.appointments.filter(
        (apt) =>
            apt.dentist_id === dentistId && apt.appointment_date === dateStr,
    ).length;
};
</script>
