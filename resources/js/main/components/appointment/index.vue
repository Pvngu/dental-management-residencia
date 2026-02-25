<template>
    <a-modal
        :open="visible"
        :width="modalWidth"
        :footer="null"
        :maskClosable="false"
        :onCancel="onClose"
        centered
        class="transition-all duration-300 ease-in-out origin-left"
    >
        <div
            class="flex flex-col lg:flex-row text-gray-800 transition-all duration-300 ease-in-out"
        >
            <!-- Form Section -->
            <div
                class="flex-none lg:flex-1 bg-white p-4 sm:p-6 w-full lg:w-auto lg:min-w-[350px]"
            >
                <AppointmentFormFields
                    :formData="formData"
                    @update:formData="
                        (newData) => Object.assign(formData, newData)
                    "
                    :rules="rules"
                    :autoSelectDoctor="autoSelectDoctor"
                    :autoSelectPatient="autoSelectPatient"
                    :doctorInfo="doctorInfo"
                    :roomsData="rooms"
                    :treatmentTypesData="treatmentTypes"
                    :isLoading="isLoading"
                />
            </div>
            <!-- Calendar Section -->
            <div
                class="flex-none lg:flex-1 bg-white p-4 sm:p-6 w-full lg:w-auto lg:min-w-[350px] transition-all duration-300 lg:border-l! lg:border-gray-200! lg:border-t-0! border-t! border-gray-200! relative"
                v-if="formData.doctor_id"
            >
                <!-- Loading Overlay -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-medium">
                        <span class="text-gray-800">{{ monthName }}</span>
                        <span class="text-gray-500"> {{ currentYear }}</span>
                    </h2>
                    <div class="flex gap-4">
                        <button
                            @click="handlePrevMonth"
                            class="p-2 rounded-full hover:bg-gray-100"
                            :disabled="isPrevMonthDisabled"
                        >
                            <LeftOutlined />
                        </button>
                        <button
                            @click="handleNextMonth"
                            class="p-2 rounded-full hover:bg-gray-100"
                        >
                            <RightOutlined />
                        </button>
                    </div>
                </div>
                <!-- Days of week -->
                <transition
                    mode="out-in"
                    appear
                    enter-active-class="transition-opacity duration-300"
                    leave-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div class="grid grid-cols-7 mb-4">
                        <div
                            v-for="day in days"
                            :key="day"
                            class="text-center font-medium text-gray-500"
                        >
                            {{ day }}
                        </div>
                    </div>
                </transition>

                <a-spin :spinning="isCalendarLoading">
                    <!-- Calendar grid -->
                    <div class="grid grid-cols-7 gap-2">
                        <!-- Empty cells for days before the 1st of the month -->
                        <div
                            v-for="index in firstDayOfMonth"
                            :key="`empty-${index}`"
                            class="h-14"
                        ></div>

                        <!-- Calendar days -->
                        <div
                            v-for="date in daysInMonth"
                            :key="date"
                            :class="[
                                'h-14 flex items-center justify-center rounded-lg relative',
                                isDateInPast(date) || isDateFullyBooked(date)
                                    ? 'cursor-not-allowed text-gray-300 bg-gray-50'
                                    : isAvailable(date)
                                      ? 'cursor-pointer'
                                      : 'cursor-default text-gray-300',
                                selectedDate === date
                                    ? 'bg-primary text-white'
                                    : !isDateInPast(date) &&
                                        !isDateFullyBooked(date) &&
                                        isAvailable(date)
                                      ? 'bg-gray-100 hover:bg-gray-200'
                                      : 'bg-transparent',
                            ]"
                            @click="handleDateClick(date)"
                        >
                            <span class="text-xl">{{ date }}</span>
                            <div
                                v-if="
                                    date === today &&
                                    currentMonth === currentDate.getMonth() &&
                                    currentYear === currentDate.getFullYear()
                                "
                                class="absolute bottom-2 w-1 h-1 rounded-full bg-primary"
                            ></div>

                            <!-- Show indicators for booking status -->
                            <div
                                v-if="isDateFullyBooked(date)"
                                class="absolute top-2 right-2 w-1 h-1 rounded-full bg-red-500"
                                title="Fully booked"
                            ></div>
                            <div
                                v-else-if="hasBookings(date)"
                                class="absolute top-2 right-2 w-1 h-1 rounded-full bg-yellow-500"
                                title="Partially booked"
                            ></div>
                            <div
                                v-else-if="
                                    isAvailable(date) && !isDateInPast(date)
                                "
                                class="absolute top-2 right-2 w-1 h-1 rounded-full bg-green-500"
                                title="Available"
                            ></div>
                        </div>
                    </div>
                </a-spin>

                <!-- Legend -->
                <div
                    class="flex items-center justify-end mt-4 text-sm text-gray-600 gap-4"
                >
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <span>Available</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <span>Partially booked</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span>Fully booked</span>
                    </div>
                </div>
            </div>

            <!-- Time slots section -->
            <div
                v-if="formData.doctor_id && selectedDate"
                class="flex-none w-full lg:w-80 bg-white p-4 sm:p-6 lg:border-l! lg:border-gray-200! lg:border-t-0! border-t! border-gray-200! relative"
            >
                <!-- Loading Overlay for Time Slots -->
                <div
                    v-if="isCalendarLoading"
                    class="absolute inset-0 bg-white bg-opacity-70 z-10 flex items-center justify-center"
                >
                    <div class="flex flex-col items-center">
                        <div
                            class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin mb-2"
                        ></div>
                        <p class="text-sm text-gray-600">
                            Loading time slots...
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-medium">
                        <span class="capitalize mr-2">{{
                            getDayOfWeek(selectedDate).slice(0, 3).toLowerCase()
                        }}</span>
                        <span class="text-gray-500">
                            {{ String(selectedDate).padStart(2, "0") }}</span
                        >
                    </h2>
                    <div class="flex bg-gray-100 rounded-full mb-3">
                        <button
                            :class="[
                                'px-3 py-1 rounded-full text-sm',
                                timeFormat === '12h'
                                    ? 'bg-primary text-white!'
                                    : 'bg-transparent',
                            ]"
                            @click="timeFormat = '12h'"
                        >
                            12h
                        </button>
                        <button
                            :class="[
                                'px-3 py-1 rounded-full text-sm',
                                timeFormat === '24h'
                                    ? 'bg-primary text-white!'
                                    : 'bg-transparent',
                            ]"
                            @click="timeFormat = '24h'"
                        >
                            24h
                        </button>
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="overflow-y-auto max-h-86 pr-1">
                        <button
                            v-for="(slot, index) in getTimeSlotsForDate(
                                selectedDate,
                            )"
                            :key="slot"
                            :class="[
                                'w-full py-4 rounded-lg border transition-colors',
                                isSlotBooked(selectedDate, slot)
                                    ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                                    : selectedTimeSlot === slot
                                      ? 'bg-primary text-white! border-primary'
                                      : 'border-gray-200 hover:bg-gray-100',
                                { 'opacity-50': isCalendarLoading },
                            ]"
                            @click="selectTimeSlot(slot)"
                            :disabled="isSlotBooked(selectedDate, slot)"
                        >
                            {{
                                timeFormat === "12h"
                                    ? slot
                                    : convert24hTo12h(slot)
                            }}
                            <span
                                v-if="isSlotBooked(selectedDate, slot)"
                                class="ml-2 text-sm"
                                >(Booked)</span
                            >
                        </button>
                    </div>

                    <a-button
                        type="primary"
                        :disabled="!selectedTimeSlot"
                        :loading="isSubmitting"
                        @click="submitBooking"
                        style="width: 100%"
                        block
                    >
                        <template #icon v-if="isSubmitting">
                            <a-spin size="small" />
                        </template>
                        {{
                            isSubmitting
                                ? $t("common.submitting")
                                : $t("common.submit")
                        }}
                    </a-button>
                </div>
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { Modal } from "ant-design-vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import UserSelect from "../../../common/components/common/select/UserSelect.vue";
import {
    RightOutlined,
    LeftOutlined,
    DownOutlined,
} from "@ant-design/icons-vue";
import { debounce } from "lodash-es";
import { useI18n } from "vue-i18n";
import AppointmentFormFields from "./AppointmentFormFields.vue";

const emit = defineEmits(["closed", "addEditSuccess"]);

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    formData: {
        type: Object,
        default: () => ({}),
    },
    data: {
        type: Object,
        default: () => ({}),
    },
    url: {
        type: String,
        default: "",
    },
    addEditType: {
        type: String,
        default: "add",
    },
    pageTitle: {
        type: String,
        default: "",
    },
    successMessage: {
        type: String,
        default: "",
    },
    rooms: {
        type: Array,
        default: () => [],
    },
    treatmentTypes: {
        type: Array,
        default: () => [],
    },
    autoSelectPatient: {
        type: Boolean,
        default: false,
    },
    patientId: {
        type: String,
        default: "",
    },
    autoSelectDoctor: {
        type: Boolean,
        default: false,
    },
    doctorId: {
        type: String,
        default: "",
    },
    doctorInfo: {
        type: Object,
        default: () => ({}),
    },
});

const { addEditRequestAdmin, loading, rules } = apiAdmin();

const { t } = useI18n();

// Get current date
const currentDate = new Date();
const today = currentDate.getDate();

// Initialize with current month and year, or from formData if provided (for edit mode)
const currentMonth = ref(
    props.formData.currentMonth !== undefined &&
        props.formData.currentMonth !== null
        ? props.formData.currentMonth
        : currentDate.getMonth(),
);
const currentYear = ref(
    props.formData.currentYear !== undefined &&
        props.formData.currentYear !== null
        ? props.formData.currentYear
        : currentDate.getFullYear(),
);
// Watch for changes in formData to update currentMonth and currentYear (for edit mode)
watch(
    () => props.formData.currentMonth,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            currentMonth.value = newVal;
        }
    },
);
watch(
    () => props.formData.currentYear,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            currentYear.value = newVal;
        }
    },
);

// Set patient ID automatically when the component is visible and autoSelectPatient is true
// Also fetch rooms and treatment types if needed
watch(
    () => props.visible,
    (isVisible) => {
        if (isVisible) {
            if (props.autoSelectPatient && props.patientId) {
                props.formData.patient_id = props.patientId;
            }
            if (props.autoSelectDoctor && props.doctorId) {
                props.formData.doctor_id = props.doctorId;
            }

            // Initialization for duration display
            if (props.formData.duration) {
                let mins = parseInt(props.formData.duration);
                if (mins >= 60 && mins % 60 === 0) {
                    props.formData.duration_display = mins / 60;
                    props.formData.duration_unit = "hours";
                } else {
                    props.formData.duration_display = mins;
                    props.formData.duration_unit = "mins";
                }
            } else if (!props.formData.duration_unit) {
                // Default setup if not editing and no prior selection
                props.formData.duration_display = 30;
                props.formData.duration_unit = "mins";
            }
        }
    },
    { immediate: true },
);

// Sample data for available dates and time slots - converted to refs
const availableDates = ref([]);
const timeSlots = ref({});

// State
const selectedDate = ref(props.formData.selectedDate ?? null);
const timeFormat = ref("12h");
const selectedTimeSlot = ref(props.formData.selectedTimeSlot ?? null);
const isSubmitting = ref(false);
const isCalendarLoading = ref(false);
const isLoading = ref(false);
// Watch for changes in formData to update selectedDate and selectedTimeSlot (for edit mode)
watch(
    () => props.formData.selectedDate,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            selectedDate.value = newVal;
        }
    },
);
watch(
    () => props.formData.selectedTimeSlot,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            selectedTimeSlot.value = newVal;
        }
    },
);

// Store booked slots
const bookedSlots = ref({});

const days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];

const daysInMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value, 1).getDay();
});

const monthName = computed(() => {
    return new Date(currentYear.value, currentMonth.value).toLocaleString(
        "default",
        { month: "long" },
    );
});

// Compute modal width based on visible sections
const modalWidth = computed(() => {
    const screenWidth = window.innerWidth;

    // Mobile: full width
    if (screenWidth < 768) {
        return "95%";
    }

    // Tablet: slightly larger
    if (screenWidth < 1024) {
        return "90%";
    }

    // Desktop: dynamic based on visible sections
    let width = 600;

    // Add width for calendar when doctor is selected
    if (props.formData.doctor_id) {
        width += 500;
    }

    // Add width for time slots when a date is selected
    if (selectedDate.value) {
        width += 320;
    }

    // Cap width for smaller desktop screens
    if (screenWidth <= 1400) {
        return Math.min(width, screenWidth * 0.9) + "px";
    }

    return width + "px";
});

// Check if previous month button should be disabled
// (disable if it would navigate to a month in the past)
const isPrevMonthDisabled = computed(() => {
    const now = new Date();
    const prevMonth = currentMonth.value === 0 ? 11 : currentMonth.value - 1;
    const prevYear =
        currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value;

    return (
        prevYear < now.getFullYear() ||
        (prevYear === now.getFullYear() && prevMonth < now.getMonth())
    );
});

// Check if a date is in the past
const isDateInPast = (date) => {
    const now = new Date();

    // If viewing a past month/year, all dates are in the past
    if (
        currentYear.value < now.getFullYear() ||
        (currentYear.value === now.getFullYear() &&
            currentMonth.value < now.getMonth())
    ) {
        return true;
    }

    // If viewing current month/year, check if date is before today
    if (
        currentYear.value === now.getFullYear() &&
        currentMonth.value === now.getMonth()
    ) {
        return date < now.getDate();
    }

    // If viewing a future month/year, no dates are in the past
    return false;
};

// Check if a date is fully booked (all slots are taken)
const isDateFullyBooked = (date) => {
    // If the date has no time slots, it's not bookable
    if (!timeSlots.value[date] || timeSlots.value[date].length === 0) {
        return false;
    }

    // If the date has no bookings, it's not fully booked
    if (!bookedSlots.value[date]) {
        return false;
    }

    // Check if all time slots for this date are booked
    return bookedSlots.value[date].length >= timeSlots.value[date].length;
};

const handlePrevMonth = () => {
    availableDates.value = [];
    timeSlots.value = {};
    bookedSlots.value = {};

    if (isPrevMonthDisabled.value) return;

    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value -= 1;
    } else {
        currentMonth.value -= 1;
    }
    selectedDate.value = null;
    selectedTimeSlot.value = null;
};

const handleNextMonth = () => {
    availableDates.value = [];
    timeSlots.value = {};
    bookedSlots.value = {};

    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value += 1;
    } else {
        currentMonth.value += 1;
    }
    selectedDate.value = null;
    selectedTimeSlot.value = null;
};

const handleDateClick = (date) => {
    // Don't allow selecting dates in the past or fully booked dates
    if (isDateInPast(date) || isDateFullyBooked(date)) return;

    if (isAvailable(date)) {
        selectedDate.value = date;
        selectedTimeSlot.value = null;
    }
};

const isAvailable = (date) => {
    // A date is available if it's in the availableDates array, not in the past, and not fully booked
    return (
        availableDates.value.includes(date) &&
        !isDateInPast(date) &&
        !isDateFullyBooked(date)
    );
};

const getTimeSlotsForDate = (date) => {
    return timeSlots.value[date] || [];
};

const convert24hTo12h = (time) => {
    if (timeFormat.value === "12h") return time;

    const [hours, minutes] = time
        .replace("pm", "")
        .replace("am", "")
        .split(":");
    const hour = parseInt(hours);

    if (time.includes("pm")) {
        return `${hour === 12 ? 12 : hour + 12}:${minutes}`;
    } else {
        return `${hour === 12 ? "00" : hours}:${minutes}`;
    }
};

const convertTo24HourFormat = (time) => {
    if (!time) return "";

    // Check if time is already in 24-hour format
    if (
        !time.toLowerCase().includes("am") &&
        !time.toLowerCase().includes("pm")
    ) {
        return time;
    }

    // Extract hours, minutes, and period (am/pm)
    const isPM = time.toLowerCase().includes("pm");
    const [timePart] = time.toLowerCase().split(/am|pm/);
    const [hours, minutes] = timePart.split(":");

    // Convert hours to 24-hour format
    let hour = parseInt(hours);
    if (isPM && hour !== 12) {
        hour += 12;
    } else if (!isPM && hour === 12) {
        hour = 0;
    }

    // Format the time as H:i
    return `${hour.toString().padStart(2, "0")}:${minutes}`;
};

const getDayOfWeek = (date) => {
    const dayIndex = (firstDayOfMonth.value + date - 1) % 7;
    return days[dayIndex];
};

// Booking functionality
const selectTimeSlot = (slot) => {
    if (!isSlotBooked(selectedDate.value, slot)) {
        selectedTimeSlot.value = slot;
    }
};

const isSlotBooked = (date, slot) => {
    return bookedSlots.value[date] && bookedSlots.value[date].includes(slot);
};

const hasBookings = (date) => {
    return bookedSlots.value[date] && bookedSlots.value[date].length > 0;
};

const resetData = () => {
    // Only reset doctor_id if not using autoSelectDoctor
    if (!props.autoSelectDoctor) {
        props.formData.doctor_id = null;
    } else if (props.doctorId) {
        // If using autoSelectDoctor, reset to the provided doctor ID
        props.formData.doctor_id = props.doctorId;
    }
    // Only reset patient_id if not using autoSelectPatient
    if (!props.autoSelectPatient) {
        props.formData.patient_id = null;
    } else if (props.patientId) {
        // If using autoSelectPatient, reset to the provided patient ID
        props.formData.patient_id = props.patientId;
    }
    props.formData.selectedDate = null;
    props.formData.selectedTimeSlot = null;
    selectedDate.value = null;
    selectedTimeSlot.value = null;
    availableDates.value = [];
    timeSlots.value = {};
    bookedSlots.value = {};
    isSubmitting.value = false;
};

const submitBooking = (force = false) => {
    if (
        !props.formData.doctor_id ||
        !props.formData.patient_id ||
        !selectedDate.value ||
        !selectedTimeSlot.value
    ) {
        return;
    }

    isSubmitting.value = true;

    const isEdit = !!props.formData.xid;

    const finalDuration =
        props.formData.duration_unit === "hours"
            ? parseInt(props.formData.duration_display || 1) * 60
            : parseInt(props.formData.duration_display || 30);

    const bookingData = {
        doctor_id: props.formData.doctor_id,
        patient_id: props.formData.patient_id,
        appointment_date: `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, "0")}-${String(selectedDate.value).padStart(2, "0")}`,
        appointment_time: convertTo24HourFormat(selectedTimeSlot.value),
        duration: finalDuration,
        reason_visit: props.formData.reason_visit || "",
        xid: props.formData.xid,
        _method: isEdit ? "PUT" : "POST",
        room_id: props.formData.room_id,
        treatment_type_id: props.formData.treatment_type_id,
        price: props.formData.price,
        force: force,
    };

    const url = isEdit
        ? "doctor-schedule-days/update-appointment"
        : "doctor-schedule-days/create-appointment";
    const successMessage = isEdit
        ? t("appointments.updated")
        : t("appointments.created");

    addEditRequestAdmin({
        url,
        data: bookingData,
        successMessage,
        success: () => {
            isSubmitting.value = false;
            emit("addEditSuccess");
            onClose();
        },
        error: (err) => {
            isSubmitting.value = false;

            if (err.data?.is_warning) {
                const buildWarningMessage = (errData) => {
                    if (!errData || !errData.details || !errData.details.type) {
                        return (
                            errData?.message || t("common.warning", "Warning")
                        );
                    }
                    const details = errData.details;
                    let vars = {};

                    if (details.type === "doctor_holiday") {
                        vars = { from: details.from, to: details.to };
                    } else if (details.type === "time_outside_schedule") {
                        vars = {
                            from: details.schedule_start,
                            to: details.schedule_end,
                        };
                    } else if (details.type === "doctor_break_conflict") {
                        vars = {
                            from: details.break_start,
                            to: details.break_end,
                        };
                    } else if (details.type === "appointment_conflict") {
                        vars = {
                            from: details.existing_appointment?.from,
                            to: details.existing_appointment?.to,
                            duration: details.existing_appointment?.duration,
                        };
                    }

                    return t(`appointments.warning_${details.type}`, vars);
                };

                Modal.confirm({
                    title: t("common.warning", "Warning"),
                    content: buildWarningMessage(err.data),
                    okText: t("common.proceed_anyway", "Proceed Anyway"),
                    cancelText: t("common.cancel", "Cancel"),
                    zIndex: 10000,
                    onOk: () => {
                        submitBooking(true); // Retry with force flag
                    },
                });
            }
        },
    });
};

const onClose = () => {
    resetData();
    emit("closed");
};

const getDoctorSchedule = () => {
    isCalendarLoading.value = true;
    axiosAdmin
        .get("doctor-schedule-days/monthly-available-slots", {
            params: {
                doctor_id: props.formData.doctor_id,
                month: currentMonth.value + 1, // API expects 1-based month
                year: currentYear.value,
            },
        })
        .then((res) => {
            availableDates.value = res.data.available_dates;
            timeSlots.value = res.data.time_slots;
            bookedSlots.value = res.data.booked_slots;
        })
        .catch((error) => {
            console.error("Error fetching doctor schedule:", error);
        })
        .finally(() => {
            isCalendarLoading.value = false;
        });
};

watch(
    () => props.formData.doctor_id,
    (newVal) => {
        if (newVal) {
            getDoctorSchedule();
        }
        if (!newVal) {
            selectedDate.value = null;
            selectedTimeSlot.value = null;
        }
    },
);

watch(
    () => currentMonth.value,
    debounce(() => {
        getDoctorSchedule();
    }, 300),
);

// Handle window resize for responsive behavior
const handleWindowResize = () => {
    // This will trigger recomputation of modalWidth
    // We're using a computed property, so just need to trigger a reactive update
    currentYear.value = currentYear.value; // This is just to trigger reactivity
};

// Initialize with the first available date that's not in the past and not fully booked
onMounted(() => {
    if (props.formData.doctor_id) {
        getDoctorSchedule();
    }

    // Find the first available date that's not in the past and not fully booked
    const firstAvailableDate = availableDates.value.find(
        (date) => !isDateInPast(date) && !isDateFullyBooked(date),
    );
    if (firstAvailableDate) {
        selectedDate.value = firstAvailableDate;
    } else {
        // If no available dates in current month, don't select any date
        selectedDate.value = null;
    }

    // Add window resize event listener
    window.addEventListener("resize", handleWindowResize);
});

// Clean up event listeners
onBeforeUnmount(() => {
    window.removeEventListener("resize", handleWindowResize);
});
</script>

<style>
:root {
    --primary: #1677ff;
    --primary-hover: rgba(22, 119, 255, 0.9);
    --primary-light: rgba(22, 119, 255, 0.2);
}

.bg-primary {
    background-color: var(--primary);
}

.bg-primary\/90 {
    background-color: var(--primary-hover);
}

.bg-primary\/20 {
    background-color: var(--primary-light);
}

.hover\:bg-primary\/90:hover {
    background-color: var(--primary-hover);
}

.text-primary {
    color: var(--primary);
}

.border-primary {
    border-color: var(--primary);
}

.focus\:ring-primary:focus {
    --tw-ring-color: var(--primary);
}

/* Modal width transition */
:deep(.ant-modal) {
    transition: width 0.3s ease-in-out !important;
}

:deep(.ant-modal-content) {
    transition: all 0.3s ease-in-out !important;
}

/* Custom animations */
@keyframes modal-in {
    0% {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes modal-out {
    0% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    100% {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
}

.animate-modal-in {
    animation: modal-in 0.3s forwards ease-out;
}

.animate-modal-out {
    animation: modal-out 0.2s forwards ease-in;
}

.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(22, 119, 255, 0.4);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(22, 119, 255, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(22, 119, 255, 0);
    }
}

.animate-bounce {
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%,
    100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
</style>
