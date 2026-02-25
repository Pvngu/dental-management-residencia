<template>
    <div
        class="w-full bg-white relative p-4 lg:p-6 overflow-y-auto max-h-[calc(100vh-130px)] min-h-[500px]"
    >
        <div
            v-if="Object.keys(groupedAppointments).length === 0"
            class="text-center text-gray-400 py-10 mt-10"
        >
            <CalendarOutlined class="text-4xl mb-4 text-gray-300" />
            <p>No appointments or events scheduled.</p>
        </div>

        <div v-else class="max-w-4xl mx-auto flex flex-col gap-6">
            <div
                v-for="(dayAppointments, dateKey) in groupedAppointments"
                :key="dateKey"
                class="flex flex-col md:flex-row border-b border-gray-100 pb-4 mb-2"
            >
                <!-- Day Header (Left Sidebar) -->
                <div class="md:w-32 flex-shrink-0 mb-4 md:mb-0">
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl font-semibold text-gray-800">{{
                            getDayDate(dateKey)
                        }}</span>
                        <span class="text-sm font-medium text-gray-600">{{
                            getDayMonthName(dateKey)
                        }}</span>
                    </div>
                </div>

                <!-- Appointments List -->
                <div class="flex-1 flex flex-col gap-2 relative">
                    <div
                        v-for="apt in dayAppointments"
                        :key="apt.id"
                        class="group flex flex-col sm:flex-row sm:items-center py-2 px-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors relative"
                        @click="$emit('appointment-click', apt)"
                    >
                        <!-- Time Range -->
                        <div
                            class="w-36 flex-shrink-0 text-sm font-medium text-gray-500 mb-2 sm:mb-0"
                        >
                            {{ formatTime(apt.start_time) }} &ndash;
                            {{ formatTime(apt.end_time) }}
                        </div>

                        <!-- Main Info -->
                        <div class="flex-1 flex items-center gap-3 min-w-0">
                            <!-- Color Dot -->
                            <div
                                class="w-3.5 h-3.5 rounded-full flex-shrink-0"
                                :style="{
                                    backgroundColor: apt.color || '#039be5',
                                }"
                            ></div>

                            <div class="min-w-0 flex flex-col">
                                <!-- Title / Patient -->
                                <div
                                    class="text-sm font-semibold text-gray-800 truncate"
                                    :class="{
                                        'line-through text-gray-400':
                                            apt.status === 'canceled',
                                    }"
                                >
                                    {{ getAppointmentTitle(apt) }}
                                </div>

                                <!-- Meta data (Duration, label) -->
                                <div
                                    class="text-xs text-gray-500 truncate flex items-center gap-1"
                                >
                                    {{ getAppointmentDuration(apt) }}
                                    <span
                                        v-if="apt.treatment_type"
                                        class="px-1.5 py-0.5 rounded bg-gray-100 text-[10px] text-gray-600 font-medium"
                                    >
                                        {{ apt.treatment_type.name }}
                                    </span>
                                    <span
                                        v-if="apt.type === 'event'"
                                        class="px-1.5 py-0.5 rounded bg-blue-50 border border-blue-100 text-[10px] text-blue-600 font-medium"
                                    >
                                        Event
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Doctor Info -->
                        <div
                            class="mt-2 sm:mt-0 sm:w-48 flex-shrink-0 flex items-center gap-2"
                        >
                            <a-avatar
                                :size="24"
                                :src="getDoctorInfo(apt)?.image"
                                class="bg-gray-200 text-gray-600 text-[10px] flex items-center justify-center font-bold"
                            >
                                {{ getDoctorInfo(apt)?.name?.charAt(0) || "D" }}
                            </a-avatar>
                            <span
                                class="text-sm font-medium text-gray-700 truncate"
                            >
                                {{ getDoctorInfo(apt)?.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Empty State for Day -->
                    <div
                        v-if="dayAppointments.length === 0"
                        class="py-2 px-3 text-sm text-gray-400 italic"
                    >
                        No appointments today
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
import { CalendarOutlined } from "@ant-design/icons-vue";

const props = defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
    dentists: {
        type: Array,
        default: () => [],
    },
    currentDate: {
        type: Object, // moment
        required: true,
    },
});

const emit = defineEmits(["appointment-click"]);
const { t } = useI18n();

const TIME_FORMAT_DISPLAY = "hh:mm A";
const DATE_FORMAT_DB = "YYYY-MM-DD";

// Sort appointments by date and then by start time, then group by date
const groupedAppointments = computed(() => {
    // 1. Sort all appointments
    const sorted = [...props.appointments].sort((a, b) => {
        const dateA = moment(a.appointment_date, DATE_FORMAT_DB);
        const dateB = moment(b.appointment_date, DATE_FORMAT_DB);
        if (dateA.isBefore(dateB)) return -1;
        if (dateA.isAfter(dateB)) return 1;

        // Same date, sort by start time
        const timeA = moment(a.start_time, TIME_FORMAT_DISPLAY);
        const timeB = moment(b.start_time, TIME_FORMAT_DISPLAY);
        if (timeA.isBefore(timeB)) return -1;
        if (timeA.isAfter(timeB)) return 1;
        return 0;
    });

    // 2. Group by date string
    const groups = {};
    sorted.forEach((apt) => {
        const key = apt.appointment_date;
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(apt);
    });

    return groups;
});

// Formatting Helpers
const getDayDate = (dateStr) => {
    return moment(dateStr, DATE_FORMAT_DB).format("DD"); // e.g., "17"
};

const getDayMonthName = (dateStr) => {
    return moment(dateStr, DATE_FORMAT_DB).format("MMM, ddd"); // e.g., "Feb, Tue"
};

const formatTime = (timeStr) => {
    return moment(timeStr, TIME_FORMAT_DISPLAY).format("h:mm A"); // e.g., "12:00 AM"
};

const getAppointmentTitle = (apt) => {
    if (apt.type === "event") {
        return apt.title || t("calendar.new_event");
    }
    return apt.patient_name || t("appointments.new_appointment");
};

const getAppointmentDuration = (apt) => {
    if (apt.duration) {
        if (apt.duration >= 60 && apt.duration % 60 === 0) {
            return `${apt.duration / 60} Hour${apt.duration / 60 > 1 ? "s" : ""}`;
        }
        return `${apt.duration} Minutes`;
    }
    // Fallback if no duration prop
    const start = moment(apt.start_time, TIME_FORMAT_DISPLAY);
    const end = moment(apt.end_time, TIME_FORMAT_DISPLAY);
    const diffMins = end.diff(start, "minutes");
    if (diffMins >= 60 && diffMins % 60 === 0) {
        return `${diffMins / 60} Hour${diffMins / 60 > 1 ? "s" : ""}`;
    }
    return `${diffMins} Minutes`;
};

const getDoctorInfo = (apt) => {
    const docId = apt.doctor_id || apt.dentist_id;
    const doc = props.dentists.find((d) => d.xid === docId);
    return doc || null;
};
</script>
