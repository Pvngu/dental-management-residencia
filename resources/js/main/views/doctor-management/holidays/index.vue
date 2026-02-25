<template>
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
    />
    
    <HolidayEventModal
            :visible="eventModalVisible"
            :holiday="selectedEvent"
            :startDate="selectedEventStartDate"
            :endDate="selectedEventEndDate"
            :canEdit="permsArray.includes('doctor_holidays_edit') || permsArray.includes('admin')"
            :holidayTypes="holidayTypes"
            :statusOptions="statusOptions"
            @close="closeEventModal"
            @edit="editEventHoliday"
        />

        <a-row :gutter="16">
            <!-- Left Sidebar - Doctor Selection -->
            <a-col :xs="24" :sm="24" :md="6" :lg="6" :xl="8">
                <DoctorSelection
                    :doctors="doctors"
                    v-model:selectedDoctorIds="selectedDoctorIds"
                    v-model:searchText="doctorSearchText"
                    :loading="doctorsLoading"
                    :showHolidayCount="true"
                    :title="$t('doctor_holidays.select_doctor')"
                    @toggle="toggleDoctorSelection"
                />
            </a-col>

            <!-- Right Content - Holiday List/Calendar -->
            <a-col :xs="24" :sm="24" :md="18" :lg="18" :xl="16">
                <a-card :bordered="false">
                    <!-- View Toggle and Selected Doctor Info -->
                    <template #title>
                        <a-flex justify="space-between" align="center">
                            <div v-if="selectedDoctorIds.length > 0">
                                <a-avatar-group :max-count="3" :size="32">
                                    <a-avatar
                                        v-for="doctorId in selectedDoctorIds"
                                        :key="doctorId"
                                        :src="
                                            getDoctorById(doctorId)?.user
                                                ?.profile_image_url
                                        "
                                    >
                                        {{
                                            getInitials(
                                                getDoctorById(doctorId)?.user
                                            )
                                        }}
                                    </a-avatar>
                                </a-avatar-group>
                                <span
                                    style="margin-left: 12px; font-weight: 500"
                                >
                                    {{ getSelectedDoctorNames() }}
                                </span>
                            </div>
                            <div v-else style="color: #8c8c8c">
                                {{ $t("doctor_holidays.no_doctor_selected") }}
                            </div>
                            <a-segmented
                                v-model:value="viewMode"
                                :options="viewOptions"
                            />
                        </a-flex>
                    </template>

                    <!-- List View -->
                    <div v-if="viewMode === 'list'" style="min-height: 400px">
                        <HolidayList
                            :holidays="table.data"
                            :loading="table.loading"
                            :showDoctorInfo="true"
                            :showActions="true"
                            :canEdit="permsArray.includes('doctor_holidays_edit') || permsArray.includes('admin')"
                            :canDelete="permsArray.includes('doctor_holidays_delete') || permsArray.includes('admin')"
                            :holidayTypes="holidayTypes"
                            :statusOptions="statusOptions"
                            @edit="editItem"
                            @delete="showDeleteConfirm"
                        />

                        <!-- Pagination -->
                        <a-flex justify="center" style="margin-top: 24px">
                            <a-pagination
                                v-model:current="table.pagination.current"
                                v-model:pageSize="table.pagination.pageSize"
                                :total="table.pagination.total"
                                :show-total="
                                    (total) =>
                                        `${$t('common.total')} ${total} ${$t(
                                            'common.items'
                                        )}`
                                "
                                @change="handleTableChange"
                            />
                        </a-flex>
                    </div>

                    <!-- Calendar View -->
                    <div
                        v-else-if="viewMode === 'calendar'"
                        style="min-height: 400px"
                    >
                        <a-spin :spinning="calendarLoading">
                            <FullCalendar ref="calendarRef" :options="calendarOptions" />
                        </a-spin>
                    </div>
                </a-card>
            </a-col>
        </a-row>
</template>

<script>
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    CalendarOutlined,
    ClockCircleOutlined,
    UnorderedListOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import HolidayList from "../components/HolidayList.vue";
import HolidayEventModal from "../components/HolidayEventModal.vue";
import DoctorSelection from "../components/DoctorSelection.vue";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import { useI18n } from "vue-i18n";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        CalendarOutlined,
        ClockCircleOutlined,
        AddEdit,
        UserInfo,
        HolidayList,
        HolidayEventModal,
        DoctorSelection,
        FullCalendar,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            hashableColumns,
            holidayTypes,
            statusOptions,
            doctors,
            getPrefetchData,
            calendarEventsUrl,
        } = fields();
        const { permsArray, formatDate, dayjs } = common();
        const crudVariables = crud();

        const viewMode = ref("list");
        const selectedDoctorIds = ref([]);
        const doctorSearchText = ref("");
        const calendarEvents = ref([]);
        const doctorsLoading = ref(true);
        const eventModalVisible = ref(false);
        const selectedEvent = ref(null);
        const selectedEventStartDate = ref(null);
        const selectedEventEndDate = ref(null);

        const { t } = useI18n();

        const viewOptions = [
            {
                label: "List",
                value: "list",
            },
            {
                label: "Calendar",
                value: "calendar",
            },
        ];

        // FullCalendar configuration
        const calendarOptions = ref({
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,dayGridWeek",
            },
            events: (fetchInfo, successCallback, failureCallback) => {
                calendarLoading.value = true;
                const params = new URLSearchParams({
                    start: fetchInfo.startStr.split("T")[0],
                    end: fetchInfo.endStr.split("T")[0],
                });

                if (selectedDoctorIds.value.length > 0) {
                    params.append("doctor_id", selectedDoctorIds.value.join(","));
                }

                axiosAdmin
                    .get(`${calendarEventsUrl}?${params.toString()}`)
                    .then((response) => {
                        // Ensure we're passing an array
                        const events = Array.isArray(response) 
                            ? response 
                            : (response.data || []);
                        successCallback(events);
                    })
                    .catch((error) => {
                        console.error("Error loading calendar events:", error);
                        failureCallback(error);
                    })
                    .finally(() => {
                        calendarLoading.value = false;
                    });                    
            },
            eventClick: handleEventClick,
            height: "auto",
            editable: false,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
        });

        function handleEventClick(info) {
            const holiday = info.event.extendedProps;
            selectedEvent.value = holiday;
            selectedEventStartDate.value = info.event.start;
            selectedEventEndDate.value = new Date(info.event.end.getTime() - 86400000);
            eventModalVisible.value = true;
        }

        const closeEventModal = () => {
            eventModalVisible.value = false;
            selectedEvent.value = null;
            selectedEventStartDate.value = null;
            selectedEventEndDate.value = null;
        };

        const editEventHoliday = () => {
            // Find the full holiday data and edit it
            // The API might return holiday_xid in extendedProps, or the full holiday object with xid
            const xid = selectedEvent.value.holiday_xid || selectedEvent.value.xid;
            const holidayData = crudVariables.table.data.find(
                (h) => h.xid === xid
            );
            if (holidayData) {
                // Use the overridden editItem which properly sets up the form
                crudVariables.editItem(holidayData);
            }
            closeEventModal();
        };

        const calendarRef = ref(null);
        const calendarLoading = ref(false);
        
        const handleAddHoliday = () => {
            crudVariables.addItem();
        };

        // Watch for doctor selection changes to reload calendar
        watch(selectedDoctorIds, () => {
            if (viewMode.value === "calendar" && calendarRef.value) {
                // Refetch events when doctor selection changes
                const calendarApi = calendarRef.value.getApi();
                if (calendarApi) {
                    calendarApi.refetchEvents();
                }
            }
        }, { deep: true });

        onMounted(() => {
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "doctor_holidays";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            // Load doctors without selecting any by default
            if (doctors.value && doctors.value.length > 0) {
                doctorsLoading.value = false;
            } else {
                // If doctors are still loading, fetch them
                getPrefetchData().then(() => {
                    doctorsLoading.value = false;
                });
            }

            setUrlData();
            
            window.addEventListener('add-holiday', handleAddHoliday);
        });
        
        onUnmounted(() => {
            window.removeEventListener('add-holiday', handleAddHoliday);
        });

        const toggleDoctorSelection = (doctorId) => {
            // Refresh based on current view mode
            if (viewMode.value === 'list') {
                setUrlData();
            }
            // Calendar refresh is handled by the watch on selectedDoctorIds
        };

        const getDoctorById = (doctorId) => {
            return doctors.value.find((d) => d.xid === doctorId);
        };

        const getInitials = (user) => {
            if (!user) return "?";
            const firstInitial = user.name
                ? user.name.charAt(0).toUpperCase()
                : "";
            const lastInitial = user.last_name
                ? user.last_name.charAt(0).toUpperCase()
                : "";
            return `${firstInitial}${lastInitial}`;
        };

        const getSelectedDoctorNames = () => {
            const names = selectedDoctorIds.value
                .map((id) => {
                    const doctor = getDoctorById(id);
                    return doctor
                        ? `${doctor.user.name} ${doctor.user.last_name || ''}`.trim()
                        : "";
                })
                .filter((name) => name);

            if (names.length > 2) {
                return `${names.slice(0, 2).join(", ")} +${names.length - 2}`;
            }
            return names.join(", ");
        };

        const getDoctorHolidayCount = (doctorId) => {
            const doctor = doctors.value.find((d) => d.xid === doctorId);
            return doctor ? doctor.total_holidays_count || 0 : 0;
        };

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url:
                    selectedDoctorIds.value.length > 0
                        ? `${url}&doctor_id=${selectedDoctorIds.value.join(
                              ","
                          )}`
                        : url,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };



        const handleTableChange = (page, pageSize) => {
            crudVariables.fetch({
                page: page,
            });
        };

        // Override addItem to pre-populate selected doctors
        const originalAddItem = crudVariables.addItem;
        crudVariables.addItem = () => {
            originalAddItem();
            
            // Pre-populate doctor_id with selected doctors
            if (selectedDoctorIds.value.length > 0) {
                crudVariables.formData.value.doctor_id = [...selectedDoctorIds.value];
            }
        };

        // Override editItem to properly populate dateRange and handle doctor_id array
        const originalEditItem = crudVariables.editItem;
        crudVariables.editItem = (item) => {
            originalEditItem(item);
            
            // Populate dateRange from start_date and end_date
            if (crudVariables.formData.value.start_date && crudVariables.formData.value.end_date) {
                crudVariables.formData.value.dateRange = [
                    crudVariables.formData.value.start_date,
                    crudVariables.formData.value.end_date
                ];
            }
            
            // For edit mode, keep doctor_id as single value (not array)
            // The UserSelect will be in single-select mode during edit
        };

        // Override addEditSuccess to refresh doctors list and calendar
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            // Call original first (this handles closing drawer and refreshing table)
            originalAddEditSuccess(id);
            
            // Then refresh doctors list with updated holiday counts
            getPrefetchData();
            
            // And refresh calendar if in calendar view
            if (viewMode.value === 'calendar' && calendarRef.value) {
                const calendarApi = calendarRef.value.getApi();
                if (calendarApi) {
                    calendarApi.refetchEvents();
                }
            }
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            setUrlData,
            formatDate,
            viewMode,
            viewOptions,
            selectedDoctorIds,
            doctors,
            doctorSearchText,
            doctorsLoading,
            toggleDoctorSelection,
            getDoctorById,
            getInitials,
            getSelectedDoctorNames,
            getDoctorHolidayCount,
            handleTableChange,
            dayjs,
            calendarOptions,
            calendarEvents,
            calendarRef,
            calendarLoading,
            holidayTypes,
            statusOptions,
            eventModalVisible,
            selectedEvent,
            selectedEventStartDate,
            selectedEventEndDate,
            closeEventModal,
            editEventHoliday,
        };
    },
};
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
