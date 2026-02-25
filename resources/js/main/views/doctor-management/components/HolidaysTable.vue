<template>
    <div>
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            :hideDoctorSelect="true"
        />
        
        <HolidayEventModal
            :visible="eventModalVisible"
            :holiday="selectedEvent"
            :startDate="selectedEventStartDate"
            :endDate="selectedEventEndDate"
            :canEdit="true"
            @close="closeEventModal"
            @edit="editEventHoliday"
        />
        
        <a-row :gutter="[0, 16]">
            <a-col :span="24">
                <a-space>
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("doctor_holidays.add") }}
                    </a-button>
                    <a-radio-group v-model:value="viewMode" button-style="solid" size="small">
                        <a-radio-button value="list">
                            <UnorderedListOutlined />
                            {{ $t("common.list") }}
                        </a-radio-button>
                        <a-radio-button value="calendar">
                            <CalendarOutlined />
                            {{ $t("common.calendar") }}
                        </a-radio-button>
                    </a-radio-group>
                </a-space>
            </a-col>
            <a-col :span="24">
                <!-- List View -->
                <div v-if="viewMode === 'list'">
                    <HolidayList
                        :holidays="table.data"
                        :loading="table.loading"
                        :showDoctorInfo="false"
                        :showActions="true"
                        :canEdit="true"
                        :canDelete="true"
                        @edit="editItem"
                        @delete="showDeleteConfirm"
                    />
                </div>
                
                <!-- Calendar View -->
                <div v-else style="min-height: 500px">
                    <a-spin :spinning="calendarLoading">
                        <FullCalendar
                            ref="calendarRef"
                            :options="calendarOptions"
                        />
                    </a-spin>
                </div>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, ref, watch, computed } from "vue";
import { PlusOutlined, UnorderedListOutlined, CalendarOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import crud from "../../../../common/composable/crud";
import AddEdit from "../holidays/AddEdit.vue";
import HolidayList from "./HolidayList.vue";
import HolidayEventModal from "./HolidayEventModal.vue";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import { useI18n } from "vue-i18n";

export default defineComponent({
    props: ["doctorId"],
    components: {
        PlusOutlined,
        UnorderedListOutlined,
        CalendarOutlined,
        AddEdit,
        HolidayList,
        HolidayEventModal,
        FullCalendar,
    },
    setup(props) {
        const { dayjs, formatDate } = common();
        const crudVariables = crud();
        const { t } = useI18n();
        const viewMode = ref("list");
        const calendarRef = ref(null);
        const calendarLoading = ref(false);
        const eventModalVisible = ref(false);
        const selectedEvent = ref(null);
        const selectedEventStartDate = ref(null);
        const selectedEventEndDate = ref(null);

        const addEditUrl = "doctor-holidays";
        
        const filters = ref({});

        const initData = {
            doctor_id: props.doctorId,
            dateRange: [],
            start_date: "",
            end_date: "",
            reason: "",
            holiday_type: "vacation",
            status: "pending",
        };

        const fetchData = () => {
            if (!props.doctorId) return;
            
            crudVariables.tableUrl.value = {
                url: `doctor-holidays?fields=id,xid,doctor_id,x_doctor_id,start_date,end_date,reason,holiday_type,status&doctor_id=${props.doctorId}`,
                filters,
            };
            
            crudVariables.fetch({
                page: 1,
            });
        };

        // Convert holidays to calendar events
        const calendarEvents = computed(() => {
            if (!crudVariables.table.data) return [];
            
            return crudVariables.table.data.map(holiday => {
                const colors = {
                    vacation: '#8b5cf6',
                    sick_leave: '#f59e0b',
                    personal: '#10b981',
                    other: '#6366f1',
                };
                
                return {
                    id: holiday.xid,
                    title: holiday.reason || t('doctor_holidays.no_reason'),
                    start: holiday.start_date,
                    end: dayjs(holiday.end_date).add(1, 'day').format('YYYY-MM-DD'),
                    backgroundColor: colors[holiday.holiday_type] || '#6366f1',
                    borderColor: colors[holiday.holiday_type] || '#6366f1',
                    extendedProps: holiday,
                };
            });
        });

        // Handle calendar event click
        const handleEventClick = (info) => {
            const holiday = info.event.extendedProps;
            selectedEvent.value = holiday;
            selectedEventStartDate.value = info.event.start;
            selectedEventEndDate.value = new Date(info.event.end.getTime() - 86400000);
            eventModalVisible.value = true;
        };

        const closeEventModal = () => {
            eventModalVisible.value = false;
            selectedEvent.value = null;
            selectedEventStartDate.value = null;
            selectedEventEndDate.value = null;
        };

        const editEventHoliday = () => {
            // The selectedEvent already contains the full holiday data
            if (selectedEvent.value && selectedEvent.value.xid) {
                editItem(selectedEvent.value);
            }
            closeEventModal();
        };

        // Calendar options
        const calendarOptions = ref({
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek',
            },
            events: calendarEvents,
            eventClick: handleEventClick,
            height: 'auto',
            editable: false,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
        });

        // Initialize - IMPORTANT: Set crudUrl before any operations
        crudVariables.crudUrl.value = addEditUrl;
        crudVariables.langKey.value = "doctor_holidays";
        crudVariables.initData.value = { ...initData };
        crudVariables.formData.value = { ...initData };

        // Watch for doctor ID changes
        watch(() => props.doctorId, (newId) => {
            if (newId) {
                crudVariables.formData.value.doctor_id = newId;
                crudVariables.initData.value.doctor_id = newId;
                fetchData();
            }
        }, { immediate: true });

        // Watch for data changes to refresh calendar
        watch(() => crudVariables.table.data, () => {
            if (viewMode.value === 'calendar' && calendarRef.value) {
                const calendarApi = calendarRef.value.getApi();
                if (calendarApi) {
                    calendarApi.refetchEvents();
                }
            }
        }, { deep: true });

        // Override editItem to populate dateRange correctly
        const editItem = (record) => {
            crudVariables.editItem(record);
            console.log('Edit URL:', crudVariables.addEditUrl.value); // Debug: Check the URL
            // Ensure all fields are properly populated
            crudVariables.formData.value.doctor_id = props.doctorId;
            crudVariables.formData.value.start_date = record.start_date || '';
            crudVariables.formData.value.end_date = record.end_date || '';
            crudVariables.formData.value.holiday_type = record.holiday_type || 'vacation';
            crudVariables.formData.value.status = record.status || 'pending';
            crudVariables.formData.value.reason = record.reason || '';
            // Populate dateRange from start_date and end_date for the DateRangePicker
            if (record.start_date && record.end_date) {
                crudVariables.formData.value.dateRange = [record.start_date, record.end_date];
            }
        };

        // Override addItem to wrap doctor_id in array for create
        const addItem = () => {
            crudVariables.addItem();
            // Wrap doctor_id in array for backend validation
            crudVariables.formData.value.doctor_id = [props.doctorId];
        };

        // Override addEditSuccess to refresh calendar
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            originalAddEditSuccess(id);
            // Refresh calendar if in calendar view
            if (viewMode.value === 'calendar' && calendarRef.value) {
                const calendarApi = calendarRef.value.getApi();
                if (calendarApi) {
                    calendarApi.refetchEvents();
                }
            }
        };

        return {
            ...crudVariables,
            editItem,
            addItem,
            viewMode,
            calendarRef,
            calendarOptions,
            calendarLoading,
            eventModalVisible,
            selectedEvent,
            selectedEventStartDate,
            selectedEventEndDate,
            closeEventModal,
            editEventHoliday,
        };
    },
});
</script>

<style scoped>
/* FullCalendar custom styles */
:deep(.fc) {
    font-family: inherit;
}

:deep(.fc .fc-toolbar-title) {
    font-size: 1.25rem;
    font-weight: 600;
}

:deep(.fc .fc-button) {
    background-color: #1890ff;
    border-color: #1890ff;
    text-transform: capitalize;
}

:deep(.fc .fc-button:hover) {
    background-color: #40a9ff;
    border-color: #40a9ff;
}

:deep(.fc .fc-button-primary:not(:disabled):active),
:deep(.fc .fc-button-primary:not(:disabled).fc-button-active) {
    background-color: #096dd9;
    border-color: #096dd9;
}

:deep(.fc .fc-daygrid-day.fc-day-today) {
    background-color: #e6f7ff;
}

:deep(.fc-event) {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
}

:deep(.fc-event:hover) {
    opacity: 0.85;
}
</style>
