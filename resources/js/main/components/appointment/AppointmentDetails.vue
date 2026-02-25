<template>
    <a-modal
        :open="visible"
        :title="getTreatmentTypeName(appointmentData.treatment_type) || 'Appointment Details'"
        @cancel="onClose"
        :footer="null"
        width="90%"
        maxWidth="1200px"
        height="auto"
        class="appointment-details-modal"
        centered
    >
        <div class="appointment-details">
            <!-- Header with appointment info and status -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        {{ getTreatmentTypeName(appointmentData.treatment_type) || 'Appointment' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        Appointment ID: {{ appointmentData.xid }} • {{ formatDate(appointmentData.appointment_date) }} at {{ formatTime(appointmentData.appointment_date) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a-tag :color="getStatusColor(appointmentData.status)" class="text-sm px-3 py-1">
                        {{ appointmentData.status }}
                    </a-tag>
                </div>
            </div>

            <!-- Main content - 3 column layout for better space utilization -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Column 1: Patient Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <UserOutlined class="text-gray-600 mr-2" />
                        <h4 class="font-medium text-gray-900">Patient Information</h4>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <img 
                                :src="appointmentData.patient?.user?.profile_image_url || '/images/user.png'" 
                                alt="Patient"
                                class="w-8 h-8 rounded-full mr-3"
                            />
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ appointmentData.patient?.user?.name }} {{ appointmentData.patient?.user?.last_name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Phone:</strong> {{ appointmentData.patient?.user?.phone || 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ appointmentData.patient?.user?.email || 'N/A' }}</p>
                            <p><strong>Age:</strong> {{ calculateAge(appointmentData.patient?.user?.date_of_birth) || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Doctor Information -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <MedicineBoxOutlined class="text-gray-600 mr-2" />
                        <h4 class="font-medium text-gray-900">Doctor Information</h4>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <img 
                                :src="appointmentData.doctor?.user?.profile_image_url || '/images/user.png'" 
                                alt="Doctor"
                                class="w-8 h-8 rounded-full mr-3"
                            />
                            <div>
                                <p class="font-medium text-gray-900">
                                    Dr. {{ appointmentData.doctor?.user?.name }} {{ appointmentData.doctor?.user?.last_name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><strong>Phone:</strong> {{ appointmentData.doctor?.user?.phone || 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ appointmentData.doctor?.user?.email || 'N/A' }}</p>
                            <div v-if="appointmentData.doctor?.specialties?.length">
                                <p class="mb-0"><strong>Specialties:</strong></p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <a-tag v-for="specialty in appointmentData.doctor.specialties" :key="specialty.id" size="small">
                                        {{ specialty.name }}
                                    </a-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Appointment Details -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <CalendarOutlined class="text-gray-600 mr-2" />
                        <h4 class="font-medium text-gray-900">Appointment Details</h4>
                    </div>
                    <div class="space-y-2 text-sm">
                        <p><strong>Date:</strong> {{ formatDate(appointmentData.appointment_date) }}</p>
                        <p><strong>Time:</strong> {{ formatTime(appointmentData.appointment_date) }} - {{ calculateEndTime(appointmentData.appointment_date, appointmentData.duration) }}</p>
                        <p><strong>Duration:</strong> {{ appointmentData.duration || 'N/A' }} minutes</p>
                        <p><strong>Treatment:</strong> {{ getTreatmentTypeName(appointmentData.treatment_type) || 'N/A' }}</p>
                        <p v-if="getTreatmentTypeDuration(appointmentData.treatment_type)">
                            <strong>Standard Duration:</strong> {{ getTreatmentTypeDuration(appointmentData.treatment_type) }} minutes
                        </p>
                        <p v-if="getTreatmentTypeCost(appointmentData.treatment_type)">
                            <strong>Cost:</strong> ${{ getTreatmentTypeCost(appointmentData.treatment_type) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Second Row - Room & Status Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Room Information -->
                <div v-if="appointmentData.room" class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <HomeOutlined class="text-gray-600 mr-2" />
                        <h4 class="font-medium text-gray-900">Room Information</h4>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                        <p><strong>Room:</strong> {{ appointmentData.room.name }}</p>
                        <p><strong>Floor:</strong> {{ appointmentData.room.floor }}</p>
                        <p><strong>Capacity:</strong> {{ appointmentData.room.capacity }}</p>
                        <p v-if="appointmentData.room.room_no"><strong>Room No:</strong> {{ appointmentData.room.room_no }}</p>
                        <p><strong>Status:</strong> 
                            <a-tag :color="appointmentData.room.status === 'Available' ? 'green' : 'red'" size="small">
                                {{ appointmentData.room.status }}
                            </a-tag>
                        </p>
                    </div>
                    <div v-if="appointmentData.room.equipment?.length" class="mt-2">
                        <p class="text-sm mb-1"><strong>Equipment:</strong></p>
                        <div class="flex flex-wrap gap-1">
                            <a-tag v-for="equipment in appointmentData.room.equipment" :key="equipment" size="small">
                                {{ equipment }}
                            </a-tag>
                        </div>
                    </div>
                    <div v-if="appointmentData.room.notes" class="mt-2">
                        <p class="text-sm"><strong>Notes:</strong> {{ appointmentData.room.notes }}</p>
                    </div>
                </div>

                <!-- Status & Notes -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <FileTextOutlined class="text-gray-600 mr-2" />
                        <h4 class="font-medium text-gray-900">Status & Notes</h4>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">Status</p>
                            <a-tag :color="getStatusColor(appointmentData.status)" class="text-sm">
                                {{ appointmentData.status }}
                            </a-tag>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">Notes</p>
                            <div class="bg-white rounded border p-3 min-h-[60px]">
                                <p class="text-sm text-gray-600">
                                    {{ appointmentData.treatment_details || 'No notes available for this appointment.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer with timestamps -->
            <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200 text-xs text-gray-500">
                <p>Created: {{ formatDateTime(appointmentData.created_at) }}</p>
                <p>Last updated: {{ formatDateTime(appointmentData.updated_at) }}</p>
            </div>
        </div>
    </a-modal>
</template>

<script>
import { defineComponent } from "vue";
import {
    CloseOutlined,
    UserOutlined,
    MedicineBoxOutlined,
    CalendarOutlined,
    HomeOutlined,
    FileTextOutlined,
} from "@ant-design/icons-vue";
import common from "../../../common/composable/common";

export default defineComponent({
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        appointmentData: {
            type: Object,
            default: () => ({}),
        },
    },
    components: {
        CloseOutlined,
        UserOutlined,
        MedicineBoxOutlined,
        CalendarOutlined,
        HomeOutlined,
        FileTextOutlined,
    },
    setup(props, { emit }) {
        const { formatDate, formatTime, dayjsObject, formatDateTime } = common();

        const onClose = () => {
            emit("closed");
        };

        // Calculate end time based on start time and duration
        const calculateEndTime = (startTime, durationMinutes) => {
            const startDateTime = dayjsObject(startTime);
            const endDateTime = startDateTime.add(parseInt(durationMinutes) || 0, 'minute');
            return formatTime(endDateTime);
        };

        // Calculate age from date of birth
        const calculateAge = (dateOfBirth) => {
            if (!dateOfBirth) return null;
            const today = new Date();
            const birthDate = new Date(dateOfBirth);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age;
        };

        // Function to determine status color for tags
        const getStatusColor = (status) => {
            const statusColors = {
                'pending': 'orange',
                'confirmed': 'green',
                'completed': 'blue',
                'cancelled': 'red',
                'no-show': 'gray'
            };

            return statusColors[status] || 'default';
        };

        // Helper functions to handle treatment_type data (could be object or array)
        const getTreatmentTypeName = (treatmentType) => {
            if (!treatmentType) return null;
            if (Array.isArray(treatmentType) && treatmentType.length > 0) {
                return treatmentType[0].name;
            }
            return treatmentType.name;
        };

        const getTreatmentTypeDuration = (treatmentType) => {
            if (!treatmentType) return null;
            if (Array.isArray(treatmentType) && treatmentType.length > 0) {
                return treatmentType[0].duration_minutes;
            }
            return treatmentType.duration_minutes;
        };

        const getTreatmentTypeCost = (treatmentType) => {
            if (!treatmentType) return null;
            if (Array.isArray(treatmentType) && treatmentType.length > 0) {
                return treatmentType[0].cost;
            }
            return treatmentType.cost;
        };

        return {
            onClose,
            formatDate,
            formatTime,
            formatDateTime,
            calculateEndTime,
            calculateAge,
            getStatusColor,
            getTreatmentTypeName,
            getTreatmentTypeDuration,
            getTreatmentTypeCost,
        };
    },
});
</script>

<style scoped>
.appointment-details-modal :deep(.ant-modal-header) {
    border-bottom: 1px solid #f0f0f0;
    padding: 16px 24px;
}

.appointment-details-modal :deep(.ant-modal-body) {
    padding: 20px;
    max-height: 85vh;
    overflow-y: auto;
}

.appointment-details-modal :deep(.ant-modal-content) {
    max-width: 1200px;
    margin: 0 auto;
}

.appointment-details {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Reduce some spacing to fit more content */
.appointment-details p {
    margin-bottom: 4px;
}

.appointment-details .mb-6 {
    margin-bottom: 16px;
}

/* Ensure modal doesn't take more than 85% of viewport height */
@media (min-height: 800px) {
    .appointment-details-modal :deep(.ant-modal-body) {
        max-height: 80vh;
    }
}
</style>
