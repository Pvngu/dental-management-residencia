<template>
    <a-modal
        :open="visible"
        :title="modalTitle"
        :width="modalWidth"
        :footer="null"
        @cancel="onClose"
        :body-style="{ padding: '24px', maxHeight: '90vh', overflow: 'auto' }"
        centered
    >
        <div class="w-full">
            <!-- Search and Filter Row -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <a-input-search
                    v-model:value="searchQuery"
                    placeholder="Search doctors by name or specialty..."
                    @search="handleSearch"
                    class="flex-1 min-w-[200px]"
                />

                <a-select
                    v-model:value="selectedDepartment"
                    placeholder="Filter by Department"
                    style="width: 220px"
                    :allowClear="true"
                    @change="filterDoctors"
                    class="md:ml-3"
                >
                    <a-select-option :value="''">All Departments</a-select-option>
                    <a-select-option
                        v-for="dept in departments"
                        :key="dept.xid || dept.id || dept.name"
                        :value="dept.id ?? dept.xid ?? dept.name"
                    >
                        {{ dept.name }}
                    </a-select-option>
                </a-select>
            </div>

            <!-- When status is editable, show an edit icon next to the badge (no hint text) -->

            <!-- Loading Skeleton -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div
                    v-for="n in 12"
                    :key="`skeleton-${n}`"
                    class="bg-white rounded-xl p-4 shadow-md flex flex-col h-full animate-pulse"
                >
                    <a-skeleton :active="true" :avatar="true" :paragraph="{ rows: 3 }" />
                </div>
            </div>

            <!-- Doctors Grid -->
            <div v-else-if="filteredDoctors.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div
                    v-for="doctor in filteredDoctors"
                    :key="doctor.id"
                    :class="[
                        'bg-white rounded-xl p-4 shadow-md flex flex-col h-full transform transition-transform duration-150 ease-in-out',
                        !doctor.is_available ? 'opacity-60 cursor-not-allowed' : 'hover:-translate-y-1 hover:shadow-lg',
                        getCardClass(doctor.status)
                    ]"
                >
                    <!-- Unavailability Banner -->
                    <div v-if="!doctor.is_available" class="mb-3 p-2 bg-red-50 border border-red-200 rounded-md">
                        <div class="flex items-center gap-2 text-red-700 text-sm font-semibold">
                            <ExclamationCircleOutlined />
                            <span>Unavailable</span>
                        </div>
                        <ul v-if="doctor.unavailability_reasons && doctor.unavailability_reasons.length" class="mt-1 ml-4 text-xs text-red-600 space-y-1">
                            <li v-for="(reason, index) in doctor.unavailability_reasons" :key="index">
                                {{ reason }}
                            </li>
                        </ul>
                    </div>

                    <!-- Availability Banner (for available doctors) -->
                    <div v-else-if="doctor.is_available && doctor.is_clocked_in" class="mb-3 p-2 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex items-center gap-2 text-green-700 text-sm font-semibold">
                            <ClockCircleOutlined />
                            <span>Clocked In & Available</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="relative">
                            <a-avatar
                                :size="64"
                                :src="doctor.profile_image_url"
                                class="flex-shrink-0"
                                :class="{ 'grayscale': !doctor.is_available }"
                            >
                                {{ doctor.name.charAt(0) }}
                            </a-avatar>
                            <!-- Clock in badge -->
                            <div
                                v-if="doctor.is_clocked_in"
                                class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center border-2 border-white"
                                :title="'Clocked In'"
                            >
                                <ClockCircleOutlined class="text-white text-xs" />
                            </div>
                        </div>

                        <div class="flex-1">
                            <h3 class="m-0 text-base font-semibold">{{ doctor.name }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ doctor.specialist }}</p>

                            <div class="flex gap-2 mt-2 text-sm text-gray-500 items-center">
                                <span v-if="doctor.department" class="flex items-center gap-1">
                                    <TeamOutlined /> {{ doctor.department }}
                                </span>
                                <span v-if="doctor.practice_id" class="flex items-center gap-1">
                                    <NumberOutlined /> {{ doctor.practice_id }}
                                </span>
                            </div>

                            <!-- Show Clinics -->
                            <div v-if="doctor.clinics && doctor.clinics.length" class="mt-2 text-xs text-gray-400">
                                <span class="font-semibold">Clinics:</span>
                                <span v-for="(clinic, idx) in doctor.clinics" :key="clinic.id">
                                    {{ clinic.name }}<span v-if="idx < doctor.clinics.length - 1">, </span>
                                </span>
                            </div>
                        </div>

                        <div class="ml-2 flex items-center gap-2">
                            <div v-if="canChangeStatus && doctor.is_available">
                                <a-dropdown :trigger="['click']" placement="bottomRight">
                                    <template #default>
                                        <div class="flex items-center gap-2 cursor-pointer">
                                            <a-badge
                                                :status="getStatusType(doctor.status)"
                                                :text="formatStatus(doctor.status)"
                                                class="hover:opacity-80"
                                            />
                                            <EditOutlined v-if="canChangeStatus" class="text-gray-400" />
                                            <LoadingOutlined v-if="statusUpdating === doctor.xid" />
                                        </div>
                                    </template>
                                    <template #overlay>
                                        <a-menu @click="({ key }) => handleStatusChange(doctor, key)">
                                            <a-menu-item key="available" :disabled="doctor.status === 'available'">
                                                <a-badge status="success" text="Available" />
                                            </a-menu-item>
                                            <a-menu-item key="busy" :disabled="doctor.status === 'busy'">
                                                <a-badge status="error" text="Busy" />
                                            </a-menu-item>
                                            <a-menu-item key="break" :disabled="doctor.status === 'break'">
                                                <a-badge status="warning" text="On Break" />
                                            </a-menu-item>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </div>

                            <div v-else>
                                <a-badge
                                    :status="getStatusType(doctor.status)"
                                    :text="formatStatus(doctor.status)"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex-1">
                        <div v-if="doctor.appointment_charge || doctor.missed_appointment_charge" class="space-y-1 text-sm">
                            <div v-if="doctor.appointment_charge" class="flex justify-between items-center">
                                <small>Appointment:</small>
                                <strong>${{ doctor.appointment_charge }}</strong>
                            </div>
                            <div v-if="doctor.missed_appointment_charge" class="flex justify-between items-center">
                                <small>Missed:</small>
                                <strong class="text-red-600">${{ doctor.missed_appointment_charge }}</strong>
                            </div>
                        </div>

                        <div v-if="doctor.last_attendance" class="mt-2 text-xs text-gray-400 flex items-center gap-1">
                            <ClockCircleOutlined />
                            {{ doctor.last_attendance.status }} at {{ formatTime(doctor.last_attendance.clock_time) }}
                        </div>

                        <div v-if="doctor.description" class="mt-2 text-sm">
                            <a-typography-paragraph
                                :ellipsis="{ rows: 2, expandable: true, symbol: 'more' }"
                                :content="doctor.description"
                            />
                        </div>
                    </div>

                    <div class="mt-4">
                        <a-space direction="vertical" style="width: 100%">
                            <a-button
                                type="primary"
                                block
                                :disabled="!doctor.is_available"
                                @click="bookAppointment(doctor)"
                            >
                                <template #icon>
                                    <CalendarOutlined />
                                </template>
                                {{ doctor.is_available ? 'Book Appointment' : 'Unavailable' }}
                            </a-button>
                            <a-button block @click="messageDoctor(doctor)" :disabled="!doctor.is_available">
                                <template #icon>
                                    <MessageOutlined />
                                </template>
                                Message
                            </a-button>
                        </a-space>
                    </div>
                </div>
            </div>

            <a-empty v-else description="No doctors found">
                <template #image>
                    <MedicineBoxOutlined style="font-size: 48px; color: #d9d9d9" />
                </template>
            </a-empty>
        </div>

        <!-- Appointment Creation Modal -->
        <Appointment
            :visible="appointmentModalVisible"
            :formData="appointmentFormData"
            :autoSelectDoctor="true"
            :doctorId="appointmentFormData.doctor_id"
            :doctorInfo="selectedDoctor"
            @closed="onAppointmentModalClose"
            @addEditSuccess="onAppointmentSuccess"
        />
    </a-modal>
</template>

<script>
import { ref, computed, onMounted, watch, inject } from 'vue';
import {
    TeamOutlined,
    SafetyOutlined,
    IdcardOutlined,
    NumberOutlined,
    ClockCircleOutlined,
    CalendarOutlined,
    MedicineBoxOutlined,
    EditOutlined,
    MessageOutlined,
    LoadingOutlined,
    ExclamationCircleOutlined,
} from '@ant-design/icons-vue';
import { message, Modal } from 'ant-design-vue';
import common from '../../composable/common';
import Appointment from '../../../main/components/appointment/index.vue';

export default {
    name: 'AvailableDoctorsDrawer',
    components: {
        TeamOutlined,
        SafetyOutlined,
        IdcardOutlined,
        NumberOutlined,
        ClockCircleOutlined,
        CalendarOutlined,
        MedicineBoxOutlined,
        EditOutlined,
        MessageOutlined,
        LoadingOutlined,
        ExclamationCircleOutlined,
        Appointment,
    },
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: 'Available Doctors',
        },
    },
    emits: ['close', 'book-appointment', 'status-changed'],
    setup(props, { emit }) {
        const { permsArray } = common();
        const searchQuery = ref('');
        const selectedDepartment = ref('');
        const loading = ref(false);
        const statusUpdating = ref(null); // Track which doctor's status is being updated

        const departments = ref([]);

        const doctors = ref([]);

        // Appointment modal state
        const appointmentModalVisible = ref(false);
        const appointmentFormData = ref({
            doctor_id: null,
            patient_id: null,
            appointment_date: null,
            appointment_time: null,
            duration: 30,
            reason_visit: '',
            room_id: null,
            treatment_type_id: null,
            selectedDate: null,
            selectedTimeSlot: null,
            currentMonth: null,
            currentYear: null,
        });
        const selectedDoctor = ref(null);

        // Check if user can change doctor status
        const canChangeStatus = computed(() => {
            return permsArray.value.includes('doctors_status_edit') || permsArray.value.includes('admin');
        });

        const filteredDoctors = computed(() => {
            let result = Array.isArray(doctors.value) ? doctors.value.slice() : [];

            // Filter by department (support id, xid or name values)
            if (selectedDepartment.value !== '' && selectedDepartment.value !== null && selectedDepartment.value !== undefined) {
                const sel = selectedDepartment.value;

                result = result.filter((doctor) => {
                    // Direct numeric id match
                    if (doctor.doctor_department_id != null && doctor.doctor_department_id == sel) return true;

                    // x_ prefixed xid match (if backend provides it)
                    if (doctor.x_doctor_department_id != null && doctor.x_doctor_department_id == sel) return true;

                    // Direct department name match (case-insensitive)
                    if (doctor.department && typeof sel === 'string' && doctor.department.toLowerCase() === sel.toLowerCase()) return true;

                    // Fallback: if we have departments list try to resolve xid->id or id->xid or name
                    const deptMatch = (departments.value || []).find((d) => {
                        if (d == null) return false;
                        if (d.id != null && d.id == sel) return true;
                        if (d.xid != null && d.xid == sel) return true;
                        if (d.name && typeof sel === 'string' && d.name.toLowerCase() === sel.toLowerCase()) return true;
                        return false;
                    });

                    if (deptMatch) {
                        if (doctor.doctor_department_id != null && deptMatch.id != null && doctor.doctor_department_id == deptMatch.id) return true;
                        if (doctor.x_doctor_department_id != null && deptMatch.xid != null && doctor.x_doctor_department_id == deptMatch.xid) return true;
                        if (doctor.department && deptMatch.name && doctor.department.toLowerCase() === deptMatch.name.toLowerCase()) return true;
                    }

                    return false;
                });
            }

            // Filter by search query
            if (searchQuery.value) {
                const query = searchQuery.value.toLowerCase();
                result = result.filter(
                    (doctor) =>
                        doctor.name.toLowerCase().includes(query) ||
                        (doctor.specialist && doctor.specialist.toLowerCase().includes(query)) ||
                        (doctor.department && doctor.department.toLowerCase().includes(query))
                );
            }

            return result;
        });

        // Fetch doctors from API
        const fetchDoctors = async () => {
            loading.value = true;
            try {
                // TODO: Update endpoint when backend is ready
                const params = {
                    search: searchQuery.value || undefined,
                };

                // If a department is selected, try to send the most appropriate param
                if (selectedDepartment.value !== '' && selectedDepartment.value !== null && selectedDepartment.value !== undefined) {
                    const sel = selectedDepartment.value;
                    // numeric id
                    if (!isNaN(Number(sel)) && Number.isInteger(Number(sel))) {
                        params.department_id = Number(sel);
                    } else {
                        // send as xid (if backend supports) or name
                        params.department_xid = sel;
                    }
                }

                const response = await axiosAdmin.get('doctors/available', { params });

                doctors.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching doctors:', error);
                message.error('Failed to load doctors');
            } finally {
                loading.value = false;
            }
        };

        // Fetch departments from API
        const fetchDepartments = async () => {
            try {
                const response = await axiosAdmin.get('doctor-departments', {
                    params: {
                        fields: 'id,xid,name',
                    },
                });

                // Some API endpoints return { data: [...] } while others return array directly
                departments.value = response.data && response.data.data ? response.data.data : response.data;
            } catch (error) {
                console.error('Error fetching departments:', error);
            }
        };

        // Handle status change for a doctor (with confirmation)
        const handleStatusChange = async (doctor, newStatus) => {
            if (!canChangeStatus.value || doctor.status === newStatus) return;

            Modal.confirm({
                title: `Change status for ${doctor.name}`,
                content: `Are you sure you want to mark ${doctor.name} as "${formatStatus(newStatus)}"?`,
                okText: 'Yes, change',
                cancelText: 'Cancel',
                async onOk() {
                    statusUpdating.value = doctor.xid;
                    try {
                        const response = await axiosAdmin.post(`doctors/${doctor.xid}/status`, {
                            status: newStatus,
                        });

                        if (response.data) {
                            // Update the doctor's status locally
                            const doctorIndex = doctors.value.findIndex(d => d.xid === doctor.xid);
                            if (doctorIndex !== -1) {
                                doctors.value[doctorIndex].status = newStatus;
                            }

                            message.success(`${doctor.name}'s status changed to ${formatStatus(newStatus)}`);
                            emit('status-changed', { doctor, newStatus });
                        }
                    } catch (error) {
                        console.error('Error updating doctor status:', error);
                        message.error(`Failed to update ${doctor.name}'s status`);
                    } finally {
                        statusUpdating.value = null;
                    }
                }
            });
        };

        // Watch for modal visibility to fetch data
        watch(() => props.visible, (newVal) => {
            if (newVal) {
                fetchDoctors();
                fetchDepartments();
            }
        });

        onMounted(() => {
            fetchDepartments();
        });

        const getCardClass = (status) => {
            switch (status) {
                case 'available':
                    return 'border-l-4 border-green-500';
                case 'busy':
                    return 'border-l-4 border-red-500';
                case 'break':
                    return 'border-l-4 border-yellow-500';
                default:
                    return 'border-l-4 border-gray-400';
            }
        };

        const getStatusType = (status) => {
            switch (status) {
                case 'available':
                    return 'success';
                case 'busy':
                    return 'error';
                case 'break':
                    return 'warning';
                default:
                    return 'default';
            }
        };

        const formatStatus = (status) => {
            if (status === 'break') return 'On Break';
            return status.charAt(0).toUpperCase() + status.slice(1);
        };

        const formatTime = (datetime) => {
            const date = new Date(datetime);
            return date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        const handleSearch = () => {
            // Search is reactive through computed property
        };

        const filterDoctors = () => {
            // Filter is reactive through computed property
        };

        const bookAppointment = (doctor) => {
            selectedDoctor.value = doctor;
            // Pre-fill doctor information
            appointmentFormData.value = {
                doctor_id: doctor.xid || doctor.x_user_id,
                patient_id: null,
                appointment_date: null,
                appointment_time: null,
                duration: 30,
                reason_visit: '',
                room_id: null,
                treatment_type_id: null,
                selectedDate: null,
                selectedTimeSlot: null,
                currentMonth: new Date().getMonth(),
                currentYear: new Date().getFullYear(),
            };
            appointmentModalVisible.value = true;
        };

        const messageDoctor = (doctor) => {
            // TODO: Implement messaging functionality
            message.info(`Messaging feature coming soon for ${doctor.name}`);
        };

        const onClose = () => {
            emit('close');
        };

        const onAppointmentModalClose = () => {
            appointmentModalVisible.value = false;
            appointmentFormData.value = {
                doctor_id: null,
                patient_id: null,
                appointment_date: null,
                appointment_time: null,
                duration: 30,
                reason_visit: '',
                room_id: null,
                treatment_type_id: null,
                selectedDate: null,
                selectedTimeSlot: null,
                currentMonth: null,
                currentYear: null,
            };
            selectedDoctor.value = null;
        };

        const onAppointmentSuccess = () => {
            message.success(`Appointment booked successfully with ${selectedDoctor.value?.name || 'doctor'}`);
            onAppointmentModalClose();
            emit('book-appointment', selectedDoctor.value);
            // Optionally refresh the doctors list
            fetchDoctors();
        };

        const modalWidth = window.innerWidth >= 1200 ? 1400 : window.innerWidth >= 768 ? 900 : '90%';
        const modalTitle = props.title || 'Available Doctors';

        return {
            searchQuery,
            selectedDepartment,
            departments,
            doctors,
            filteredDoctors,
            loading,
            statusUpdating,
            canChangeStatus,
            getCardClass,
            getStatusType,
            formatStatus,
            formatTime,
            handleSearch,
            filterDoctors,
            handleStatusChange,
            bookAppointment,
            messageDoctor,
            onClose,
            modalWidth,
            modalTitle,
            appointmentModalVisible,
            appointmentFormData,
            selectedDoctor,
            onAppointmentModalClose,
            onAppointmentSuccess,
        };
    },
};
</script>
<style scoped>
.grayscale {
    filter: grayscale(100%);
}
</style>