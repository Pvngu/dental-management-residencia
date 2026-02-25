<template>
    <admin-page-table-content>
        <!-- Page Header -->
        <div class="bg-white p-3 sm:p-5 rounded-lg shadow-sm mb-4 mt-2">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-semibold mb-2">{{ $t('patients.assign_doctor') }}</h1>
                    <div v-if="patient" class="text-gray-600 text-sm sm:text-base">
                        <span class="font-medium">{{ $t('common.patient') }}:</span>
                        {{ patient.user?.name }} {{ patient.user?.last_name }}
                    </div>
                </div>
                <a-button @click="goBack" class="flex items-center self-start sm:self-auto">
                    <LeftOutlined class="mr-2" />
                    {{ $t('common.back') }}
                </a-button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Left Side - Calendar and Time -->
            <div class="w-full lg:w-80 lg:flex-shrink-0 flex flex-col">
                <!-- Calendar Card -->
                <div class="bg-white rounded-lg shadow-sm border p-4 mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <button @click="previousMonth" class="p-2 hover:bg-gray-100 rounded">
                            <LeftOutlined class="text-gray-600" />
                        </button>
                        <h2 class="text-lg font-semibold">{{ currentMonthYear }}</h2>
                        <button @click="nextMonth" class="p-2 hover:bg-gray-100 rounded">
                            <RightOutlined class="text-gray-600" />
                        </button>
                    </div>
                    
                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                             :key="day" 
                             class="text-center text-xs font-medium text-gray-500 py-2">
                            {{ day }}
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div class="grid grid-cols-7 gap-1">
                            <div v-for="date in calendarDates" :key="date.key" class="h-8 sm:h-10">
                                <button
                                    v-if="date.day"
                                    @click="selectDate(date)"
                                    :disabled="date.disabled"
                                    :class="[
                                        'w-full h-full text-xs sm:text-sm rounded transition-colors',
                                        date.isToday ? 'bg-blue-500 !text-white font-semibold' : '',
                                        date.isSelected ? '!bg-blue-200 font-semibold' : '',
                                        !date.isToday && !date.isSelected && !date.disabled ? 'hover:bg-gray-100' : '',
                                        date.disabled ? 'text-gray-300 cursor-not-allowed bg-gray-50' : 'cursor-pointer'
                                    ]"
                                >
                                    {{ date.day }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Available Card -->
                <div class="bg-white rounded-lg shadow-sm border p-4">
                    <h3 class="font-medium mb-3">Time Available</h3>
                    
                    <!-- Time Picker -->
                    <div class="mb-4">
                        <TimePicker
                            :timeRange="selectedTimeRange"
                            :isRange="true"
                            format="HH:mm"
                            @timeRangeChanged="onTimeRangeChanged"
                            disableOutsideSchedule
                            :minuteStep="5"
                            @change="setUrlData"
                        />
                    </div>

                    <!-- Treatment Type Checkboxes -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-2">{{ $t('calendar.treatment_type') }}</label>
                        <div class="treatment-options">
                            <div v-if="treatmentLoading" class="text-sm text-gray-500">{{ $t('common.loading') }}...</div>
                            <div v-else-if="treatmentOptions.length === 0" class="text-sm text-gray-500">{{ $t('appointments.no_treatment_types') || 'No treatment types' }}</div>
                            <div v-else>
                                <a-select
                                    v-model:value="selectedTreatments"
                                    mode="multiple"
                                    :placeholder="$t('common.select_default_text', [$t('calendar.treatment_type')])"
                                    :loading="treatmentLoading"
                                    style="width: 100%"
                                    optionFilterProp="label"
                                    show-search
                                    @change="() => setUrlData()"
                                >
                                    <a-select-option 
                                        v-for="t in treatmentOptions" 
                                        :key="t.xid" 
                                        :value="t.xid" 
                                        :label="t.name"
                                    >
                                        {{ t.name }}
                                    </a-select-option>
                                </a-select>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Specialty Select -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium mb-2">{{ $t('doctors.specialty') }}</label>
                        <div>
                            <div v-if="specialtyLoading" class="text-sm text-gray-500">{{ $t('common.loading') }}...</div>
                            <div v-else-if="specialties.length === 0" class="text-sm text-gray-500">{{ $t('doctors.no_specialties') || 'No specialties' }}</div>
                            <div v-else>
                                <a-select
                                    v-model:value="selectedSpecialties"
                                    mode="multiple"
                                    :placeholder="$t('common.select_default_text', [$t('doctors.specialty')])"
                                    :loading="specialtyLoading"
                                    style="width: 100%"
                                    optionFilterProp="label"
                                    show-search
                                    @change="() => setUrlData()"
                                >
                                    <a-select-option v-for="s in specialties" :key="s.xid" :value="s.xid" :label="s.name">
                                        {{ s.name }}
                                    </a-select-option>
                                </a-select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t text-sm text-gray-600">
                        <div class="flex items-center space-x-2 mb-1">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span>Available or urgency patient</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Doctor List -->
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm border p-3 sm:p-4">
                    <h2 class="text-lg sm:text-xl font-semibold mb-4">List Doctor</h2>

                    <a-spin :spinning="scheduleLoading">
                        <template v-if="filteredDoctors && filteredDoctors.length > 0">
                            <transition-group name="doctor-list" tag="div" class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">
                                <div v-for="(doctor, index) in filteredDoctors" :key="doctor.xid" :style="staggerStyle(index)" class="doctor-card">
                                    <div class="relative border rounded-lg p-2 sm:p-3 lg:p-4 transform border-gray-200 hover:border-gray-300 hover:shadow-sm">
                                        <!-- <div class="absolute top-3 right-3">
                                            <a-badge 
                                                :count="1" 
                                                :number-style="{
                                                backgroundColor: '#fff',
                                                color: '#999',
                                                boxShadow: '0 0 0 1px #d9d9d9 inset',
                                                }"
                                            />
                                        </div> -->
                                        <div class="flex items-start space-x-2 sm:space-x-3 mb-4">
                                            <div class="flex-shrink-0">
                                                <a-avatar :size="64" :src="doctor.profile_image_url">
                                                    <UserOutlined v-if="!doctor.profile_image_url" />
                                                </a-avatar>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between mb-1">
                                                    <h3 class="font-medium text-gray-900 text-xs sm:text-sm lg:text-base truncate pr-1">{{ doctor.full_name }}</h3>
                                                    <template v-if="patient && patient.x_preferred_doctor_id && (doctor.xid == patient.x_preferred_doctor_id)">
                                                        <a-tooltip :title="$t('patients.preferred_doctor') || 'Preferred Doctor'">
                                                            <StarOutlined style="color: #f59e0b; font-size: 12px;" class="flex-shrink-0" />
                                                        </a-tooltip>
                                                    </template>
                                                </div>
                                                <p v-if="doctor.department" class="text-xs text-gray-600 mb-1 truncate">{{ doctor.department.name }}</p>
                                                <div v-if="doctor.specialties && doctor.specialties.length > 0" class="mb-1">
                                                    <div class="flex flex-wrap gap-1">
                                                        <span v-for="specialty in doctor.specialties" :key="specialty.xid" class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded truncate">{{ specialty.name }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center text-xs text-gray-600 mb-2">
                                                    <ClockCircleOutlined class="mr-1 flex-shrink-0" style="font-size: 10px;" />
                                                    <span class="truncate">{{ formatAvailableTime(doctor) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex gap-1 sm:gap-2">
                                            <a-button class="flex-1 min-w-0">
                                                <template #icon><MessageOutlined /></template>
                                                <span>Message</span>
                                            </a-button>
                                            <a-button @click="createAppointment(doctor)" type="primary" class="flex-1 min-w-0">
                                                <template #icon><CalendarOutlined /></template>
                                                <span>Create appointment</span>
                                            </a-button>
                                        </div>
                                    </div>
                                </div>
                            </transition-group>
                        </template>
                        <template v-else>
                            <div class="text-center py-12">
                                <div class="inline-flex flex-col items-center text-gray-500">
                                    <UserOutlined style="font-size:48px;" />
                                    <h3 class="mt-4 text-lg font-semibold">{{ $t('doctors.no_doctors') || 'No doctors available' }}</h3>
                                </div>
                            </div>
                        </template>
                    </a-spin>
                </div>
            </div>
        </div>

        <!-- Appointment Modal -->
        <AppointmentModal
            :visible="appointmentModalVisible"
            :formData="appointmentFormData"
            :autoSelectPatient="true"
            :patientId="props.patientId"
            url="appointments"
            addEditType="add"
            :pageTitle="selectedDoctor ? `Create Appointment - ${selectedDoctor.full_name}` : 'Create Appointment'"
            :successMessage="t('appointments.created')"
            @closed="onAppointmentModalClosed"
            @addEditSuccess="onAppointmentSuccess"
        />
    </admin-page-table-content>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import {
    LeftOutlined,
    RightOutlined,
    ClockCircleOutlined,
    UserOutlined,
    EnvironmentOutlined,
    MessageOutlined,
    CalendarOutlined,
    StarOutlined
} from "@ant-design/icons-vue";
import { notification, Modal } from "ant-design-vue";
import common from "../../../../common/composable/common";
import TimePicker from "../../../../common/components/common/calendar/TimePicker.vue";
import AppointmentModal from "../../../components/appointment/index.vue";

const props = defineProps({
    patientId: {
        required: true
    }
});

const { t } = useI18n();
const router = useRouter();
const { dayjs, formatDate } = common();
// fields() usually lives in ./fields.js. We inline the same param string here
const addEditUrlParams = "id,xid,allergies,updated_at,user,user:role,pharmacy_name,pharmacy_phone,blood_type,emergencyContacts,insurances,primaryInsurance,secondaryInsurance,media_channels,preferred_doctor_id,x_preferred_doctor_id";

    // State
const patient = ref(null);
// holds selected treatment xids (from treatment_types endpoint)
const selectedTreatments = ref([]);

// selected specialties
const selectedSpecialties = ref([]);

// doctor specialties fetched from API
const specialties = ref([]);
const specialtyLoading = ref(false);

// treatment types fetched from API
const treatmentOptions = ref([]);
const treatmentLoading = ref(false);

// Calendar state
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedDate = ref(null);
// available dates returned from API for selected doctor/month
const availableDates = ref(null); // Initialize as null to indicate no data loaded yet

// loading state for schedule
const listLoading = ref(false);

// alias used for spinner on the doctor schedule/list
const scheduleLoading = listLoading;

// Time state
const selectedTimeRange = ref(['10:00', '12:00']);

// Appointment modal state
const appointmentModalVisible = ref(false);
const appointmentFormData = ref({
    doctor_id: null,
    patient_id: null,
    room_id: null,
    treatment_type_id: null,
    reason_visit: '',
    duration: 30,
    selectedDate: null,
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear()
});
const selectedDoctor = ref(null);

const doctors = ref([]);

// Computed properties
const currentMonthYear = computed(() => {
    const date = new Date(currentYear.value, currentMonth.value);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const calendarDates = computed(() => {
    const firstDay = new Date(currentYear.value, currentMonth.value, 1);
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    const firstDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    
    const dates = [];
    const today = new Date();
    
    // Empty cells for days before the first day of month
    for (let i = 0; i < firstDayOfWeek; i++) {
        dates.push({ key: `empty-${i}`, day: null });
    }
    
    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(currentYear.value, currentMonth.value, day);
        const isToday = date.toDateString() === today.toDateString();
        const isSelected = selectedDate.value && date.toDateString() === selectedDate.value.toDateString();
        // don't mutate today's time for subsequent iterations
        const startOfToday = new Date(); startOfToday.setHours(0,0,0,0);
        const isPast = date < startOfToday;

        // By default, past dates are disabled and cannot be re-enabled by availableDates
        let disabled = isPast;
        
        // If not a past date, check availability
        if (!isPast) {
            // If availableDates is null/undefined, we haven't loaded data yet, so keep future dates enabled
            // If availableDates is an empty array, keep future dates enabled (no restrictions)
            // If availableDates has items, only enable dates that are in the list
            if (availableDates.value === null || availableDates.value === undefined) {
                // No availability data loaded yet, keep future dates enabled
                disabled = false;
            } else if (availableDates.value.length === 0) {
                // Empty array means no specific restrictions, keep future dates enabled
                disabled = false;
            } else {
                // availableDates expected to be array of numbers (day of month) or ISO dates; handle both
                const monthDayList = availableDates.value || [];
                // normalize to day numbers (1..31) if items are ISO dates or strings
                const availableDayNumbers = monthDayList.map(d => {
                    if (!d && d !== 0) return null;
                    if (typeof d === 'number') return d;
                    const parsed = new Date(d);
                    if (!isNaN(parsed)) return parsed.getDate();
                    const n = parseInt(d);
                    return isNaN(n) ? null : n;
                }).filter(Boolean);

                // If the day is not in the available list, disable it. Otherwise keep enabled.
                disabled = !availableDayNumbers.includes(day);
            }
        }

        dates.push({
            key: `day-${day}`,
            day,
            date,
            isToday,
            isSelected,
            disabled
        });
    }
    
    return dates;
});

// Replace remote fetch with dummy availability for now (static/example data)
// availableDates will be an array of day numbers for the current month (1..31)
const getDoctorSchedule = () => {
    // no-op schedule fetch when select is removed; provide a default availability
    listLoading.value = true;

    // Default dummy available_dates for the current month
    availableDates.value = [1,2,3,4,8,9,10,11,15];

    setTimeout(() => {
        listLoading.value = false;
    }, 200);
};

// Provide per-item staggered inline style for transition delay
const staggerStyle = (index) => {
    const delayMs = Math.min(200 + index * 60, 1000); // base 200ms + 60ms per item, cap at 1s
    return {
        transitionDelay: `${delayMs}ms`,
    };
};

// Filter doctors by xid for both treatments and specialties. Doctors have `specialties` array
// where each specialty contains an `xid`. Treatment types are selected by xid too.
const filteredDoctors = computed(() => {
    let filtered = doctors.value || [];

    // Filter by selected treatment xids (selectedTreatments contains xids)
    if (selectedTreatments.value && selectedTreatments.value.length > 0) {
        const selSet = new Set(selectedTreatments.value.map(String));
        filtered = filtered.filter((doctor) => {
            // doctor may have specialties array; check if any specialty.xid matches
            if (!doctor.specialties || doctor.specialties.length === 0) return false;
            return doctor.specialties.some(sp => sp && sp.xid && selSet.has(String(sp.xid)));
        });
    }

    // Filter by selected specialty xids (selectedSpecialties contains xids)
    if (selectedSpecialties.value && selectedSpecialties.value.length > 0) {
        const selSpecSet = new Set(selectedSpecialties.value.map(String));
        filtered = filtered.filter((doctor) => {
            if (!doctor.specialties || doctor.specialties.length === 0) return false;
            return doctor.specialties.some(sp => sp && sp.xid && selSpecSet.has(String(sp.xid)));
        });
    }

    return filtered;
});

// fetch both treatment types and specialties concurrently
const fetchFilters = () => {
    treatmentLoading.value = true;
    specialtyLoading.value = true;

    const treatmentsUrl = 'treatment_types?fields=id,xid,name,duration_minutes&limit=10000';
    const specialtiesUrl = 'doctor-specialty?fields=id,xid,name&limit=10000';

    return Promise.all([
        axiosAdmin.get(treatmentsUrl),
        axiosAdmin.get(specialtiesUrl),
    ])
        .then(([tRes, sRes]) => {
            treatmentOptions.value = (tRes && tRes.data) ? tRes.data : [];
            specialties.value = (sRes && sRes.data) ? sRes.data : [];
        })
        .catch((err) => {
            console.error('Failed to fetch filters', err);
            notification.error({
                message: t('common.error'),
                description: t('common.failed_fetch_treatment_types') || 'Failed to load filter data'
            });
        })
        .finally(() => {
            treatmentLoading.value = false;
            specialtyLoading.value = false;
        });
};

// Methods
const fetchPatientData = () => {
    // mirror Details.vue behaviour: fetch patient with addEditUrlParams
    listLoading.value = true;
    axiosAdmin.get(`patients/${props.patientId}?fields=${addEditUrlParams}`)
        .then((response) => {
            patient.value = response.data;
        })
        .catch((error) => {
            console.error('Error fetching patient data:', error);
            notification.error({
                message: t('common.error'),
                description: t('common.failed_fetch_patient') || 'Failed to fetch patient data'
            });
        })
        .finally(() => {
            listLoading.value = false;
        });
};

const previousMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
    // refresh schedule availability when month changes
    getDoctorSchedule();
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
    // refresh schedule availability when month changes
    getDoctorSchedule();
};

const selectDate = (dateObj) => {
    // prevent selecting disabled dates
    if (dateObj.disabled) return;
    selectedDate.value = dateObj.date;
    setUrlData();
};

const onTimeRangeChanged = (newTimeRange) => {
    selectedTimeRange.value = newTimeRange;
};

const createAppointment = (doctor) => {
    selectedDoctor.value = doctor;
    
    // Set the current month and year for the appointment modal
    const currentDate = selectedDate.value || new Date();
    console.log('Creating appointment for doctor:', doctor, 'on date:', selectedDate.value);
    appointmentFormData.value = {
        doctor_id: doctor.xid,
        patient_id: props.patientId,
        room_id: null,
        treatment_type_id: null,
        reason_visit: '',
        duration: 30,
        selectedDate: selectedDate.value ? selectedDate.value.getDate() : null,
        currentMonth: currentDate.getMonth(),
        currentYear: currentDate.getFullYear()
    };
    appointmentModalVisible.value = true;
};

const onAppointmentModalClosed = () => {
    appointmentModalVisible.value = false;
    selectedDoctor.value = null;
    appointmentFormData.value = {
        doctor_id: null,
        patient_id: null,
        room_id: null,
        treatment_type_id: null,
        reason_visit: '',
        duration: 30,
        selectedDate: null,
        currentMonth: new Date().getMonth(),
        currentYear: new Date().getFullYear()
    };
};

const onAppointmentSuccess = () => {
    appointmentModalVisible.value = false;
    notification.success({
        message: t('appointments.created'),
        description: t('appointments.appointment_created_successfully') || 'Appointment created successfully.'
    });
    
    // Navigate back to patient details
    router.push({
        name: 'admin.patients.detail',
        params: { id: props.patientId }
    });
};

const goBack = () => {
    router.push({
        name: 'admin.patients.detail',
        params: { id: props.patientId }
    });
};

const setUrlData = () => {
    listLoading.value = true;
    
    axiosAdmin.post(`/doctors/availability`, {
        selectedTimeRange: selectedTimeRange.value,
        selectedTreatments: selectedTreatments.value,
        selectedSpecialties: selectedSpecialties.value,
        selectedDate: selectedDate.value
    }).then((res) => {
        if(res && res.data){
            const data = res.data || {};
            // Only update availableDates if the API actually returns availability data
            // If no available_dates field or it's null, keep current state
            if (data.hasOwnProperty('available_dates')) {
                availableDates.value = data.available_dates || [];
            }
            doctors.value = data.doctors || [];
        }
    }).catch((error) => {
        console.error('Error fetching availability:', error);
        // On error, don't change availableDates to avoid disabling all dates
    }).finally(() => {
        listLoading.value = false;
    });
}

const formatAvailableTime = (doctor) => {
    if (doctor.schedule && doctor.schedule.available_time_range && doctor.schedule.available_time_range.length >= 2) {
        const startTime = doctor.schedule.available_time_range[0];
        const endTime = doctor.schedule.available_time_range[1];
        
        // Convert 24-hour format to 12-hour format with AM/PM
        const formatTime = (time) => {
            if (!time) return '';
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        };
        
        return `${formatTime(startTime)} - ${formatTime(endTime)}`;
    }
    
    // Fallback to old property if schedule data is not available
    return doctor.availableTime || 'Time not available';
};

onMounted(() => {
    fetchPatientData();
    // Set today as default selected date
    selectedDate.value = new Date();
    // load treatment types and specialties concurrently
    fetchFilters();

    setUrlData();
});
</script>

<style scoped>
/* Custom styles for the calend
ar and time selector */
.appearance-none {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Ensure proper spacing for doctor cards */
.break-words {
    word-break: break-words;
}

/* Custom checkbox styling */
input[type="checkbox"] {
    accent-color: #3b82f6;
}

/* Smooth transitions */
.transition-colors {
    transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
}

.transition-all {
    transition: all 0.15s ease-in-out;
}

/* Custom scrollbar for overflow areas */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Treatment options scrollable with safe viewport fallbacks */
.treatment-options {
    max-height: 40svh; /* preferred */
    max-height: 40vh;  /* fallback */
    overflow-y: auto;
    padding-right: 6px; /* avoid scrollbar overlapping text */
}

/* Line clamp utility for text truncation */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive breakpoints for smaller screens */
@media (max-width: 640px) {
    .treatment-options {
        max-height: 30vh;
    }
    
    /* Ensure buttons don't overflow on very small screens */
    .ant-btn-sm {
        font-size: 11px;
        padding: 2px 6px;
        min-width: 0;
    }
    
    /* Force flex items to respect minimum sizes */
    .flex-1.min-w-0 {
        min-width: 0;
        flex: 1 1 0%;
    }
}

/* Transition-group animations for doctor cards */
.doctor-list-enter-from,
.doctor-list-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.995);
}
.doctor-list-enter-active,
.doctor-list-leave-active {
    transition: opacity 280ms ease, transform 280ms ease;
}

/* Ensure each child participates in the stagger via inline transition-delay */
.doctor-card {
    will-change: opacity, transform;
}
</style>