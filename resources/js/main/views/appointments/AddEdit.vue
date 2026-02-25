<template>
    <Appointment />
</template>

<script>
import { defineComponent, ref, computed, onMounted } from "vue";
import { SaveOutlined, ClockCircleOutlined, LeftOutlined, RightOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import UserSelect from "../../../common/components/common/select/UserSelect.vue";
import DateTimePicker from "../../../common/components/common/calendar/DateTimePicker.vue";
import Appointment from "../../components/appointment/index.vue"
import axios from "axios";
import dayjs from 'dayjs';

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        SaveOutlined,
        ClockCircleOutlined,
        LeftOutlined,
        RightOutlined,
        DateTimePicker,
        Appointment,
        UserSelect,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const selectedDate = ref(null);
        const selectedSlot = ref(null);
        const availableSlots = ref([]);
        const loadingSlots = ref(false);
        const currentDate = ref(dayjs());

        const canSubmit = computed(() => {
            return props.formData.patient_id && 
                   props.formData.doctor_id && 
                   selectedDate.value && 
                   selectedSlot.value;
        });

        const onDateSelect = (date) => {
            selectedDate.value = date;
            selectedSlot.value = null;
            if (props.formData.doctor_id) {
                getAvailableSlots();
            }
        };

        const selectTimeSlot = (time) => {
            selectedSlot.value = time;
            const dateTimeStr = `${dayjs(selectedDate.value).format('YYYY-MM-DD')} ${time}`;
            props.formData.appointment_date = dayjs(dateTimeStr, 'YYYY-MM-DD HH:mm').format('YYYY-MM-DD HH:mm:ss');
        };

        const getAvailableSlots = () => {
            if (!selectedDate.value || !props.formData.doctor_id) {
                availableSlots.value = [];
                return;
            }

            loadingSlots.value = true;
            const formattedDate = dayjs(selectedDate.value).format('YYYY-MM-DD');
            
            axios.get(`/api/appointments/available-slots`, {
                params: {
                    doctor_id: props.formData.doctor_id,
                    date: formattedDate
                }
            })
            .then(response => {
                // Assuming the API returns an array of available time slots
                availableSlots.value = response.data.data || [
                    // Fallback mock data in case the API is not yet implemented
                    { time: '9:00am', booked: false },
                    { time: '9:30am', booked: false },
                    { time: '10:00am', booked: false },
                    { time: '10:30am', booked: true },
                    { time: '11:00am', booked: false },
                    { time: '11:30am', booked: false },
                    { time: '1:30pm', booked: false },
                    { time: '2:00pm', booked: false },
                    { time: '2:30pm', booked: false },
                    { time: '3:00pm', booked: false }
                ];
            })
            .catch(error => {
                console.error('Error fetching available slots:', error);
                availableSlots.value = [];
            })
            .finally(() => {
                loadingSlots.value = false;
            });
        };

        const disabledDate = (current) => {
            // Disable dates before today
            return current && current < dayjs().startOf('day').toDate();
        };

        const formatDate = (date) => {
            return dayjs(date).format('dddd, MMMM D, YYYY');
        };

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            selectedDate.value = null;
            selectedSlot.value = null;
            emit("closed");
        };

        const changeMonth = (direction) => {
            currentDate.value = dayjs(currentDate.value).add(direction, 'month').toDate();
        };

        const currentMonthYear = computed(() => {
            return dayjs(currentDate.value).format('MMMM YYYY');
        });

        onMounted(() => {
            if (props.addEditType === 'edit' && props.formData.appointment_date) {
                selectedDate.value = dayjs(props.formData.appointment_date).toDate();
                selectedSlot.value = dayjs(props.formData.appointment_date).format('h:mma');
                if (props.formData.doctor_id) {
                    getAvailableSlots();
                }
            }
        });

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            selectedDate,
            selectedSlot,
            availableSlots,
            loadingSlots,
            onDateSelect,
            selectTimeSlot,
            disabledDate,
            formatDate,
            canSubmit,
            getAvailableSlots,
            modalWidth: window.innerWidth <= 991 ? "95%" : "90%",
            currentDate,
            changeMonth,
            currentMonthYear,
        };
    },
});
</script>

<style lang="scss" scoped>
/* Override ant design calendar styles for better fit */
:deep(.ant-picker-calendar-header) {
    padding-top: 0;
    padding-bottom: 8px;
}

:deep(.ant-picker-panel) {
    border: none;
}

:deep(.ant-picker-calendar-date-content) {
    height: 20px;
}

:deep(.ant-picker-calendar) {
    border: none;
}
</style>

<style>
.schedule-calendar .ant-picker-calendar-header {
  display: none;
}
.schedule-calendar .ant-picker-panel .ant-picker-date-panel .ant-picker-body .ant-picker-content tbody tr td div{
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
}

.schedule-calendar .ant-picker-panel .ant-picker-date-panel .ant-picker-body .ant-picker-content tbody tr td div{
    height: 54px !important;
    margin: 0 !important;
    padding: 0 !important;
}

.schedule-calendar .ant-picker-panel .ant-picker-date-panel .ant-picker-body .ant-picker-content tbody tr td div div{
    font-size: 12px !important;
}
</style>