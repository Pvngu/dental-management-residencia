<template>
    <!-- Loading Skeleton -->
    <a-skeleton
        v-if="internalLoading"
        active
        :paragraph="{ rows: 3 }"
        class="bg-white p-3 sm:p-4 rounded-lg shadow-sm mt-3 mb-4"
    />

    <div
        v-else-if="hasAppointmentToday"
        class="bg-white p-3 sm:p-4 rounded-lg shadow-sm mt-3 mb-4"
    >
        <!-- Header for multiple appointments -->
        <div
            v-if="allAppointments.length > 1"
            class="mb-4 pb-2 border-b border-gray-200"
        >
            <h3 class="text-base font-semibold text-gray-700">
                {{
                    $t("appointments.todays_appointments") ||
                    "Today's Appointments"
                }}
                ({{ allAppointments.length }})
            </h3>
        </div>

        <!-- Display all appointments -->
        <div
            v-for="(appointment, index) in allAppointments"
            :key="appointment.xid"
            :class="{
                'mb-4 pb-4': index < allAppointments.length - 1,
                'border-b border-gray-200': index < allAppointments.length - 1,
                'bg-blue-50 -mx-3 sm:-mx-4 px-3 sm:px-4 py-3 rounded':
                    appointment.xid === currentAppointment?.xid &&
                    allAppointments.length > 1,
            }"
        >
            <div
                class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-3 lg:gap-4"
            >
                <div
                    class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-2 sm:gap-3 lg:gap-4 flex-1"
                >
                    <div class="flex items-center gap-2 flex-wrap">
                        <a-tag
                            :color="getStatusColor(appointment)"
                            class="text-base sm:text-lg px-2 sm:px-3 py-1 whitespace-nowrap"
                        >
                            <ClockCircleOutlined />
                            {{ getStatusText(appointment) }}
                        </a-tag>
                        <span
                            v-if="
                                appointment.xid === currentAppointment?.xid &&
                                allAppointments.length > 1
                            "
                            class="text-xs bg-blue-600 text-white px-2 py-1 rounded font-medium"
                        >
                            {{ $t("appointments.current") || "Current" }}
                        </span>
                    </div>
                    <div
                        v-if="
                            appointment.checkin_datetime ||
                            appointment.arrive_datetime
                        "
                        class="text-gray-600 text-sm sm:text-base"
                    >
                        <span class="font-medium sm:font-normal"
                            >{{ $t("patients.checked_in_at") }}:</span
                        >
                        <span class="block sm:inline mt-1 sm:mt-0">{{
                            formatDate(
                                appointment.checkin_datetime ||
                                    appointment.arrive_datetime,
                            )
                        }}</span>
                    </div>
                    <div
                        v-if="appointment.appointment_date"
                        class="text-gray-600 text-sm sm:text-base"
                    >
                        <ClockCircleOutlined />
                        <span class="font-medium sm:font-normal ml-2"
                            >{{ $t("appointments.time") || "Time" }}:
                        </span>
                        <span class="block sm:inline mt-1 sm:mt-0">{{
                            getAppointmentTime(appointment.appointment_date)
                        }}</span>
                    </div>
                    <div
                        v-if="appointment.room"
                        class="text-gray-600 text-sm sm:text-base flex items-center flex-wrap"
                    >
                        <HomeOutlined />
                        <span class="font-medium sm:font-normal ml-2"
                            >{{ $t("patients.assigned_room") }}:
                        </span>
                        <span>{{ appointment.room.name }}</span>
                        <EditOutlined
                            v-if="
                                appointment.xid === currentAppointment?.xid &&
                                getStatusForAppointment(appointment) ===
                                    'checked_in'
                            "
                            @click="handleSelectRoom"
                            class="cursor-pointer text-blue-500 hover:text-blue-700 ml-1"
                            style="font-size: 14px"
                        />
                    </div>
                    <div
                        v-else-if="
                            !appointment.room &&
                            appointment.xid === currentAppointment?.xid &&
                            getStatusForAppointment(appointment) ===
                                'checked_in'
                        "
                        class="text-gray-600 text-sm sm:text-base flex items-center gap-1 flex-wrap"
                    >
                        <HomeOutlined />
                        <span class="font-medium sm:font-normal"
                            >{{ $t("patients.assigned_room") }}:</span
                        >
                        <span class="text-red-400 italic">{{
                            $t("patients.no_room")
                        }}</span>
                        <EditOutlined
                            @click="handleSelectRoom"
                            class="cursor-pointer text-blue-500! hover:text-blue-700! ml-1"
                            style="font-size: 14px"
                        />
                    </div>
                </div>

                <!-- Actions - Only show for current appointment -->
                <div
                    v-if="appointment.xid === currentAppointment?.xid"
                    class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto lg:w-auto"
                >
                    <!-- Reload Button -->
                    <a-button
                        @click="handleReload"
                        :loading="internalLoading || loading"
                        class="w-full sm:w-auto"
                    >
                        <template #icon><ReloadOutlined /></template>
                    </a-button>
                    <!-- Show View button if prescription exists -->
                    <a-button
                        v-if="
                            appointmentPrescription &&
                            (permsArray.includes('prescriptions_view') ||
                                permsArray.includes('admin'))
                        "
                        type="default"
                        @click.stop="handleViewPrescription"
                        :loading="loading"
                        class="w-full sm:w-auto"
                    >
                        <template #icon><MedicineBoxOutlined /></template>
                        {{ $t("prescriptions.view_prescription") }}
                    </a-button>
                    <!-- Show Create button if no prescription -->
                    <a-button
                        v-else-if="
                            !appointmentPrescription &&
                            (permsArray.includes('prescriptions_create') ||
                                permsArray.includes('admin'))
                        "
                        type="default"
                        @click="handleCreatePrescription"
                        :loading="loading"
                        class="w-full sm:w-auto"
                    >
                        <template #icon><MedicineBoxOutlined /></template>
                        {{ $t("prescriptions.add") }}
                    </a-button>
                    <a-button
                        v-if="getNextStatus(appointment)"
                        type="primary"
                        @click="toggleAppointmentStatus"
                        :loading="loading"
                        :style="appointmentActionStyle"
                        class="w-full sm:w-auto"
                    >
                        {{ appointmentActionLabel }}
                    </a-button>
                </div>
            </div>

            <!-- Appointment Details -->
            <div
                v-if="
                    appointment.treatment_details ||
                    appointment.duration ||
                    appointment.notes
                "
                class="mt-3 pt-3 border-t border-gray-200"
            >
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3 text-xs sm:text-sm text-gray-600"
                >
                    <div
                        v-if="appointment.treatment_details"
                        class="col-span-1 sm:col-span-2 lg:col-span-3"
                    >
                        <strong class="block sm:inline"
                            >{{ $t("appointments.treatment_details") }}:
                        </strong>
                        <span class="block sm:inline mt-1 sm:mt-0">{{
                            appointment.treatment_details
                        }}</span>
                    </div>
                    <div v-if="appointment.duration">
                        <strong class="block sm:inline"
                            >{{ $t("appointments.duration") }}:
                        </strong>
                        <span class="block sm:inline mt-1 sm:mt-0"
                            >{{ appointment.duration }}
                            {{ $t("common.minutes") }}</span
                        >
                    </div>
                    <div
                        v-if="appointment.notes"
                        class="col-span-1 sm:col-span-2 lg:col-span-3"
                    >
                        <strong class="block sm:inline"
                            >{{ $t("appointments.notes") }}:
                        </strong>
                        <span class="block sm:inline mt-1 sm:mt-0">{{
                            appointment.notes
                        }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationModal
        v-model:open="modalVisible"
        :title="modalTitle"
        :action="modalAction"
        :patient="modalPatient"
        @confirm="onConfirm"
        @cancel="onCancel"
    />
</template>

<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";
import {
    CalendarOutlined,
    ClockCircleOutlined,
    HomeOutlined,
    EditOutlined,
    MedicineBoxOutlined,
    ReloadOutlined,
    DownOutlined,
    FileOutlined,
    UserOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import { notification } from "ant-design-vue";
import ConfirmationModal from "../../../../components/appointment/ConfirmationModal.vue";

const axiosAdmin = window.axiosAdmin;
const echo = window.Echo;

const props = defineProps({
    patientId: {
        type: String,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "selectRoom",
    "appointmentUpdated",
    "update:loading",
    "createPrescription",
    "viewPrescription",
    "editPrescription",
]);

const { t } = useI18n();
const { formatDate, permsArray, user } = common();

// Helper function to extract time from appointment_date
const getAppointmentTime = (appointmentDate) => {
    if (!appointmentDate) return "";
    const date = new Date(appointmentDate);
    return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
};

const internalLoading = ref(false);
const todayAppointments = ref([]);

// Track appointments being updated locally to skip notifications
const updatingLocally = ref(new Set());

// Fetch all appointments for today
const fetchNextAppointment = async () => {
    try {
        internalLoading.value = true;
        const response = await axiosAdmin.get(
            `patients/${props.patientId}/next-appointment`,
        );
        todayAppointments.value = response.data.appointments || [];
    } catch (error) {
        console.error("Error fetching appointments:", error);
        if (error.response?.status !== 404) {
            notification.error({
                message: t("common.error"),
                description: "Failed to fetch appointments",
            });
        }
        todayAppointments.value = [];
    } finally {
        internalLoading.value = false;
    }
};

// Computed property to check if patient has appointment today
const hasAppointmentToday = computed(() => {
    return todayAppointments.value.length > 0;
});

// Computed properties for current appointment - prioritize unfinished appointments
const currentAppointment = computed(() => {
    if (!hasAppointmentToday.value) return null;

    // Sort appointments by priority: unfinished first, then by time
    const sortedAppointments = [...todayAppointments.value].sort((a, b) => {
        // Check if appointments are finished (checked out)
        const aFinished = !!a.checkout_datetime;
        const bFinished = !!b.checkout_datetime;

        // Unfinished appointments come first
        if (!aFinished && bFinished) return -1;
        if (aFinished && !bFinished) return 1;

        // If both have same finish status, sort by appointment_date
        return new Date(a.appointment_date) - new Date(b.appointment_date);
    });

    return sortedAppointments[0] || null;
});

// All appointments for display
const allAppointments = computed(() => {
    if (!hasAppointmentToday.value) return [];

    // Sort appointments: unfinished first, then by time
    return [...todayAppointments.value].sort((a, b) => {
        const aFinished = !!a.checkout_datetime;
        const bFinished = !!b.checkout_datetime;

        if (!aFinished && bFinished) return -1;
        if (aFinished && !bFinished) return 1;

        return new Date(a.appointment_date) - new Date(b.appointment_date);
    });
});

// Computed property for appointment prescription (for current appointment)
const appointmentPrescription = computed(() => {
    return currentAppointment.value?.prescription || null;
});

// Appointment status management functions
const getStatusForAppointment = (appointment) => {
    if (!appointment) return "scheduled";
    // Status is derived from datetime fields in order of progression
    if (appointment.checkout_datetime) return "checked_out";
    if (appointment.completed_datetime) return "completed";
    if (appointment.in_progress_datetime) return "in_progress";
    if (appointment.checkin_datetime) return "checked_in";
    if (appointment.arrive_datetime) return "checked_in";
    return "scheduled";
};

const getNextStatus = (appointment) => {
    // Flow: checked_in -> in_progress -> completed -> checked_out
    if (!appointment) return "checked_in";
    if (appointment.checkout_datetime) return null; // Already checked out, no next status
    if (appointment.completed_datetime) return "checked_out";
    if (appointment.in_progress_datetime) return "completed";
    if (appointment.checkin_datetime || appointment.arrive_datetime)
        return "in_progress";
    return "checked_in"; // Not yet checked in
};

const getStatusText = (appointment) => {
    const s = getStatusForAppointment(appointment);
    switch (s) {
        case "scheduled":
            return t("appointments.scheduled") || "Scheduled";
        case "checked_in":
            return t("appointments.checked_in") || "Checked in";
        case "in_progress":
            return t("appointments.in_progress") || "In Progress";
        case "completed":
            return t("appointments.completed") || "Completed";
        case "checked_out":
            return t("appointments.checked_out") || "Checked out";
        case "cancelled":
            return t("appointments.cancelled") || "Cancelled";
        default:
            return s;
    }
};

const getStatusColor = (appointment) => {
    const s = getStatusForAppointment(appointment);
    switch (s) {
        case "scheduled":
            return "default";
        case "checked_in":
            return "processing";
        case "in_progress":
            return "cyan";
        case "completed":
            return "blue";
        case "checked_out":
            return "green";
        case "cancelled":
            return "red";
        default:
            return "default";
    }
};

const getActionLabel = (appointment) => {
    if (!appointment) return t("appointments.check_in");
    if (appointment.checkout_datetime)
        return t("appointments.checked_out") || "Checked Out";
    if (appointment.completed_datetime) return t("appointments.check_out");
    if (appointment.in_progress_datetime)
        return t("appointments.finish_appointment");
    if (appointment.checkin_datetime || appointment.arrive_datetime)
        return t("appointments.start_appointment");
    return t("appointments.check_in");
};

const getActionButtonStyle = (appointment) => {
    // Default blue primary
    const styles = {
        background: "#1890ff",
        borderColor: "#1890ff",
        color: "#fff",
    };

    if (!appointment) return styles;

    // If already checked out, make it neutral/disabled-looking
    if (appointment.checkout_datetime) {
        return {
            background: "#d9d9d9",
            borderColor: "#d9d9d9",
            color: "#8c8c8c",
        };
    }

    // Completed: green for Check Out
    if (appointment.completed_datetime) {
        return {
            background: "#52c41a",
            borderColor: "#52c41a",
            color: "#fff",
        };
    }

    // In Progress: blue for Complete
    if (appointment.in_progress_datetime) {
        return styles;
    }

    // Checked In: cyan for Start Progress
    if (appointment.checkin_datetime || appointment.arrive_datetime) {
        return {
            background: "#13c2c2",
            borderColor: "#13c2c2",
            color: "#fff",
        };
    }

    // Not checked in yet: amber/warning for Check In
    return {
        background: "#faad14",
        borderColor: "#faad14",
        color: "#fff",
    };
};

const currentAppointmentStatus = computed(() => {
    return getStatusForAppointment(currentAppointment.value);
});

const appointmentActionLabel = computed(() => {
    return getActionLabel(currentAppointment.value);
});

const appointmentActionStyle = computed(() => {
    return getActionButtonStyle(currentAppointment.value);
});

const handleSelectRoom = () => {
    emit("selectRoom");
};

const handleCreatePrescription = () => {
    emit("createPrescription", currentAppointment.value);
};

const handleViewPrescription = () => {
    if (appointmentPrescription.value) {
        emit("viewPrescription", appointmentPrescription.value);
    }
};

const handleReload = async () => {
    await fetchNextAppointment();
};

// Confirmation modal state and handlers
const modalVisible = ref(false);
const modalAction = ref(null);
const modalItem = ref(null);
const modalPatient = reactive({
    fullName: "",
    phone: "",
    address: "",
    ssn: "",
});

const modalTitle = computed(() => {
    if (!modalAction.value) return "";
    if (modalAction.value === "check_in")
        return t("appointments.confirm_check_in") || "Confirm check in";
    if (modalAction.value === "check_out")
        return t("appointments.confirm_check_out") || "Confirm check out";
    return t("common.confirm") || "Confirm";
});

const onConfirm = async () => {
    modalVisible.value = false;
    await performStatusUpdate();
    modalItem.value = null;
    modalAction.value = null;
};

const onCancel = () => {
    modalVisible.value = false;
    modalItem.value = null;
    modalAction.value = null;
};

const performStatusUpdate = async () => {
    if (!currentAppointment.value) return;

    const appointment = currentAppointment.value;
    const nextStatus = getNextStatus(appointment);

    if (!nextStatus) return; // Already at final status

    try {
        emit("update:loading", true);
        internalLoading.value = true;

        // Mark as being updated locally
        updatingLocally.value.add(appointment.xid);

        const url = `appointments/${appointment.xid}/update-status`;
        const payload = { flow_status: nextStatus };

        const response = await axiosAdmin.put(url, payload);

        if (response && response.data && response.status === "success") {
            // Update the appointment data in place without re-fetching
            const updated = response.data;
            Object.keys(updated).forEach((key) => {
                appointment[key] = updated[key];
            });

            // Show success message based on status
            if (nextStatus === "checked_in") {
                notification.success({
                    message: t("appointments.patient_checked_in"),
                    description: `Patient has been checked in successfully.`,
                });
            } else if (nextStatus === "in_progress") {
                notification.success({
                    message: t("appointments.in_progress_started"),
                    description: `Treatment has started.`,
                });
            } else if (nextStatus === "completed") {
                notification.success({
                    message: t("appointments.appointment_completed"),
                    description: `Treatment has been completed.`,
                });
            } else if (nextStatus === "checked_out") {
                notification.success({
                    message: t("appointments.patient_checked_out"),
                    description: `Patient has been checked out successfully.`,
                });
            }

            // Notify parent that appointment was updated (without triggering full fetch)
            emit("appointmentUpdated", updated);
        }
    } catch (error) {
        console.error("Error updating appointment status:", error);
        notification.error({
            message: t("common.error"),
            description: "Failed to update appointment status",
        });
    } finally {
        emit("update:loading", false);
        internalLoading.value = false;

        // Remove from local update tracking after a short delay
        // to ensure WebSocket event has been processed
        setTimeout(() => {
            updatingLocally.value.delete(appointment.xid);
        }, 1000);
    }
};

const toggleAppointmentStatus = () => {
    if (!currentAppointment.value) return;

    const appointment = currentAppointment.value;
    const nextStatus = getNextStatus(appointment);

    if (!nextStatus) return;

    modalAction.value = nextStatus;
    modalItem.value = appointment;

    // Populate modalPatient with basic info
    modalPatient.fullName = "";
    modalPatient.phone = "";
    modalPatient.address = "";
    modalPatient.ssn = "";

    modalVisible.value = true;
};

// Handle real-time appointment updates from WebSocket
const handleAppointmentUpdate = (data) => {
    const { appointment, action } = data;

    // Check if this appointment belongs to today's appointments
    const appointmentIndex = todayAppointments.value.findIndex(
        (apt) => apt.xid === appointment.xid,
    );

    if (appointmentIndex !== -1) {
        // Skip if this is a local update we just made
        if (updatingLocally.value.has(appointment.xid)) {
            updatingLocally.value.delete(appointment.xid);
            emit("appointmentUpdated", appointment);
            return;
        }

        // Store previous appointment data
        const previousAppointment = todayAppointments.value[appointmentIndex];

        // Update the appointment in the array
        todayAppointments.value[appointmentIndex] = {
            ...previousAppointment,
            ...appointment,
        };

        // Show notification based on action
        const patientName = "Patient";
        const statusText = getStatusText(appointment);
        const appointmentTime = getAppointmentTime(
            appointment.appointment_date,
        );

        if (action === "created") {
            notification.success({
                message: t("appointments.new_appointment"),
                description: `${appointmentTime} - New appointment for ${patientName}`,
                duration: 3,
            });
        } else if (action === "status_changed") {
            notification.info({
                message: t("appointments.status_updated"),
                description: `${appointmentTime} - ${patientName} - ${statusText}`,
                duration: 3,
            });
        } else if (action === "updated") {
            // Check if this is a room assignment update
            const previousRoom = previousAppointment.room;
            const currentRoom = appointment.room;
            const roomChanged = previousRoom?.xid !== currentRoom?.xid;

            if (roomChanged) {
                if (currentRoom) {
                    notification.info({
                        message:
                            t("appointments.room_assigned") || "Room Assigned",
                        description: `${appointmentTime} - ${patientName} assigned to ${currentRoom.name || currentRoom.room_number}`,
                        duration: 3,
                    });
                } else if (previousRoom) {
                    notification.info({
                        message:
                            t("appointments.room_unassigned") ||
                            "Room Unassigned",
                        description: `${appointmentTime} - ${patientName} unassigned from ${previousRoom.name || previousRoom.room_number}`,
                        duration: 3,
                    });
                }
            }
        }

        // Emit event to parent
        emit("appointmentUpdated", appointment);
    } else if (action === "created") {
        // New appointment created for today - refresh to get it
        fetchNextAppointment();
    }
};

// Expose method to mark appointment as being updated locally
const markAsLocalUpdate = (appointmentXid) => {
    updatingLocally.value.add(appointmentXid);

    // Remove from tracking after 3 seconds
    setTimeout(() => {
        updatingLocally.value.delete(appointmentXid);
    }, 3000);
};

// Expose method to parent component
defineExpose({
    markAsLocalUpdate,
    fetchNextAppointment,
    currentAppointment,
});

// Subscribe to WebSocket updates
onMounted(() => {
    // Fetch next appointment on mount
    fetchNextAppointment();

    const companyId = user.value?.company_id;
    if (echo && companyId) {
        echo.private(`company.${companyId}`).listen(
            ".appointment.updated",
            (data) => {
                handleAppointmentUpdate(data);
            },
        );
    }
});

// Cleanup on unmount
onUnmounted(() => {
    const companyId = user.value?.company_id;
    if (echo && companyId) {
        echo.leave(`company.${companyId}`);
    }
});
</script>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
