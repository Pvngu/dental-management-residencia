<template>
    <div>
        <CalendarReservationsHeader
            v-model:activeTab="activeTab"
            :currentDate="currentDate"
            :formattedDate="formattedCurrentDate"
            :viewMode="viewMode"
            :totalAppointments="totalAppointments"
            :dentists="dentists"
            :selectedDentists="selectedDentists"
            :sidebarVisible="showSidebar"
            @update:viewMode="onViewModeChange"
            @update:selectedDentists="selectedDentists = $event"
            @add-reservation="addReservation"
            @go-today="goToToday"
            @previous-date="previousDate"
            @next-date="nextDate"
            @toggle-sidebar="store.dispatch('ui/toggleCalendarSidebar')"
        />

        <!-- Calendar Content -->
        <div class="calendar-content flex" v-if="activeTab === 'calendar'">
            <DoctorSidebarFilter
                :visible="showSidebar"
                :dentists="dentists"
                v-model:selectedDentists="selectedDentists"
                :singleSelect="viewMode === 'week'"
                @update:selectedDentists="fetchCalendarData()"
            />
            <div class="flex-1 overflow-x-hidden">
                <a-spin :spinning="loading" tip="Loading calendar...">
                    <!-- Calendar Grid -->
                    <CalendarReservationsGrid
                        v-if="viewMode === 'day' || viewMode === 'week'"
                        :viewMode="viewMode"
                        :weekDays="weekDays"
                        :timeSlots="timeSlots"
                        :dentists="filteredDentists"
                        :currentDate="currentDate"
                        :appointmentSlots="appointmentSlots"
                        :appointments="appointments"
                        :doctorHolidays="doctorHolidays"
                        :timezoneOffset="timezoneName"
                        :isBreakTime="isBreakTime"
                        :isDoctorOnBreak="isDoctorOnBreak"
                        :isSlotUnavailable="isSlotUnavailable"
                        :isDoctorOnHoliday="isDoctorOnHoliday"
                        :isSlotOccupied="isSlotOccupied"
                        :isHourly="isHourly"
                        :draggedAppointmentId="draggedAppointmentId"
                        :resizingAppointmentId="resizingAppointmentId"
                        :isPopoverOpen="slotAppointmentModalVisible"
                        :isOutsideSchedule="isOutsideSchedule"
                        @toggle-dentist-menu="toggleDentistMenu"
                        @slot-click="onSlotClick"
                        @appointment-click="openAppointment"
                        @appointment-move="onAppointmentMove"
                        @drag-start="onDragStart"
                        @drag-end="onDragEnd"
                        @drag-move="onDragMove"
                        @resize-start="onResizeStart"
                        @resize-end="onAppointmentResize"
                    />
                    <!-- Month Grid View -->
                    <CalendarReservationsMonth
                        v-else-if="viewMode === 'month'"
                        :currentDate="currentDate"
                        :appointments="appointments"
                        :dentists="dentists"
                        @appointment-click="openAppointment"
                        @day-click="handleMonthDayClick"
                        @change-view="handleViewChange"
                    />
                    <!-- Agenda List View -->
                    <CalendarReservationsAgenda
                        v-else-if="viewMode === 'agenda'"
                        :appointments="appointments"
                        :dentists="dentists"
                        :currentDate="currentDate"
                        @appointment-click="openAppointment"
                    />
                </a-spin>
            </div>
        </div>

        <!-- Appointment Modal -->
        <AppointmentIndex
            :visible="appointmentModalVisible"
            :formData="appointmentFormData"
            :autoSelectPatient="false"
            @closed="closeAppointmentModal"
            @addEditSuccess="onAppointmentSave"
        />
        <!-- Event Modal -->
        <EventModal
            :visible="eventModalVisible"
            :initialData="appointmentFormData"
            @closed="eventModalVisible = false"
            @addEditSuccess="onAppointmentSave"
            :autoSelectDoctor="false"
        />

        <!-- Reschedule Confirmation Modal -->
        <CalendarRescheduleModal
            v-model:visible="rescheduleModalVisible"
            :data="rescheduleData"
            :loading="isRescheduling"
            @confirm="confirmReschedule"
            @cancel="cancelReschedule"
        />

        <!-- Slot Appointment Popover -->
        <a-popover
            :open="slotAppointmentModalVisible"
            @openChange="handlePopoverOpenChange"
            trigger="click"
            placement="right"
            :destroyTooltipOnHide="false"
            :overlayInnerStyle="{
                padding: 0,
                borderRadius: '8px',
                overflow: 'hidden',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            }"
            :arrowPointAtCenter="true"
        >
            <template #content>
                <SlotAppointmentPopover
                    :visible="slotAppointmentModalVisible"
                    :formData="slotAppointmentFormData"
                    :doctorInfo="{}"
                    @closed="closeSlotAppointmentModal"
                    @addEditSuccess="onAppointmentSave"
                    @warningState="isWarningModalOpen = $event"
                />
            </template>
            <!-- Invisible Anchor Box positioned exactly over the clicked slot -->
            <div
                class="fixed pointer-events-none z-50 transition-none"
                :style="popoverAnchorStyle"
            ></div>
        </a-popover>

        <AppointmentOverview
            :visible="overviewVisible"
            :appointmentData="overviewData"
            :index="currentOverviewIndex"
            :total="overviewList.length"
            @closed="onOverviewClosed"
            @update:open="
                (val) => {
                    overviewVisible.value = val;
                }
            "
            @prev="onOverviewPrev"
            @next="onOverviewNext"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useStore } from "vuex";
import { CalendarOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import moment from "moment";
import { debounce } from "lodash-es";
import AppointmentIndex from "../../../../main/components/appointment/index.vue";
import CalendarReservationsAgenda from "./CalendarReservationsAgenda.vue";
import CalendarReservationsMonth from "./CalendarReservationsMonth.vue";
import EventModal from "../../../../main/components/appointment/EventModal.vue";
import SlotAppointmentPopover from "../../../../main/components/appointment/SlotAppointmentPopover.vue";
import AppointmentOverview from "./AppointmentOverview.vue";
import CalendarReservationsHeader from "./CalendarReservationsHeader.vue";
import CalendarReservationsGrid from "./CalendarReservationsGrid.vue";
import CalendarRescheduleModal from "./CalendarRescheduleModal.vue";
import DoctorSidebarFilter from "./DoctorSidebarFilter.vue";
import apiAdmin from "../../../composable/apiAdmin";
import common from "../../../composable/common";

const { t } = useI18n();
const { loading } = apiAdmin();
const { selectedClinicId, clinicSchedules } = common();
const store = useStore();

// Constants
const SLOT_DURATION_MINUTES = 15;
const DEFAULT_TIME_SLOTS = [
    "9am",
    "9:30am",
    "10am",
    "10:30am",
    "11am",
    "11:30am",
    "12pm",
    "12:30pm",
    "1pm",
    "1:30pm",
    "2pm",
    "2:30pm",
    "3pm",
    "3:30pm",
    "4pm",
    "4:30pm",
    "5pm",
    "5:30pm",
    "6pm",
];
const DATE_FORMAT_DB = "YYYY-MM-DD";
const TIME_FORMAT_DB = "HH:mm:ss";
const TIME_FORMAT_DISPLAY = "h:mm A";

// Reactive Data
const activeTab = ref("calendar");
const showSidebar = computed({
    get: () => store.state.ui.calendarSidebarVisible,
    set: (val) => store.dispatch("ui/setCalendarSidebarVisible", val),
});
const viewMode = computed({
    get: () => store.state.calendar.viewMode,
    set: (val) => store.dispatch("calendar/setViewMode", val),
});
const currentDate = ref(moment());
const selectedDentists = computed({
    get: () => {
        if (viewMode.value === "week") {
            const dentistId = store.state.calendar.selectedDentistWeek;
            return dentistId ? [dentistId] : [];
        }
        return store.state.calendar.selectedDentistsDay;
    },
    set: (val) => {
        if (viewMode.value === "week") {
            store.dispatch("calendar/setSelectedDentistWeek", val[0] || null);
        } else {
            store.dispatch("calendar/setSelectedDentistsDay", val);
        }
    },
});
const isInitialized = ref(false);
const timezoneOffset = ref("+07:00");
const timezoneName = ref(
    new Date()
        .toLocaleTimeString("en-us", { timeZoneName: "short" })
        .split(" ")
        .pop(),
);

// API Data
const dentists = ref([]);
const appointments = ref([]);
const doctorHolidays = ref([]); // Replaces doctorBreaks/Holidays split if needed, or keeping usage

// UI State
const appointmentModalVisible = ref(false);
const eventModalVisible = ref(false);
const appointmentFormData = ref({});
const slotAppointmentModalVisible = ref(false);
const slotAppointmentFormData = ref({});
const popoverAnchorStyle = ref({
    top: "-9999px",
    left: "-9999px",
    width: "0px",
    height: "0px",
    position: "fixed",
});
const overviewVisible = ref(false);
const overviewData = ref({});
const overviewList = ref([]);
const currentOverviewIndex = ref(0);
const appointmentSlots = ref({});
const draggedAppointmentId = ref(null);
const resizingAppointmentId = ref(null);
const isWarningModalOpen = ref(false);

const rescheduleModalVisible = ref(false);
const rescheduleData = ref(null);
const isRescheduling = ref(false);

// --- Computed Properties ---

const weekDays = computed(() => {
    if (viewMode.value !== "week") return [];

    const startOfWeek = currentDate.value.clone().startOf("isoWeek");
    const days = [];
    for (let i = 0; i < 7; i++) {
        const day = startOfWeek.clone().add(i, "days");
        days.push({
            date: day.format(DATE_FORMAT_DB),
            momentObj: day,
            label: day.format("D ddd"),
        });
    }
    return days;
});

const formattedCurrentDate = computed(() => {
    if (viewMode.value === "week" && weekDays.value.length > 0) {
        const startMonth = weekDays.value[0].momentObj.format("MMM");
        const endMonth = weekDays.value[6].momentObj.format("MMM");
        const endYear = weekDays.value[6].momentObj.format("YYYY");

        if (startMonth !== endMonth) {
            return `${startMonth} - ${endMonth} ${endYear}`;
        }
        return `${startMonth} ${endYear}`;
    }
    if (viewMode.value === "month" || viewMode.value === "agenda") {
        return currentDate.value.format("MMMM YYYY");
    }
    return currentDate.value.format("ddd, DD MMM YYYY");
});

const totalAppointments = computed(() => {
    if (viewMode.value === "week") {
        return appointments.value.length;
    }
    const dateStr = currentDate.value.format(DATE_FORMAT_DB);
    return appointments.value.filter((a) => a.appointment_date === dateStr)
        .length;
});

const filteredDentists = computed(() => {
    if (selectedDentists.value?.length > 0) {
        return dentists.value.filter((d) =>
            selectedDentists.value.includes(d.xid),
        );
    }
    return [];
});

const hasScheduleForCurrentDay = computed(() => {
    if (viewMode.value === "week") return true;
    return hasScheduleForDay(currentDate.value);
});

const currentClinicSchedule = computed(() => {
    if (!clinicSchedules.value || clinicSchedules.value.length === 0)
        return null;

    const currentDayOfWeek = currentDate.value.isoWeekday();
    const currentClinicId = selectedClinicId.value;

    let daySchedule = null;
    if (currentClinicId) {
        daySchedule = clinicSchedules.value.find(
            (s) =>
                s.day_of_week === currentDayOfWeek &&
                (s.x_clinic_id === currentClinicId ||
                    s.clinic_id === currentClinicId),
        );
    }
    // Fallback or if no clinic selected
    if (!daySchedule && !currentClinicId) {
        daySchedule = clinicSchedules.value.find(
            (s) => s.day_of_week === currentDayOfWeek,
        );
    }

    return daySchedule;
});

const timeSlots = computed(() => {
    const slots = [];
    // Start at 12:00 AM
    const startTime = moment("00:00:00", TIME_FORMAT_DB);
    // End at 11:59 PM
    const endTime = moment("23:59:59", TIME_FORMAT_DB);

    // Clone to iterate
    let current = startTime.clone();

    while (current.isBefore(endTime)) {
        const hour = current.hour();
        const minute = current.minute();
        const period = hour >= 12 ? "pm" : "am";
        const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;

        const timeStr =
            minute === 0
                ? `${displayHour}${period}`
                : `${displayHour}:${minute.toString().padStart(2, "0")}${period}`;

        slots.push(timeStr);
        current.add(SLOT_DURATION_MINUTES, "minutes");
    }

    return slots;
});

watch(
    slotAppointmentFormData,
    () => {
        if (slotAppointmentModalVisible.value) {
            updateAppointmentSlots();
        }
    },
    { deep: true },
);

watch(
    () => slotAppointmentModalVisible.value,
    (visible) => {
        if (!visible) {
            updateAppointmentSlots();
        }
    },
);

// --- Helpers ---

const isHourly = (time) => !time.includes(":");

const hasScheduleForDay = (date) => {
    if (!clinicSchedules.value || clinicSchedules.value.length === 0)
        return true;

    const dayOfWeek = date.isoWeekday();
    const currentClinicId = selectedClinicId.value;

    if (currentClinicId) {
        return clinicSchedules.value.some(
            (s) =>
                s.day_of_week === dayOfWeek &&
                (s.x_clinic_id === currentClinicId ||
                    s.clinic_id === currentClinicId),
        );
    }
    return clinicSchedules.value.some((s) => s.day_of_week === dayOfWeek);
};

const formatTimeSlot = (momentObj) => {
    const hour = momentObj.hour();
    const minute = momentObj.minute();
    const period = hour >= 12 ? "pm" : "am";
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    if (minute === 0) return `${displayHour}${period}`;
    return `${displayHour}:${minute.toString().padStart(2, "0")}${period}`;
};

const convertAppointmentTimeToSlot = (timeStr) => {
    return formatTimeSlot(moment(timeStr, TIME_FORMAT_DISPLAY));
};

const convertToTime24 = (timeSlot) => {
    const match = timeSlot.match(/^(\d+)(?::(\d+))?(am|pm)$/i);
    if (!match) return "00:00";

    let [_, h, m, p] = match;
    let hour = parseInt(h);
    const minute = m ? parseInt(m) : 0;
    const isPM = p.toLowerCase() === "pm";

    if (isPM && hour !== 12) hour += 12;
    else if (!isPM && hour === 12) hour = 0;

    return `${hour.toString().padStart(2, "0")}:${minute.toString().padStart(2, "0")}`;
};

const isDoctorOnHoliday = (doctorId, dateStr) => {
    return doctorHolidays.value.some(
        (h) => h.doctor_id === doctorId && h.date === dateStr,
    );
};

const isDoctorOnBreak = (doctorId, timeSlot, dateStr) => {
    const doctor = dentists.value.find((d) => d.xid === doctorId);
    if (!doctor?.breaks?.length) return false;

    const slotTime24 = convertToTime24(timeSlot);

    return doctor.breaks.some((b) => {
        const isApplicable = b.every_day || b.date === dateStr;
        if (!isApplicable) return false;
        return slotTime24 >= b.break_from && slotTime24 < b.break_to;
    });
};

const isBreakTime = (timeSlot, dateStr = null) => {
    const targetDateStr = dateStr || currentDate.value.format(DATE_FORMAT_DB);
    return dentists.value.some((d) =>
        isDoctorOnBreak(d.xid, timeSlot, targetDateStr),
    );
};

const isSlotUnavailable = (dentistId, timeSlot, dateStr = null) => {
    const targetDateStr = dateStr || currentDate.value.format(DATE_FORMAT_DB);
    if (!dentistId || dentistId === "week-view") return false; // Generic column

    const doctor = dentists.value.find((d) => d.xid === dentistId);

    if (!doctor) return true;
    if (!doctor.available || doctor.is_on_holiday) return true;
    if (isDoctorOnHoliday(dentistId, targetDateStr)) return true;
    if (isDoctorOnBreak(dentistId, timeSlot, targetDateStr)) return true;

    return false;
};

const getAppointmentDurationInSlots = (appointment) => {
    const start = moment(appointment.start_time, TIME_FORMAT_DISPLAY);
    const end = moment(appointment.end_time, TIME_FORMAT_DISPLAY);
    const duration = end.diff(start, "minutes");
    return Math.max(1, Math.ceil(duration / SLOT_DURATION_MINUTES));
};

const isSlotOccupied = (dentistId, timeSlot, dateStr = null) => {
    const slotIndex = timeSlots.value.indexOf(timeSlot);
    if (slotIndex === -1) return false;

    const targetDateStr = dateStr || currentDate.value.format(DATE_FORMAT_DB);

    return appointments.value.some((apt) => {
        if (
            dentistId &&
            dentistId !== "week-view" &&
            apt.dentist_id !== dentistId
        )
            return false;
        if (apt.appointment_date !== targetDateStr) return false;

        const startSlot = convertAppointmentTimeToSlot(apt.start_time);
        const startIndex = timeSlots.value.indexOf(startSlot);
        if (startIndex === -1) return false;

        const duration = getAppointmentDurationInSlots(apt);
        // Check if current slot is within [start + 1, start + duration - 1]
        return slotIndex > startIndex && slotIndex < startIndex + duration;
    });
};

const isOutsideSchedule = (timeSlot, dateStr = null) => {
    const targetDate = dateStr
        ? moment(dateStr, DATE_FORMAT_DB)
        : currentDate.value;
    const dayOfWeek = targetDate.isoWeekday();
    const currentClinicId = selectedClinicId.value;

    let daySchedule = null;
    if (clinicSchedules.value && clinicSchedules.value.length > 0) {
        if (currentClinicId) {
            daySchedule = clinicSchedules.value.find(
                (s) =>
                    s.day_of_week === dayOfWeek &&
                    (s.x_clinic_id === currentClinicId ||
                        s.clinic_id === currentClinicId),
            );
        }
        if (!daySchedule && !currentClinicId) {
            daySchedule = clinicSchedules.value.find(
                (s) => s.day_of_week === dayOfWeek,
            );
        }
    }

    // Default to closed if no schedule found (clinic only saves open days)
    if (!daySchedule) return true;

    const slotTime24 = convertToTime24(timeSlot) + ":00";
    const startTime = daySchedule.start_time;
    const endTime = daySchedule.end_time;

    return slotTime24 < startTime || slotTime24 >= endTime;
};

// --- Actions (Navigation, API, User Interaction) ---

const onTabChange = (key) => (activeTab.value = key);

const goToToday = () => (currentDate.value = moment());

const findAdjacentScheduleDay = (startDate, direction) => {
    let check = startDate.clone();
    for (let i = 0; i < 14; i++) {
        check.add(direction, "day");
        if (hasScheduleForDay(check)) return check;
    }
    return startDate;
};

const previousDate = () => {
    if (viewMode.value === "day") {
        currentDate.value = findAdjacentScheduleDay(currentDate.value, -1);
    } else if (viewMode.value === "week") {
        currentDate.value = currentDate.value.clone().subtract(1, "week");
    } else {
        // month and agenda views navigate by a full month
        currentDate.value = currentDate.value.clone().subtract(1, "month");
    }
};

const nextDate = () => {
    if (viewMode.value === "day") {
        currentDate.value = findAdjacentScheduleDay(currentDate.value, 1);
    } else if (viewMode.value === "week") {
        currentDate.value = currentDate.value.clone().add(1, "week");
    } else {
        // month and agenda views navigate by a full month
        currentDate.value = currentDate.value.clone().add(1, "month");
    }
};

const onViewModeChange = (mode) => {
    viewMode.value = mode;
    fetchCalendarData();
};

const handleMonthDayClick = (date, event) => {
    // Open add appointment popover instead of switching to day view
    const dateStr = date.format(DATE_FORMAT_DB);
    addAppointmentToSlot(null, "09:00am", event, dateStr);
};

const handleViewChange = ({ mode, date }) => {
    if (date) currentDate.value = date;
    viewMode.value = mode;
    fetchCalendarData();
};

const toggleDentistMenu = (dentistId) => {
    // Show actions for dentist
    message.info(`Menu for ${dentistId}`);
};

// --- API Calls ---

const fetchCalendarData = async ({ background = false } = {}) => {
    if (!background) loading.value = true;
    try {
        let dateStr, endDateStr;
        if (viewMode.value === "week") {
            const startOfWeek = currentDate.value.clone().startOf("isoWeek");
            const endOfWeek = startOfWeek.clone().add(6, "days");
            dateStr = startOfWeek.format(DATE_FORMAT_DB);
            endDateStr = endOfWeek.format(DATE_FORMAT_DB);
        } else if (viewMode.value === "agenda" || viewMode.value === "month") {
            const startOfMonth = currentDate.value.clone().startOf("month");
            const endOfMonth = currentDate.value.clone().endOf("month");

            // Month view needs padding days to fill the grid
            const fetchStart =
                viewMode.value === "month"
                    ? startOfMonth.clone().startOf("isoWeek")
                    : startOfMonth;
            const fetchEnd =
                viewMode.value === "month"
                    ? endOfMonth.clone().endOf("isoWeek")
                    : endOfMonth;

            dateStr = fetchStart.format(DATE_FORMAT_DB);
            endDateStr = fetchEnd.format(DATE_FORMAT_DB);
        } else {
            dateStr = currentDate.value.format(DATE_FORMAT_DB);
            endDateStr = dateStr;
        }

        const params = new URLSearchParams({
            date: dateStr, // keep for legacy if the API requires 'date'
            start_date: dateStr,
            end_date: endDateStr,
        });

        if (selectedDentists.value?.length) {
            params.append("doctor_ids", selectedDentists.value.join(","));
        }
        if (selectedClinicId.value) {
            params.append("clinic_id", selectedClinicId.value);
        }

        const res = await window.axiosAdmin.get(
            `calendar-data?${params.toString()}`,
        );

        if (res.success) {
            dentists.value = res.data.doctors;
            console.log("FETCH_CALENDAR_DATA_SUCCESS:", {
                receivedCount: res.data.doctors.length,
                doctorIds: params.get("doctor_ids"),
            });
            // Auto-select doctors if none are persisted
            if (!isInitialized.value && dentists.value.length > 0) {
                if (
                    viewMode.value === "day" &&
                    selectedDentists.value.length === 0
                ) {
                    selectedDentists.value = dentists.value.map((d) => d.xid);
                } else if (
                    viewMode.value === "week" &&
                    selectedDentists.value.length === 0
                ) {
                    selectedDentists.value = [dentists.value[0].xid];
                }
                isInitialized.value = true;
            }
            appointments.value = [
                ...(res.data.appointments || []),
                ...(res.data.events || []),
            ];
            doctorHolidays.value = res.data.holidays || [];
            updateAppointmentSlots();
        }
    } catch (e) {
        console.error(e);
        message.error(t("common.error_loading_data"));
    } finally {
        if (!background) loading.value = false;
    }
};

const debouncedFetchData = debounce(() => {
    fetchCalendarData();
}, 300);

// --- Wrapper Logic ---

const draftAppointment = computed(() => {
    if (
        !slotAppointmentModalVisible.value ||
        !slotAppointmentFormData.value.selectedTimeSlot
    ) {
        return null;
    }

    const formData = slotAppointmentFormData.value;

    // Parse start time
    const isPM = formData.selectedTimeSlot.toLowerCase().includes("pm");
    const [timePart] = formData.selectedTimeSlot.toLowerCase().split(/am|pm/);
    const [hours, minutes] = timePart.split(":");

    let hour = parseInt(hours);
    if (isPM && hour !== 12) hour += 12;
    else if (!isPM && hour === 12) hour = 0;

    const startObj = moment()
        .hour(hour)
        .minute(minutes || 0)
        .second(0);
    const startTimeStr = startObj.format("HH:mm:ss");

    // Calculate end time
    const durationMins =
        formData.duration_unit === "hours"
            ? parseInt(formData.duration_display || 1) * 60
            : parseInt(formData.duration_display || 30);

    const endObj = startObj.clone().add(durationMins, "minutes");
    const endTimeStr = endObj.format("HH:mm:ss");

    // Month is 0-indexed in JS date objects, +1 for API format
    const monthStr = String((formData.currentMonth || 0) + 1).padStart(2, "0");
    const dateStr = String(formData.selectedDate || 1).padStart(2, "0");
    const apptDateStr = `${formData.currentYear}-${monthStr}-${dateStr}`;

    return {
        id: "draft-1",
        xid: "draft-1",
        is_draft: true, // Special flag for styling
        dentist_id: formData.doctor_id,
        patient: {
            name:
                formData.form_type === "event"
                    ? t("calendar.new_event", "New Event...")
                    : t("appointments.new_appointment", "New Appointment..."),
        },
        treatment_type: { name: t("common.draft", "Draft") },
        appointment_date: apptDateStr,
        start_time: startTimeStr,
        end_time: endTimeStr,
        status: "draft",
    };
});

const updateAppointmentSlots = () => {
    const newSlots = {};
    const dateStr = currentDate.value.format(DATE_FORMAT_DB);

    // Filter appointments for current view
    let activeAppointments = [];
    if (viewMode.value === "day") {
        activeAppointments = appointments.value.filter(
            (apt) => apt.appointment_date === dateStr,
        );
    } else {
        activeAppointments = [...appointments.value];
    }

    // Inject the draft appointment if it exists
    if (draftAppointment.value) {
        if (
            viewMode.value === "day" &&
            draftAppointment.value.appointment_date === dateStr
        ) {
            activeAppointments.push(draftAppointment.value);
        } else if (viewMode.value === "week") {
            // Include draft in week view
            activeAppointments.push(draftAppointment.value);
        }
    }

    // Group to process layout isolated per column
    const visualGroups = {};
    activeAppointments.forEach((apt) => {
        const groupKey =
            viewMode.value === "day" ? apt.dentist_id : apt.appointment_date;
        if (!visualGroups[groupKey]) {
            visualGroups[groupKey] = [];
        }
        visualGroups[groupKey].push(apt);
    });

    Object.values(visualGroups).forEach((group) => {
        // 1. Sort by start time.
        // Duration tie-breaker: longer events sorting
        group.sort((a, b) => {
            const startA = moment(a.start_time, TIME_FORMAT_DISPLAY);
            const startB = moment(b.start_time, TIME_FORMAT_DISPLAY);
            if (startA.isBefore(startB)) return -1;
            if (startA.isAfter(startB)) return 1;

            // Tie-break: longer first (Duration Descending)
            const endA = moment(a.end_time, TIME_FORMAT_DISPLAY);
            const endB = moment(b.end_time, TIME_FORMAT_DISPLAY);
            const durA = endA.diff(startA);
            const durB = endB.diff(startB);
            return durB - durA;
        });

        // 2. Assign columns (Greedy packing)
        const columns = []; // Array of "last end time" for each column

        group.forEach((apt) => {
            const start = moment(apt.start_time, TIME_FORMAT_DISPLAY);
            const end = moment(apt.end_time, TIME_FORMAT_DISPLAY);

            let placed = false;
            for (let i = 0; i < columns.length; i++) {
                const lastEnd = columns[i];
                if (lastEnd.isSameOrBefore(start)) {
                    // Fits in this column
                    columns[i] = end;
                    apt._visual_col = i;
                    placed = true;
                    break;
                }
            }

            if (!placed) {
                // Start new column
                columns.push(end);
                apt._visual_col = columns.length - 1;
            }
        });

        // 3. Mark total columns (for calculating offsets/widths if needed)
        // For cascading, we mostly care about _visual_col index.
        const meta = group.map((apt) => ({
            apt,
            start: moment(apt.start_time, TIME_FORMAT_DISPLAY),
            end: moment(apt.end_time, TIME_FORMAT_DISPLAY),
            col: apt._visual_col,
        }));

        meta.forEach((item) => {
            const overlappingGroup = meta.filter(
                (other) =>
                    other.start.isBefore(item.end) &&
                    other.end.isAfter(item.start),
            );
            const maxCol = Math.max(...overlappingGroup.map((o) => o.col));
            item.apt._visual_max_cols = maxCol + 1;
        });
    });

    // Populate the slot map
    activeAppointments.forEach((apt) => {
        const slotTime = convertAppointmentTimeToSlot(apt.start_time);
        const colKey =
            viewMode.value === "day" ? apt.dentist_id : apt.appointment_date;
        const key = `${colKey}-${slotTime}`;
        if (!newSlots[key]) newSlots[key] = [];
        newSlots[key].push(apt);
    });

    appointmentSlots.value = newSlots;
};

const addReservation = (form_type = "appointment") => {
    appointmentFormData.value = {
        currentMonth: currentDate.value.month(),
        currentYear: currentDate.value.year(),
        selectedDate: currentDate.value.date(),
        form_type: form_type,
    };
    if (form_type === "event") {
        eventModalVisible.value = true;
    } else {
        appointmentModalVisible.value = true;
    }
};

const onSlotClick = (dentistId, time, event, dateStr = null) => {
    if (event) {
        event.stopPropagation();
    }
    if (
        !isSlotUnavailable(dentistId, time, dateStr) &&
        !isSlotOccupied(dentistId, time, dateStr)
    ) {
        addAppointmentToSlot(dentistId, time, event, dateStr);
    }
};

const addAppointmentToSlot = (dentistId, timeSlot, event, dateStr = null) => {
    const targetDate = dateStr
        ? moment(dateStr, DATE_FORMAT_DB)
        : currentDate.value;

    // Default to selected dentist if none provided or if we received the generic 'week-view' column identifier
    let targetDentist = dentistId;
    if (!targetDentist || targetDentist === "week-view") {
        targetDentist =
            selectedDentists.value.length > 0
                ? selectedDentists.value[0]
                : null;
    }

    slotAppointmentFormData.value = {
        doctor_id: targetDentist,
        duration: 30,
        currentMonth: targetDate.month(),
        currentYear: targetDate.year(),
        selectedDate: targetDate.date(),
        selectedTimeSlot: timeSlot,
    };

    if (event && event.currentTarget) {
        const rect = event.currentTarget.getBoundingClientRect();
        popoverAnchorStyle.value = {
            top: `${rect.top}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            height: `${rect.height}px`,
            position: "fixed",
        };
    }

    slotAppointmentModalVisible.value = true;
};

const onAppointmentSave = () => {
    closeAppointmentModal();
    closeSlotAppointmentModal();

    // Trigger background fetch instead of awaiting it
    fetchCalendarData({ background: true });
};

const closeAppointmentModal = () => {
    appointmentModalVisible.value = false;
    appointmentFormData.value = {};
};

const closeSlotAppointmentModal = () => {
    slotAppointmentModalVisible.value = false;
    isWarningModalOpen.value = false;
    slotAppointmentFormData.value = {};
    popoverAnchorStyle.value = {
        top: "-9999px",
        left: "-9999px",
        width: "0px",
        height: "0px",
        position: "fixed",
    };
};

const handlePopoverOpenChange = (open) => {
    if (!open && isWarningModalOpen.value) {
        return; // Prevent closing if warning modal is active
    }
    slotAppointmentModalVisible.value = open;
    if (!open) {
        closeSlotAppointmentModal();
    }
};

// Overview & Drag/Drop
const openAppointment = (apt) => {
    overviewData.value = { ...apt };
    overviewVisible.value = true;
    // Setup list for navigation
    overviewList.value = [apt]; // Simplified for now
    currentOverviewIndex.value = 0;
};

const onOverviewClosed = () => {
    overviewVisible.value = false;
};

const onOverviewPrev = () => {
    // Logic
};
const onOverviewNext = () => {
    // Logic
};

const onDragStart = (appointment) => {
    // Delay state update so browsers snapshot the drag image at full opacity
    setTimeout(() => {
        draggedAppointmentId.value = appointment?.id || null;
    }, 0);
    document.body.classList.add("no-select");
};

const onDragEnd = () => {
    draggedAppointmentId.value = null;
    document.body.classList.remove("no-select");
};

const onDragMove = (evt) => {
    return true;
};

const onAppointmentMove = async (evt, dentistId, timeSlot, dateStr = null) => {
    if (!evt.added) return;
    const apt = evt.added.element;

    const startM = moment(timeSlot, TIME_FORMAT_DISPLAY);
    const newDbTime = startM.format("HH:mm:ss");
    const newDbDate = dateStr || currentDate.value.format(DATE_FORMAT_DB);
    const newEndTime = startM
        .clone()
        .add(apt.duration || 60, "minutes")
        .format(TIME_FORMAT_DISPLAY);

    // In week view, dentistId might be passed as generic string or null. Keep originality if so.
    const isGenericDentist = dentistId === "week-view" || !dentistId;
    const finalDentistId = isGenericDentist
        ? apt.doctor_id || apt.dentist_id
        : dentistId;

    // Optimistic update
    const idx = appointments.value.findIndex((a) => a.id === apt.id);
    if (idx !== -1) {
        appointments.value[idx] = {
            ...appointments.value[idx],
            doctor_id: finalDentistId,
            dentist_id: finalDentistId,
            start_time: startM.format(TIME_FORMAT_DISPLAY),
            end_time: newEndTime,
            appointment_date: newDbDate,
        };
    }
    updateAppointmentSlots();

    // Prepare reschedule modal data
    const originalStart = moment(
        `${apt.appointment_date} ${apt.start_time}`,
        `${DATE_FORMAT_DB} ${TIME_FORMAT_DISPLAY}`,
    );
    const originalEnd = moment(
        `${apt.appointment_date} ${apt.end_time}`,
        `${DATE_FORMAT_DB} ${TIME_FORMAT_DISPLAY}`,
    );

    const newStart = startM.clone();
    const newEnd = startM.clone().add(apt.duration || 60, "minutes");

    const formatStringDate = "ddd DD MMM YYYY";

    const isEvent = apt.type === "event";
    const url = isEvent
        ? `calendar-events/${apt.id}`
        : `appointments/${apt.id}`;

    const payload = isEvent
        ? {
              _method: "PUT",
              doctor_id: finalDentistId,
              event_date: newDbDate,
              event_time: startM.format("HH:mm"),
              duration: apt.duration,
          }
        : {
              appointment_date: `${newDbDate} ${newDbTime}`,
              doctor_id: finalDentistId,
              _method: "PUT",
          };

    rescheduleData.value = {
        appointment: apt,
        originalStartStr: originalStart.format(formatStringDate),
        originalTimeStr: `${originalStart.format("h:mmA")} - ${originalEnd.format("h:mmA")}`,
        newStartStr: moment(newDbDate, DATE_FORMAT_DB).format(formatStringDate),
        newTimeStr: `${newStart.format("h:mmA")} - ${newEnd.format("h:mmA")}`,
        isEvent,
        url,
        payload,
    };

    rescheduleModalVisible.value = true;
};

const cancelReschedule = () => {
    if (rescheduleData.value && rescheduleData.value.appointment) {
        const apt = rescheduleData.value.appointment;
        const idx = appointments.value.findIndex((a) => a.id === apt.id);
        if (idx !== -1) {
            // Revert to original state
            appointments.value[idx] = { ...apt };
            updateAppointmentSlots();
        }
    }
    rescheduleModalVisible.value = false;
    rescheduleData.value = null;
};

const confirmReschedule = (shouldNotifyGuests) => {
    if (!rescheduleData.value) return;

    // Immediately close modal and show loading state
    const { isEvent, url, payload } = rescheduleData.value;
    rescheduleModalVisible.value = false;
    rescheduleData.value = null;

    if (!isEvent && shouldNotifyGuests) {
        payload.send_notification = true;
    } else if (!isEvent) {
        payload.send_notification = false;
    }

    const hideLoading = message.loading(t("common.saving", "Saving..."), 0);

    window.axiosAdmin
        .post(url, payload)
        .then(() => {
            hideLoading();
            // Refetch in background to avoid full-page spinner
            fetchCalendarData({ background: true });
        })
        .catch((e) => {
            hideLoading();
            console.error("Reschedule failed", e);
            // Revert changes visually since it failed
            fetchCalendarData({ background: true });
        });
};

const onResizeStart = (appointment) => {
    resizingAppointmentId.value = appointment?.id || null;
    document.body.classList.add("no-select");
    document.body.classList.add("is-resizing");
};

const onAppointmentResize = async ({
    appointment,
    newDuration,
    dateStr = null,
}) => {
    resizingAppointmentId.value = null;
    document.body.classList.remove("no-select");
    document.body.classList.remove("is-resizing");

    const apt = appointment;
    // Assuming TIME_FORMAT_DISPLAY is available in scope (it is used in onAppointmentMove)
    const startM = moment(apt.start_time, TIME_FORMAT_DISPLAY);

    // Calculate new end time
    const endM = startM.clone().add(newDuration, "minutes");
    const newEndTime = endM.format(TIME_FORMAT_DISPLAY);
    const dbDate = dateStr || currentDate.value.format(DATE_FORMAT_DB);
    const dbStartTime = startM.format("HH:mm:ss");
    const dbEndTime = endM.format("HH:mm:ss");

    // Optimistic update
    const idx = appointments.value.findIndex((a) => a.id === apt.id);
    if (idx !== -1) {
        appointments.value[idx] = {
            ...appointments.value[idx],
            end_time: newEndTime,
            // Update duration if tracked locally
        };
        updateAppointmentSlots();
    }

    const originalStart = moment(
        `${apt.appointment_date} ${apt.start_time}`,
        `${DATE_FORMAT_DB} ${TIME_FORMAT_DISPLAY}`,
    );
    const originalEnd = moment(
        `${apt.appointment_date} ${apt.end_time}`,
        `${DATE_FORMAT_DB} ${TIME_FORMAT_DISPLAY}`,
    );

    const newStart = startM.clone();
    const newEnd = endM.clone();

    const formatStringDate = "ddd DD MMM YYYY";

    const isEvent = apt.type === "event";
    const url = isEvent
        ? `calendar-events/${apt.id}`
        : `appointments/${apt.id}`;

    const payload = isEvent
        ? {
              _method: "PUT",
              doctor_id: apt.doctor_id || apt.dentist_id,
              event_date: dbDate,
              event_time: startM.format("HH:mm"),
              duration: newDuration,
          }
        : {
              _method: "PUT",
              doctor_id: apt.doctor_id || apt.dentist_id,
              appointment_date: `${dbDate} ${dbStartTime}`,
              duration: newDuration,
          };
    rescheduleData.value = {
        appointment: apt,
        originalStartStr: originalStart.format(formatStringDate),
        originalTimeStr: `${originalStart.format("h:mmA")} - ${originalEnd.format("h:mmA")}`,
        newStartStr: moment(dbDate, DATE_FORMAT_DB).format(formatStringDate),
        newTimeStr: `${newStart.format("h:mmA")} - ${newEnd.format("h:mmA")}`,
        isEvent,
        url,
        payload,
    };

    rescheduleModalVisible.value = true;
};

// Lifecycle
watch(currentDate, debouncedFetchData);
watch(selectedDentists, debouncedFetchData, { deep: true });
watch(selectedClinicId, debouncedFetchData);
watch(appointments, updateAppointmentSlots, { deep: true });

onMounted(async () => {
    await new Promise((r) => setTimeout(r, 100));
    await store.dispatch("auth/updateApp");
    await fetchCalendarData();
});
</script>

<style scoped>
.search-container {
    display: flex;
    align-items: center;
}

.calendar-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 16px 24px;
    border-radius: 8px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Custom Drag Styles for vuedraggable */
html body .appointment-drag {
    width: 200px !important; /* Prevent w-full from expanding to screen width */
    opacity: 1 !important;
    z-index: 2147483647 !important; /* Max Z-Index */
    cursor: grabbing !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
    right: auto !important;
    bottom: auto !important;
    transform: none !important;
}

/* Ghost (Placeholder) Styles - Visible during drag over slots */
html body .appointment-ghost {
    background: rgba(230, 247, 255, 0.95) !important;
    border: 2px dashed #1890ff !important;
    opacity: 1 !important;
    z-index: 2147483640 !important; /* Very high priority */
    pointer-events: none !important;
    transform: none !important;
    transition: none !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;

    /* CRITICAL: Prevent ghost from expanding slot and causing target flickering */
    height: 30px !important;
    min-height: 30px !important;
    overflow: hidden !important;
}

/* GLOBAL RESIZE LOCKS */
html body.is-resizing,
html body.is-resizing * {
    cursor: ns-resize !important;
}

html body.is-resizing .calendar-slot {
    pointer-events: none !important;
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 24px;
}

.appointment-count {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #595959;
    font-size: 14px;
}

.calendar-tabs-section {
    background: white;
    border-radius: 8px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    padding: 16px 24px;
}

.calendar-tabs-section .ant-tabs {
    margin: 0;
}

.nav-center {
    display: flex;
    align-items: center;
    gap: 16px;
}

.date-navigation {
    display: flex;
    align-items: center;
    gap: 12px;
}

.current-date {
    font-weight: 600;
    color: #262626;
    min-width: 180px;
    text-align: center;
}

.nav-right {
    display: flex;
    align-items: center;
}

.no-schedule-message {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.no-schedule-content {
    text-align: center;
    max-width: 400px;
}

.no-schedule-icon {
    font-size: 64px;
    color: #d9d9d9;
    margin-bottom: 24px;
}

.no-schedule-content h3 {
    color: #262626;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 8px;
}

.no-schedule-content p {
    color: #8c8c8c;
    font-size: 16px;
    margin-bottom: 8px;
}

.no-schedule-subtitle {
    color: #bfbfbf;
    font-size: 14px;
}
</style>

<style>
/* Disable text selection while dragging appointments */
.no-select {
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
    user-select: none !important;
}
</style>
