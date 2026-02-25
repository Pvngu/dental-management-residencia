<template>
    <div
        class="calendar-slot h-[30px] relative box-border group flex-none"
        :style="{
            borderRight: '1px solid #e5e7eb',
            borderTop: isHourly ? '1px solid #d1d5db' : '1px solid #f3f4f6',
            borderBottom: 'none',
        }"
        :class="[
            {
                'transition-colors duration-200': appointments.length === 0, // Disable animation if occupied
                'bg-[#fffbe6] !border-t-[#ffe58f]': isBreak,
                'bg-gray-100': isOutsideSchedule && !isUnavailable, // Out of clinic schedule
                'bg-gray-50': isUnavailable,
                'bg-[#fff2f0] !border-t-[#ffccc7]': isHoliday,
                'hover:bg-blue-50 cursor-pointer':
                    !isUnavailable &&
                    !isOccupied &&
                    !isHoliday &&
                    !isBreak &&
                    appointments.length === 0,
                'bg-transparent': isOccupied,
                'z-30':
                    appointments.length > 0 &&
                    !(isHovered && draggedAppointmentId),
                '!z-[100]': isHovered && draggedAppointmentId, // Lift only when dragging to avoid style issues
            },
        ]"
        @click="$emit('click', $event)"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
        :data-time="time"
    >
        <!-- Draggable Container for Appointments -->
        <draggable
            :list="appointments"
            :group="{ name: 'appointments', pull: 'clone', put: true }"
            :disabled="isDragDisabled"
            :filter="'.resize-handle'"
            :prevent-on-filter="false"
            item-key="id"
            class="w-full h-full relative z-10"
            :data-time="time"
            :move="onMove"
            @change="onChange"
            @start="onDragStart"
            @end="$emit('drag-end')"
            ghost-class="appointment-ghost"
            chosen-class="appointment-chosen"
            drag-class="appointment-drag"
            :animation="0"
            :force-fallback="true"
            :swap-threshold="1"
            :empty-insert-threshold="0"
            :fallback-tolerance="1"
            :fallback-on-body="true"
            :delay="200"
            :delay-on-touch-only="true"
        >
            <template #item="{ element: appointment }">
                <CalendarReservationsAppointment
                    :appointment="appointment"
                    :style="getAppointmentStyle(appointment)"
                    :class="{
                        'pointer-events-none': draggedAppointmentId,
                    }"
                    @click="$emit('appointment-click', appointment)"
                    @resize-start="$emit('resize-start', $event)"
                    @resize-end="$emit('resize-end', $event)"
                />
            </template>
        </draggable>

        <!-- Empty Slot or Indicators when no appointments (overlay) -->
        <template v-if="appointments.length === 0 && !isOccupied">
            <!-- Empty Slot Time Label (Hover Only) -->
            <div
                v-if="!isUnavailable"
                v-show="
                    isHovered && !draggedAppointmentId && !resizingAppointmentId
                "
                class="absolute inset-0 flex items-center justify-center pointer-events-none z-30"
                @click.stop="$emit('add-click', $event)"
            >
                <div
                    class="bg-white border border-gray-400 rounded-sm mx-1 py-1 text-xs text-gray-800 shadow-md w-full text-center"
                >
                    {{ time }}
                </div>
            </div>

            <!-- Holiday Indicator -->
            <div
                v-else-if="isHoliday"
                class="absolute inset-0 flex items-center justify-center gap-1 pointer-events-none select-none text-red-400 text-[10px] font-medium"
            >
                <CalendarOutlined />
                <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{
                    $t("calendar.on_holiday")
                }}</span>
            </div>

            <!-- Break Time Indicator -->
            <div
                v-else-if="isBreak"
                class="absolute inset-0 flex items-center justify-center gap-1 pointer-events-none select-none text-yellow-500 text-[10px] font-medium"
            >
                <ClockCircleOutlined />
                <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{
                    $t("calendar.break_time")
                }}</span>
            </div>

            <!-- Unavailable -->
            <div
                v-else
                class="absolute inset-0 flex items-center justify-center gap-1 pointer-events-none select-none text-gray-300 text-[10px]"
            >
                <span class="whitespace-nowrap overflow-hidden text-ellipsis">{{
                    $t("calendar.not_available")
                }}</span>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { CalendarOutlined, ClockCircleOutlined } from "@ant-design/icons-vue";
import draggable from "vuedraggable";
import CalendarReservationsAppointment from "./CalendarReservationsAppointment.vue";
import moment from "moment";

const isHovered = ref(false);

const props = defineProps({
    time: { type: String, required: true },
    dentistId: { type: String, required: true },
    appointments: { type: Array, default: () => [] },
    isBreak: { type: Boolean, default: false },
    isUnavailable: { type: Boolean, default: false },
    isHoliday: { type: Boolean, default: false },
    isOccupied: { type: Boolean, default: false },
    isHourly: { type: Boolean, default: false },
    draggedAppointmentId: { type: [String, Number], default: null },
    resizingAppointmentId: { type: [String, Number], default: null },
    isPopoverOpen: { type: Boolean, default: false },
    isOutsideSchedule: { type: Boolean, default: false },
});

const emit = defineEmits([
    "click",
    "appointment-click",
    "add-click",
    "appointment-move",
    "drag-start",
    "drag-end",
    "drag-move",
    "resize-start",
    "resize-end",
]);

const isDragDisabled = computed(() => {
    // If popover is open, we disable interaction on all slots to prevent glitching
    if (props.isPopoverOpen) return true;

    // Allow dragging existing appointments even if slot is unavailable or occupied
    if (props.appointments.length > 0) return false;

    // Otherwise, disable interaction if slot is unavailable (e.g. holiday/break)
    // We now allow interaction if 'isOccupied' to support overlapping appointments
    return props.isUnavailable;
});

const onMove = (evt) => {
    emit("drag-move", evt);
    return true; // Always allow drop, even on occupied slots
};

const onDragStart = (evt) => {
    const appointment = props.appointments[evt.oldIndex];
    emit("drag-start", appointment);
};

const onChange = (evt) => {
    emit("appointment-move", {
        evt,
        dentistId: props.dentistId,
        time: props.time,
    });
};

const getAppointmentStyle = (appointment) => {
    const SLOT_HEIGHT_PX = 30;
    const TIME_FORMAT_DISPLAY = "h:mm A";

    const start = moment(appointment.start_time, TIME_FORMAT_DISPLAY);
    const end = moment(appointment.end_time, TIME_FORMAT_DISPLAY);
    // Calculate exact duration
    const duration = end.diff(start, "minutes");
    // Calculate pixels: (duration / 15min) * 30px
    // Note: If slot is 30 mins, this math might vary, but constants say 15min/30px.
    // Assuming 15 min slots.
    const height = (duration / 15) * SLOT_HEIGHT_PX - 4; // -4 for some margin

    // Dynamic Layout (Cascading / Stacked)
    const col = appointment._visual_col || 0;
    const xOffset = col * 15; // Indent 15px per overlap level

    return {
        position: "absolute",
        top: "2px",
        left: `calc(4px + ${xOffset}px)`,
        width: `calc(100% - 8px - ${xOffset}px)`,
        height: `${height}px`,
        zIndex: 10 + col,
        borderRadius: "4px",
    };
};
</script>

<style>
/* Global override for ghost element to ensure visibility on top */
.appointment-ghost {
    z-index: 99999 !important;
    opacity: 1 !important; /* Fully opaque */
    pointer-events: none !important; /* Pass events to slot */
    transform: scale(1.02) !important; /* Slight lift */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}
.pointer-events-none {
    pointer-events: none !important;
}
</style>
