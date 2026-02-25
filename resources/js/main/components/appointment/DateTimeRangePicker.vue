<template>
    <div
        class="flex flex-col sm:flex-row items-center gap-2 mb-4 whitespace-nowrap"
    >
        <!-- Date Picker -->
        <a-date-picker
            :value="dateValue"
            @change="handleDateChange"
            format="ddd DD MMM"
            :allowClear="false"
            class="w-full sm:w-[130px] !rounded-full !px-3 font-medium text-gray-800"
            :dropdownClassName="'rounded-lg shadow-lg'"
            :getPopupContainer="(triggerNode) => triggerNode.parentNode"
        >
            <template #suffixIcon></template>
        </a-date-picker>

        <!-- Start Time -->
        <a-time-picker
            :value="startTimeValue"
            @change="handleStartTimeChange"
            format="h:mm a"
            :minute-step="15"
            :use12Hours="true"
            :allowClear="false"
            class="w-full sm:w-[100px] !rounded-full !px-3 !bg-gray-50 border-transparent hover:!bg-gray-100 font-medium text-gray-800"
            :getPopupContainer="(triggerNode) => triggerNode.parentNode"
        >
            <template #suffixIcon></template>
        </a-time-picker>

        <span class="text-gray-400 font-medium px-1">-</span>

        <!-- End Time -->
        <a-time-picker
            :value="endTimeValue"
            @change="handleEndTimeChange"
            format="h:mm a"
            :minute-step="15"
            :use12Hours="true"
            :allowClear="false"
            class="w-full sm:w-[100px] !rounded-full !px-3 border-transparent hover:shadow-sm font-medium text-gray-600 transition-all"
            :getPopupContainer="(triggerNode) => triggerNode.parentNode"
        >
            <template #suffixIcon></template>
        </a-time-picker>

        <span v-if="showEndDate" class="text-gray-400 font-medium px-2 text-sm">
            {{ endDateFormatted }}
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue";
import dayjs from "dayjs";

const props = defineProps({
    date: {
        type: [String, Object], // YYYY-MM-DD or Dayjs object
        default: null,
    },
    startTime: {
        type: [String, Object], // HH:mm or Dayjs object
        default: null,
    },
    endTime: {
        type: [String, Object], // HH:mm or Dayjs object
        default: null,
    },
    endDate: {
        type: [String, Object], // YYYY-MM-DD or Dayjs object
        default: null,
    },
});

const emit = defineEmits(["update:date", "update:startTime", "update:endTime"]);

// Computed properties to handle Dayjs conversions
const dateValue = computed(() => {
    if (!props.date) return null;
    return dayjs.isDayjs(props.date) ? props.date : dayjs(props.date);
});

const startTimeValue = computed(() => {
    if (!props.startTime) return null;
    // We only care about the time part, so we attach it to an arbitrary date
    return dayjs.isDayjs(props.startTime)
        ? props.startTime
        : dayjs(`2000-01-01 ${props.startTime}`);
});

const endTimeValue = computed(() => {
    if (!props.endTime) return null;
    return dayjs.isDayjs(props.endTime)
        ? props.endTime
        : dayjs(`2000-01-01 ${props.endTime}`);
});

const showEndDate = computed(() => {
    if (!props.date || !props.endDate) return false;
    const start = dayjs.isDayjs(props.date) ? props.date : dayjs(props.date);
    const end = dayjs.isDayjs(props.endDate)
        ? props.endDate
        : dayjs(props.endDate);
    return !start.isSame(end, "day");
});

const endDateFormatted = computed(() => {
    if (!props.endDate) return "";
    const end = dayjs.isDayjs(props.endDate)
        ? props.endDate
        : dayjs(props.endDate);
    return end.format("ddd DD MMM YYYY");
});

// Handlers
const handleDateChange = (val) => {
    if (val) {
        emit("update:date", val.format("YYYY-MM-DD"));
    }
};

const handleStartTimeChange = (val) => {
    if (val) {
        emit("update:startTime", val.format("HH:mm"));
    }
};

const handleEndTimeChange = (val) => {
    if (val) {
        emit("update:endTime", val.format("HH:mm"));
    }
};
</script>

<style scoped>
/* Scoped styles to hide the default Ant Design picker icons to match the mockup */
:deep(.ant-picker-input > input) {
    cursor: pointer;
    text-align: center;
}
:deep(.ant-picker) {
    padding-right: 11px; /* Adjust padding since icon is gone */
}
</style>
