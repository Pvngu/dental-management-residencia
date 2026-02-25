<template>
    <Appointment
        :visible="addEditVisible"
        @closed="onCloseAddEdit"
        :formData="formData"
        :data="viewData"
        :successMessage="successMessage"
        :url="addEditUrl"
        :addEditType="addEditType"
        @addEditSuccess="addEditSuccess"
        :rooms="rooms"
        :treatmentTypes="treatmentTypes"
    />
    
    <!-- Appointment Details Modal -->
    <AppointmentDetails
        :visible="viewModalVisible"
        :appointmentData="selectedAppointment"
        @closed="onCloseViewModal"
    />
    
    <a-row class="mt-5">
        <a-col :span="24">
            <FullCalendar
                :calendarData="calendarAppointments"
                :loading="table.loading"
                @changeDate="onCalendarDateChange"
                @eventClick="onCalendarEventClick"
            />
        </a-col>
    </a-row>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import Appointment from "../../components/appointment/index.vue";
import AppointmentDetails from "../../components/appointment/AppointmentDetails.vue";
import FullCalendar from "../../../common/components/common/calendar/FullCalendar.vue";

export default {
    components: {
        Appointment,
        AppointmentDetails,
        FullCalendar,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            hashableColumns,
            rooms,
            treatmentTypes,
            getPrefetchData
        } = fields();
        const { dayjsObject } = common();
        const crudVariables = crud();

        // View modal state
        const viewModalVisible = ref(false);
        const selectedAppointment = ref({});

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

        // Transform appointments data for calendar
        const transformAppointmentsForCalendar = (appointments) => {
            return appointments.map(appointment => ({
                id: appointment.xid,
                title: `${appointment.patient?.user?.name || 'Unknown Patient'} - ${appointment.doctor?.user?.name || 'Unknown Doctor'}`,
                start: appointment.appointment_date,
                end: dayjsObject(appointment.appointment_date).add(parseInt(appointment.duration) || 60, 'minute').format(),
                backgroundColor: getStatusColor(appointment.status),
                borderColor: getStatusColor(appointment.status),
                extendedProps: {
                    appointment: appointment,
                    status: appointment.status,
                    patient: appointment.patient?.user?.name || 'Unknown Patient',
                    doctor: appointment.doctor?.user?.name || 'Unknown Doctor',
                    room: appointment.room?.name || '-',
                    treatment_type: appointment.treatment_type?.name || '-',
                    route: {
                        name: 'admin.appointments.calendar',
                        query: { view: appointment.xid }
                    }
                }
            }));
        };

        // Handle calendar date change
        const onCalendarDateChange = (startStr, endStr) => {
            // You can implement date range filtering for calendar here if needed
            // For now, we'll keep the existing filtering logic
        };

        // Handle calendar event click
        const onCalendarEventClick = (info) => {
            const appointment = info.event.extendedProps.appointment;
            if (appointment) {
                viewItem(appointment);
            }
        };

        // Function to view appointment details
        const viewItem = (record) => {
            selectedAppointment.value = record;
            viewModalVisible.value = true;
        };

        // Function to close view modal
        const onCloseViewModal = () => {
            viewModalVisible.value = false;
            selectedAppointment.value = {};
        };

        // Computed property for calendar data
        const calendarAppointments = computed(() => {
            return transformAppointmentsForCalendar(crudVariables.table.data || []);
        });

        onMounted(() => {
            getPrefetchData();
            setUrlData();
            
            // Listen for add appointment event from parent
            window.addEventListener('add-appointment', () => {
                crudVariables.addItem();
            });
        });

        const setUrlData = () => {
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "appointments";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            crudVariables.tableUrl.value = {
                url,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            ...crudVariables,
            viewItem,
            viewModalVisible,
            selectedAppointment,
            onCloseViewModal,
            rooms,
            treatmentTypes,
            calendarAppointments,
            onCalendarDateChange,
            onCalendarEventClick,
        };
    },
};
</script>
