<template>
    <a-modal
        :open="visible"
        :title="$t('doctors.doctor_information') || 'Doctor Information'"
        :width="modalWidth"
        @cancel="onClose"
        :footer="null"
        centered
        :bodyStyle="{ padding: modalPadding, maxHeight: '85vh', overflowY: 'auto' }"
    >
        <div v-if="loading" class="doctor-info-modal">
            <!-- Doctor Header Skeleton -->
            <div class="doctor-header mb-4 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-start gap-4">
                    <a-skeleton-avatar :active="true" :size="80" shape="circle" />
                    <div class="flex-1">
                        <a-skeleton :active="true" :paragraph="{ rows: 2 }" />
                    </div>
                </div>
            </div>

            <!-- Content Skeleton -->
            <a-row :gutter="[16, 12]">
                <a-col :xs="24" :sm="12" :lg="8">
                    <div class="bg-gray-50 p-2 sm:p-3 rounded-lg mb-2 sm:mb-3">
                        <a-skeleton :active="true" :paragraph="{ rows: 2 }" />
                    </div>
                </a-col>
                <a-col :xs="24" :sm="12" :lg="8">
                    <div class="bg-gray-50 p-2 sm:p-3 rounded-lg mb-2 sm:mb-3">
                        <a-skeleton :active="true" :paragraph="{ rows: 3 }" />
                    </div>
                </a-col>
                <a-col :xs="24" :sm="24" :lg="8">
                    <div class="bg-gray-50 p-2 sm:p-3 rounded-lg mb-2 sm:mb-3">
                        <a-skeleton :active="true" :paragraph="{ rows: 2 }" />
                    </div>
                </a-col>
            </a-row>
        </div>

        <div v-else-if="doctorInfo" class="doctor-info-modal">
                <!-- Responsive Doctor Header -->
                <div class="doctor-header mb-3 p-3 bg-blue-50 rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0">
                        <div class="flex items-center gap-3 flex-1">
                            <a-avatar
                                :size="isMobile ? 50 : 60"
                                class="bg-blue-500"
                                :src="doctorInfo.user?.profile_image_url"
                            />
                            <div class="flex-1">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-0 leading-tight">
                                    {{ doctorInfo.user?.name }} {{ doctorInfo.user?.last_name }}
                                </h3>
                                <p class="text-gray-600 text-xs sm:text-sm mb-1">
                                    {{ doctorInfo.designation || $t('doctors.doctor') }}
                                </p>
                                <a-tag
                                    :color="getAvailabilityColor(doctorInfo.current_status || 'available')"
                                    class="text-xs px-2 py-0"
                                    size="small"
                                >
                                    <template #icon>
                                        <div
                                            class="inline-block w-2 h-2 rounded-full mr-1"
                                            :style="{ backgroundColor: getAvailabilityDotColor(doctorInfo.current_status || 'available') }"
                                        ></div>
                                    </template>
                                    {{ getAvailabilityText(doctorInfo.current_status || 'available') }}
                                </a-tag>
                            </div>
                        </div>
                        
                        <!-- Quick Stats - Mobile friendly -->
                        <div class="text-center sm:text-right flex sm:block justify-between sm:justify-normal items-center sm:items-end gap-4 sm:gap-0">
                            <div class="flex-1 sm:flex-none">
                                <div class="text-xs text-gray-500 mb-1">{{ $t('appointments.todays_appointments') || "Today's Appointments" }}</div>
                                <div class="text-lg sm:text-xl font-bold text-blue-600">{{ appointmentsToday !== null ? appointmentsToday : 0 }}</div>
                            </div>
                            <div v-if="todaySchedule" class="text-xs text-gray-600">
                                <ClockCircleOutlined class="mr-1" />
                                <span class="block sm:inline">{{ todaySchedule.start_time }} - {{ todaySchedule.end_time }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Appointment (Compact layout) -->
                <div class="mb-3">
                    <a-alert
                        v-if="doctorInfo.current_appointment"
                        type="info"
                        show-icon
                    >
                        <template #message>
                            <div class="font-medium text-sm">{{ $t('appointments.currently_in_appointment') || 'Currently In Appointment' }}</div>
                        </template>
                        <template #description>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 mt-1 text-xs sm:text-sm">
                                <div class="flex items-center gap-2">
                                    <UserOutlined class="text-blue-500 text-xs" />
                                    <span class="font-medium">{{ $t('common.patient') || 'Patient' }}:</span>
                                    <span class="truncate">{{ doctorInfo.current_appointment.patient_name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClockCircleOutlined class="text-blue-500 text-xs" />
                                    <span class="font-medium">{{ $t('appointments.scheduled_time') || 'Scheduled' }}:</span>
                                    <span class="font-semibold text-xs sm:text-sm">{{ doctorInfo.current_appointment.start_time }} - {{ doctorInfo.current_appointment.end_time }}</span>
                                </div>
                                <div v-if="doctorInfo.current_appointment.room_name" class="flex items-center gap-2">
                                    <EnvironmentOutlined class="text-green-500 text-xs" />
                                    <span class="font-medium">{{ $t('common.room') || 'Room' }}:</span>
                                    <span>{{ doctorInfo.current_appointment.room_name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClockCircleOutlined class="text-orange-500 text-xs" />
                                    <span class="font-medium">{{ $t('appointments.elapsed_time') || 'Elapsed' }}:</span>
                                    <span class="font-semibold text-orange-600">{{ elapsedTime }}</span>
                                </div>
                                <div v-if="doctorInfo.current_appointment.treatment_type" class="flex items-center gap-2 sm:col-span-2">
                                    <MedicineBoxOutlined class="text-purple-500 text-xs" />
                                    <span class="font-medium">{{ $t('common.treatment') || 'Treatment' }}:</span>
                                    <span class="text-xs sm:text-sm">{{ doctorInfo.current_appointment.treatment_type }}</span>
                                </div>
                            </div>
                        </template>
                    </a-alert>
                </div>

                <!-- Responsive Information Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <!-- Contact Information -->
                    <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                        <div class="text-xs sm:text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <MailOutlined class="text-gray-500" />
                            {{ $t('doctors.contact_information') || 'Contact' }}
                        </div>
                        <div class="space-y-1 text-xs sm:text-sm">
                            <div v-if="doctorInfo.user?.email" class="flex items-center gap-2 text-gray-600">
                                <MailOutlined class="text-gray-400 text-xs" />
                                <span class="truncate">{{ doctorInfo.user.email }}</span>
                            </div>
                            <div v-if="doctorInfo.user?.phone" class="flex items-center gap-2 text-gray-600">
                                <PhoneOutlined class="text-gray-400 text-xs" />
                                <span>{{ doctorInfo.user.phone }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                        <div class="text-xs sm:text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <MedicineBoxOutlined class="text-gray-500" />
                            {{ $t('doctors.professional_information') || 'Professional' }}
                        </div>
                        <div class="space-y-1 sm:space-y-1.5 text-xs sm:text-sm">
                            <div v-if="doctorInfo.doctor_department">
                                <a-tag color="blue" size="small">
                                    {{ doctorInfo.doctor_department.name }}
                                </a-tag>
                            </div>
                            <div v-if="doctorInfo.specialties && doctorInfo.specialties.length > 0">
                                <div class="flex gap-1 flex-wrap">
                                    <a-tag
                                        v-for="specialty in doctorInfo.specialties"
                                        :key="specialty.xid"
                                        color="cyan"
                                        size="small"
                                    >
                                        {{ specialty.name }}
                                    </a-tag>
                                </div>
                            </div>
                            <div v-if="doctorInfo.qualification" class="text-xs text-gray-600">
                                <strong>{{ $t('doctors.qualification') || 'Qualification' }}:</strong> {{ doctorInfo.qualification }}
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Summary -->
                    <div class="bg-gray-50 p-2 sm:p-3 rounded-lg sm:col-span-2 lg:col-span-1">
                        <div class="text-xs sm:text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <ClockCircleOutlined class="text-gray-500" />
                            {{ $t('doctors.todays_schedule') || 'Schedule' }}
                        </div>
                        <div class="space-y-1 text-xs sm:text-sm">
                            <div v-if="todaySchedule" class="flex items-center gap-2 text-gray-600">
                                <ClockCircleOutlined class="text-blue-500 text-xs" />
                                <span class="font-medium text-xs sm:text-sm">
                                    {{ todaySchedule.start_time }} - {{ todaySchedule.end_time }}
                                </span>
                            </div>
                            <div v-else class="text-gray-400 italic flex items-center gap-2 text-xs">
                                <ClockCircleOutlined />
                                {{ $t('doctors.no_schedule_today') || 'No schedule' }}
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <CalendarOutlined class="text-purple-500 text-xs" />
                                <span class="font-semibold">{{ appointmentsToday !== null ? appointmentsToday : 0 }}</span>
                                <span class="text-xs">{{ $t('appointments.appointments') || 'appointments' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </a-modal>
</template>

<script setup>
import { ref, watch, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    UserOutlined,
    PhoneOutlined,
    MailOutlined,
    EnvironmentOutlined,
    MedicineBoxOutlined,
    ClockCircleOutlined,
    CalendarOutlined,
} from '@ant-design/icons-vue';
import { message } from 'ant-design-vue';

const { t } = useI18n();

// Responsive computed properties
const isMobile = computed(() => {
    return window.innerWidth < 640; // sm breakpoint
});

const modalWidth = computed(() => {
    if (window.innerWidth < 640) return '95%'; // Mobile
    if (window.innerWidth < 1024) return '90%'; // Tablet  
    return 1100; // Desktop
});

const modalPadding = computed(() => {
    return window.innerWidth < 640 ? '12px' : '16px';
});

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    doctor: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:visible', 'closed']);

const loading = ref(false);
const doctorInfo = ref(null);
const todaySchedule = ref(null);
const appointmentsToday = ref(null);
const elapsedTimeSeconds = ref(0);
let timerInterval = null;

// Computed property for formatting elapsed time
const elapsedTime = computed(() => {
    if (elapsedTimeSeconds.value === 0) return '0s';
    
    const hours = Math.floor(elapsedTimeSeconds.value / 3600);
    const minutes = Math.floor((elapsedTimeSeconds.value % 3600) / 60);
    const seconds = elapsedTimeSeconds.value % 60;
    
    const parts = [];
    if (hours > 0) parts.push(`${hours}h`);
    if (minutes > 0) parts.push(`${minutes}m`);
    if (seconds > 0 || parts.length === 0) parts.push(`${seconds}s`);
    
    return parts.join(' ');
});

// Function to start the elapsed time counter
const startElapsedTimeCounter = () => {
    // Clear any existing interval
    if (timerInterval) {
        clearInterval(timerInterval);
    }
    
    // Calculate initial elapsed time
    if (doctorInfo.value?.current_appointment?.in_progress_datetime) {
        const inProgressDate = new Date(doctorInfo.value.current_appointment.in_progress_datetime);
        const now = new Date();
        
        // Calculate elapsed seconds, ensure it's never negative
        const calculatedSeconds = Math.floor((now - inProgressDate) / 1000);
        elapsedTimeSeconds.value = Math.max(0, calculatedSeconds);
        
        // Update every second
        timerInterval = setInterval(() => {
            elapsedTimeSeconds.value++;
        }, 1000);
    } else {
        elapsedTimeSeconds.value = 0;
    }
};

// Function to stop the elapsed time counter
const stopElapsedTimeCounter = () => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    elapsedTimeSeconds.value = 0;
};

// Watch for modal visibility and fetch doctor details only when modal opens
// This prevents double calls by consolidating both doctor and visible watchers
watch(
    () => props.visible,
    async (newVisible) => {
        if (newVisible && props.doctor) {
            await fetchDoctorDetails(props.doctor);
        } else {
            // Stop timer when modal closes
            stopElapsedTimeCounter();
        }
    }
);

// Clean up interval on component unmount
onUnmounted(() => {
    stopElapsedTimeCounter();
    // Remove resize listener if added
    window.removeEventListener('resize', updateResponsive);
});

// Update responsive values on window resize
const updateResponsive = () => {
    // Force reactivity update by triggering computed dependencies
    modalWidth.value;
    modalPadding.value;
    isMobile.value;
};

// Add resize listener for responsive updates
window.addEventListener('resize', updateResponsive);

const fetchDoctorDetails = async (doctor) => {
    loading.value = true;
    try {
        // Use the dedicated doctor info endpoint
        if (doctor.xid) {
            const response = await axiosAdmin.get(`doctors/${doctor.xid}/info`);
            
            // Set doctor info from the API response
            doctorInfo.value = {
                ...response.data,
                // API returns properly formatted data with all relationships
                doctorDepartment: response.data.doctor_department,
            };

            // Set today's schedule from API response
            if (response.data.today_schedule) {
                todaySchedule.value = response.data.today_schedule;
            } else {
                todaySchedule.value = null;
            }

            // Set today's appointments count from API response
            if (response.data.today_appointments_count !== undefined) {
                appointmentsToday.value = response.data.today_appointments_count;
            } else {
                appointmentsToday.value = null;
            }

            // Start elapsed time counter if appointment is in progress
            if (doctorInfo.value.current_appointment) {
                startElapsedTimeCounter();
            } else {
                stopElapsedTimeCounter();
            }
        } else {
            // Fallback to prop data if no xid
            doctorInfo.value = {
                ...doctor,
                current_status: doctor.current_status || 'available'
            };
            todaySchedule.value = null;
            appointmentsToday.value = null;
        }
    } catch (error) {
        console.error('Error fetching doctor details:', error);
        message.error(t('common.error_fetching_data') || 'Error fetching doctor details');
        // Fallback to prop data on error
        doctorInfo.value = {
            ...doctor,
            current_status: doctor.current_status || 'available'
        };
        todaySchedule.value = null;
        appointmentsToday.value = null;
    } finally {
        loading.value = false;
    }
};

const fetchTodaySchedule = async (doctorXid) => {
    // Removed - now fetched directly in getDoctorInfo endpoint
};

const fetchTodayAppointmentCount = async (doctorXid) => {
    // Removed - now fetched directly in getDoctorInfo endpoint
};

const getAvailabilityColor = (status) => {
    const colors = {
        available: 'green',
        busy: 'orange',
        in_surgery: 'red',
        on_break: 'blue',
        off_duty: 'default',
    };
    return colors[status] || 'default';
};

const getAvailabilityDotColor = (status) => {
    const colors = {
        available: '#52c41a',
        busy: '#faad14',
        in_surgery: '#ff4d4f',
        on_break: '#1890ff',
        off_duty: '#d9d9d9',
    };
    return colors[status] || '#d9d9d9';
};

const getAvailabilityText = (status) => {
    const texts = {
        available: t('doctors.available') || 'Available',
        busy: t('doctors.busy') || 'Busy',
        in_surgery: t('doctors.in_surgery') || 'In Surgery',
        on_break: t('doctors.on_break') || 'On Break',
        off_duty: t('doctors.off_duty') || 'Off Duty',
    };
    return texts[status] || t('doctors.unknown') || 'Unknown';
};

const onClose = () => {
    stopElapsedTimeCounter();
    emit('update:visible', false);
    emit('closed');
};
</script>

<style scoped>
.space-y-1 > * + * {
    margin-top: 0.25rem;
}

.space-y-2 > * + * {
    margin-top: 0.5rem;
}

.space-y-3 > * + * {
    margin-top: 0.75rem;
}

:deep(.ant-card-head) {
    background-color: #fafafa;
}

:deep(.ant-statistic-content) {
    font-size: 1.5rem;
}

:deep(.ant-skeleton) {
    width: 100%;
}

:deep(.ant-skeleton-avatar) {
    width: 80px;
    height: 80px;
}

.mb-4 {
    margin-bottom: 1rem;
}
</style>
