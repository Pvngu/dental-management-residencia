<template>
    <div
        class="border-[1px] rounded transition-all duration-200 cursor-pointer text-[11px] overflow-hidden flex flex-col justify-start gap-[2px] w-full hover:z-20 ring-1 ring-white"
        :class="[
            appointment.is_draft
                ? 'bg-blue-50/80 border-blue-400 border-dashed opacity-80 shadow-inner'
                : appointment.type === 'event'
                  ? 'border-none p-[6px] shadow-sm'
                  : 'bg-[#e6fffa] hover:bg-[#b5f5ec] border-[#006d75] border-l-[4px] p-[6px]',
            appointment.is_draft || appointment.type === 'event'
                ? ''
                : statusClasses[appointment.status] || 'appointment-card',
            { '!transition-none': resizing },
            resizing && !appointment.is_draft && appointment.type !== 'event'
                ? hoverClasses[appointment.status] || '!bg-[#b5f5ec]'
                : '',
        ]"
        :style="computedStyle"
        @click.stop="$emit('click', appointment)"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <div class="flex justify-between items-center mb-[2px]">
            <span
                class="font-bold whitespace-nowrap overflow-hidden text-ellipsis"
                :style="
                    appointment.type === 'event'
                        ? { color: getTextColor(appointment.color) }
                        : {}
                "
                :class="{
                    'text-blue-600': appointment.is_draft,
                    'text-gray-800':
                        !appointment.is_draft && appointment.type !== 'event',
                }"
            >
                {{
                    appointment.type === "event"
                        ? appointment.patient_name
                            ? `${appointment.title} - ${appointment.patient_name}`
                            : appointment.title
                        : appointment.patient?.name || appointment.patient_name
                }}
            </span>
            <div class="flex items-center text-[10px]">
                <span
                    class="text-[10px] capitalize"
                    :style="
                        appointment.type === 'event'
                            ? {
                                  color: getTextColor(appointment.color),
                                  opacity: 0.8,
                              }
                            : {}
                    "
                    :class="appointment.type === 'event' ? '' : 'text-gray-400'"
                >
                    {{
                        appointment.type === "event"
                            ? $t("calendar.event")
                            : $t(`calendar.${appointment.status}`)
                    }}
                </span>
            </div>
        </div>
        <div
            :style="
                appointment.type === 'event'
                    ? { color: getTextColor(appointment.color), opacity: 0.9 }
                    : {}
            "
            :class="appointment.type === 'event' ? '' : 'text-gray-600'"
            class="whitespace-nowrap overflow-hidden text-ellipsis leading-tight"
        >
            {{ appointment.start_time }} • {{ appointment.end_time }}
        </div>
        <div
            v-if="!appointment.is_draft"
            :style="
                appointment.type === 'event'
                    ? { color: getTextColor(appointment.color), opacity: 0.9 }
                    : {}
            "
            :class="appointment.type === 'event' ? '' : 'text-gray-600'"
            class="whitespace-nowrap overflow-hidden text-ellipsis leading-tight"
        >
            {{ appointment.treatment_type?.name || appointment.treatment_type }}
        </div>
        <div
            v-if="appointment.status === 'waiting_payment'"
            class="text-orange-400 text-[10px] flex items-center gap-1 mt-[2px]"
        >
            <ExclamationCircleOutlined />
            {{ $t("calendar.waiting_payment_for_raman") }}
        </div>

        <!-- Resize Handle (Only for non-cancelled/finished?) -->
        <div
            v-if="
                !appointment.is_draft &&
                appointment.status !== 'canceled' &&
                appointment.status !== 'finished'
            "
            class="resize-handle absolute bottom-0 left-0 right-0 h-[8px] cursor-ns-resize z-50 hover:bg-black/10 transition-colors flex justify-center items-center group/handle"
            @pointerdown.stop.prevent="startResize"
            @mousedown.stop.prevent
            @touchstart.stop.prevent
            @click.stop
        >
            <div
                class="w-8 h-[2px] bg-gray-400/50 opacity-0 group-hover/handle:opacity-100 rounded"
            ></div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onUnmounted } from "vue";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";

const props = defineProps({
    appointment: {
        type: Object,
        required: true,
    },
    style: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(["click", "resize-start", "resize-end"]);

// Resizing & Hover Logic
const isHovered = ref(false);
const resizing = ref(false);
const currentHeight = ref(null);
let startY = 0;
let startHeight = 0;

const computedStyle = computed(() => {
    const base = { ...props.style };
    if (props.appointment.type === "event" && props.appointment.color) {
        base.backgroundColor = isHovered.value
            ? lightenDarkenColor(props.appointment.color, -20)
            : props.appointment.color;
    }
    if (currentHeight.value !== null) {
        base.height = `${currentHeight.value}px`;
        // Ensure z-index is high during resizing
        base.zIndex = 100;
        base.transition = "none !important";
    }
    return base;
});

const startResize = (e) => {
    e.stopPropagation();
    e.preventDefault();
    console.log("Resize Start", e);
    resizing.value = true;
    emit("resize-start", props.appointment);
    startY = e.clientY;

    // Use offsetHeight from the card element (parent of handle)
    // The handle is absolute positioned relative to the card, so offsetParent is the card.
    const card = e.target.offsetParent;
    startHeight = card
        ? card.offsetHeight
        : parseFloat(props.style.height) || 30;

    currentHeight.value = startHeight;

    window.addEventListener("pointermove", onResize);
    window.addEventListener("pointerup", stopResize);
};

const onResize = (e) => {
    if (!resizing.value) return;
    // console.log("Resizing...", e.clientY);
    const dy = e.clientY - startY;
    // Min height 30px (15 mins)
    currentHeight.value = Math.max(30, startHeight + dy);
};

const stopResize = () => {
    if (!resizing.value) return;
    console.log("Resize Stop");
    resizing.value = false;
    window.removeEventListener("pointermove", onResize);
    window.removeEventListener("pointerup", stopResize);

    // Calculate new duration
    // Assuming 30px = 15 mins => 2px = 1 min
    const minutes = Math.round(currentHeight.value / 2);
    // Snap to 15
    const newDuration = Math.max(15, Math.round(minutes / 15) * 15);

    emit("resize-end", {
        appointment: props.appointment,
        newDuration,
    });

    currentHeight.value = null;
};

const getTextColor = (hexColor) => {
    if (!hexColor) return "#ffffff";

    // Convert hex to RGB
    const hex = hexColor.replace("#", "");
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    // Calculate relative luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

    return luminance > 0.6 ? "#000000" : "#ffffff";
};

const lightenDarkenColor = (col, amt) => {
    let usePound = false;
    if (col[0] === "#") {
        col = col.slice(1);
        usePound = true;
    }

    let num = parseInt(col, 16);

    let r = (num >> 16) + amt;
    if (r > 255) r = 255;
    else if (r < 0) r = 0;

    let b = ((num >> 8) & 0x00ff) + amt;
    if (b > 255) b = 255;
    else if (b < 0) b = 0;

    let g = (num & 0x0000ff) + amt;
    if (g > 255) g = 255;
    else if (g < 0) g = 0;

    return (
        (usePound ? "#" : "") +
        (g | (b << 8) | (r << 16)).toString(16).padStart(6, "0")
    );
};

onUnmounted(() => {
    window.removeEventListener("pointermove", onResize);
    window.removeEventListener("pointerup", stopResize);
});

const statusClasses = {
    registered: "border-[#006d75] bg-[#e6fffa] hover:bg-[#b5f5ec]",
    waiting_payment: "border-orange-400 bg-yellow-50 hover:bg-yellow-100",
    finished: "border-gray-300 bg-gray-50 text-gray-400 hover:bg-gray-100",
    canceled: "border-red-500 bg-red-50 opacity-70 hover:opacity-100",
    // Default handles the new teal theme
};

const hoverClasses = {
    registered: "!bg-[#b5f5ec]",
    waiting_payment: "!bg-yellow-100",
    finished: "!bg-gray-100",
    canceled: "!opacity-100",
};
</script>
