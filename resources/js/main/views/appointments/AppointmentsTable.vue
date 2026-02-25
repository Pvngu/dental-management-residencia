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
    
    <a-row :gutter="[16,16]" class="mb-4 w-full">
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('appointments.total_appointments_today')"
                :value="appointmentStats.totalAppointmentsToday"
                :loading="loadingStats"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('appointments.pending_appointments')"
                :value="appointmentStats.pendingAppointments"
                :loading="loadingStats"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('appointments.completed_appointments')"
                :value="appointmentStats.completedAppointments"
                :loading="loadingStats"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('appointments.cancellations')"
                :value="appointmentStats.cancellations"
                :loading="loadingStats"
            />
        </a-col>
    </a-row>
    <a-row class="w-full">
        <a-col :span="24">
            <div class="table-responsive">
                <a-table
                    :columns="columns"
                    :row-key="(record) => record.xid"
                    :data-source="table.data"
                    :pagination="table.pagination"
                    :loading="table.loading"
                    @change="handleTableChange"
                    :row-selection="{
                        selectedRowKeys: table.selectedRowKeys,
                        onChange: onRowSelectChange,
                        getCheckboxProps: (record) => ({
                            disabled: !permsArray.includes('appointments_delete') && !permsArray.includes('admin'),
                        }),
                    }"
                    bordered
                    size="middle"
                >
                    <template #title>
                        <a-row justify="end" align="middle" class="table-header">
                            <a-col 
                                :xs="21"
                                :sm="16"
                                :md="16"
                                :lg="12"
                                :xl="8"
                            >
                                <a-input-group compact>
                                    <a-select
                                        style="width: 30%"
                                        v-model:value="table.searchColumn"
                                        :placeholder="$t('common.select_default_text', [''])"
                                    >
                                        <a-select-option
                                            v-for="filterableColumn in filterableColumns"
                                            :key="filterableColumn.key"
                                        >
                                            {{ filterableColumn.value }}
                                        </a-select-option>
                                    </a-select>
                                    <a-input-search
                                        style="width: 70%"
                                        v-model:value="extraFilters.searchString"
                                        :placeholder="$t('common.search')"
                                        show-search
                                        @search="onTableSearch"
                                        @change="onTableSearch"
                                        :loading="table.loading"
                                    />
                                </a-input-group>
                            </a-col>
                            <a-col class="ml-2">
                                <Filters 
                                    @onReset="resetFilters"
                                    :filters="filters"
                                >
                                    <a-col :span="24">
                                        <a-form-item :label="$t('common.status')">
                                            <a-select
                                                v-model:value="filters.status"
                                                :placeholder="$t('common.select_default_text', [$t('common.status')])"
                                                :allowClear="true"
                                                style="width: 100%"
                                                optionFilterProp="title"
                                                show-search
                                                @change="setUrlData"
                                            >
                                                <a-select-option
                                                    v-for="option in statusOptions"
                                                    :key="option.value"
                                                    :value="option.value"
                                                >
                                                    {{ option.label }}
                                                </a-select-option>
                                            </a-select>
                                        </a-form-item>
                                    </a-col>
                                    <a-col :span="24">
                                        <a-form-item :label="$t('common.doctor')">
                                            <UserSelect
                                                @onChange="(id) => { filters.doctor_id = id; setUrlData(); }"
                                                :value="filters.doctor_id"
                                                userType="doctor"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :span="24">
                                        <a-form-item :label="$t('common.patient')">
                                            <UserSelect
                                                @onChange="(id) => { filters.patient_id = id; setUrlData(); }"
                                                :value="filters.patient_id"
                                                userType="patient"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :span="24">
                                        <a-form-item :label="$t('common.room')">
                                            <a-select
                                                v-model:value="filters.room_id"
                                                :placeholder="$t('common.select_default_text', [$t('common.room')])"
                                                :allowClear="true"
                                                style="width: 100%"
                                                optionFilterProp="title"
                                                show-search
                                                @change="setUrlData"
                                            >
                                                <a-select-option
                                                    v-for="room in rooms"
                                                    :key="room.xid"
                                                    :title="room.name"
                                                    :value="room.xid"
                                                >
                                                    {{ room.name }}
                                                </a-select-option>
                                            </a-select>
                                        </a-form-item>
                                    </a-col>
                                    <a-col :span="24">
                                        <a-form-item :label="$t('common.treatment_type')">
                                            <a-select
                                                v-model:value="filters.treatment_type_id"
                                                :placeholder="$t('common.select_default_text', [$t('common.treatment_type')])"
                                                :allowClear="true"
                                                style="width: 100%"
                                                optionFilterProp="title"
                                                show-search
                                                @change="setUrlData"
                                            >
                                                <a-select-option
                                                    v-for="type in treatmentTypes"
                                                    :key="type.xid"
                                                    :title="type.name"
                                                    :value="type.xid"
                                                >
                                                    {{ type.name }} ({{ type.duration_minutes }}min)
                                                </a-select-option>
                                            </a-select>
                                        </a-form-item>
                                    </a-col>
                                    <a-col :span="24">
                                        <a-form-item :label="$t('common.date')">
                                            <DateRangePicker
                                                @dateTimeChanged="(changedDateTime) => { extraFilters.dates = changedDateTime; setUrlData(); }"
                                            />
                                        </a-form-item>
                                    </a-col>
                                </Filters>
                            </a-col>
                        </a-row>
                    </template>
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'patient'">
                                <UserInfo
                                    isClickable
                                    @onClick="navigateToPatientDetails(record)"
                                    :user="record.patient.user"
                                    :showEmail="false"
                                />
                        </template>
                        <template v-else-if="column.dataIndex === 'doctor'">
                            <UserInfo 
                                :user="record.doctor.user" 
                                :showEmail="false"
                            />
                        </template>
                        <template v-else-if="column.dataIndex === 'room'">
                            {{ record.room ? record.room.name : '-' }}
                        </template>
                        <template v-else-if="column.dataIndex === 'treatment_type'">
                            {{ record.treatment_type ? record.treatment_type.name : '-' }}
                        </template>
                        <template v-if="column.dataIndex === 'time'">
                            <div class="font-bold">{{ formatTime(record.appointment_date) }}</div>
                            <div class="opacity-70">{{ calculateEndTime(record.appointment_date, record.duration) }}</div>
                        </template>
                        <template v-if="column.dataIndex === 'date'">
                            {{ formatDate(record.appointment_date) }}
                        </template>
                        <template v-if="column.dataIndex === 'status'">
                            <a-tag :color="getStatusColor(record.status)">
                                {{ record.status }}
                            </a-tag>
                        </template>
                        <template v-if="column.dataIndex === 'created_at'">
                            {{ formatDate(record.created_at) }}
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-dropdown>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item
                                            v-if="permsArray.includes('appointments_view') || permsArray.includes('admin')"
                                            @click="viewItem(record)"
                                            key="view"
                                        >
                                            <template #icon>
                                                <EyeOutlined />
                                            </template>
                                            <span>{{ $t("common.view") }}</span>
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="permsArray.includes('appointments_edit') || permsArray.includes('admin')"
                                            @click="editItem(record)"
                                            key="edit"
                                        >
                                            <template #icon>
                                                <EditOutlined />
                                            </template>
                                            <span>{{ $t("common.edit") }}</span>
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="(permsArray.includes('appointments_delete') || permsArray.includes('admin')) && (!record.children || record.children.length == 0)"
                                            @click="showDeleteConfirm(record.xid)"
                                            key="delete"
                                            danger
                                        >
                                            <template #icon>
                                                <DeleteOutlined />
                                            </template>
                                            <span>{{ $t("common.delete") }}</span>
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                                <a-button>
                                    <template #icon><EllipsisOutlined /></template>
                                </a-button>
                            </a-dropdown>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>
</template>

<script>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { DeleteOutlined, EditOutlined, EyeOutlined, EllipsisOutlined } from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import Appointment from "../../components/appointment/index.vue";
import AppointmentDetails from "../../components/appointment/AppointmentDetails.vue";
import UserInfo from "../../../common/components/user/UserInfo.vue";
import StateWidget from "../../../common/components/common/card/StateWidget.vue";
import Filters from "../../../common/components/common/select/Filters.vue";
import UserSelect from "../../../common/components/common/select/UserSelect.vue";
import DateRangePicker from "../../../common/components/common/calendar/DateRangePicker.vue";
export default {
    components: {
        DeleteOutlined,
        EditOutlined,
        EyeOutlined,
        EllipsisOutlined,
        Appointment,
        AppointmentDetails,
        UserInfo,
        StateWidget,
        Filters,
        UserSelect,
        DateRangePicker,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
            statusOptions,
            rooms,
            treatmentTypes,
            getPrefetchData
        } = fields();
        const router = useRouter();
        const { permsArray, formatDate, formatTime, dayjsObject } = common();
        const crudVariables = crud();

        // Filters state
        const filters = ref({
            status: undefined,
            doctor_id: undefined,
            patient_id: undefined,
            room_id: undefined,
            treatment_type_id: undefined,
        });

        // Extra filters (dates and search)
        const extraFilters = ref({
            searchString: "",
            dates: [],
        });

        // Summary stats
        const appointmentStats = ref({
            totalAppointmentsToday: 0,
            pendingAppointments: 0,
            completedAppointments: 0,
            cancellations: 0,
        });

        const loadingStats = ref(true);

        const fetchAppointmentStats = async () => {
            try {
                const response = await axiosAdmin.get("appointments/stats");
                appointmentStats.value = response.data;
            } catch (error) {
                // Handle error if needed
            } finally {
                loadingStats.value = false;
            }
        };

        // Calculate end time based on start time and duration (in minutes)
        const calculateEndTime = (startTime, durationMinutes) => {
            const startDateTime = dayjsObject(startTime);
            const endDateTime = startDateTime.add(parseInt(durationMinutes) || 0, 'minute');
            return formatTime(endDateTime);
        };

        const editItem = (record) => {
            // Parse appointment_date to get day, time, month, year
            const dateObj = new Date(record.appointment_date);
            const day = dateObj.getDate();
            const month = dateObj.getMonth(); // 0-based
            const year = dateObj.getFullYear();
            // Format time as h:mma (e.g., 9:30am)
            let hours = dateObj.getHours();
            let minutes = dateObj.getMinutes();
            const ampm = hours >= 12 ? 'pm' : 'am';
            let displayHours = hours % 12;
            displayHours = displayHours ? displayHours : 12; // 0 => 12
            const displayMinutes = minutes.toString().padStart(2, '0');
            const timeString = `${displayHours}:${displayMinutes}${ampm}`;

            crudVariables.formData.value = {
                appointment_date: record.appointment_date,
                duration: record.duration,
                treatment_details: record.treatment_details,
                status: record.status,
                xid: record.xid,
                patient_id: record.patient?.xid,
                doctor_id: record.doctor?.xid,
                selectedDate: day,
                selectedTimeSlot: timeString,
                currentMonth: month,
                currentYear: year,
                room_id: record.room?.xid,
                treatment_type_id: record.treatment_type?.xid,
            };
            crudVariables.addEditType.value = "edit";
            crudVariables.addEditVisible.value = true;
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

        // View modal state
        const viewModalVisible = ref(false);
        const selectedAppointment = ref({});

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

        const navigateToPatientDetails = (record) => {
            router.push({ name: 'admin.patients.detail', params: { id: record.patient?.xid || record.xid } });
        };

        const resetFilters = () => {
            filters.value = {
                status: undefined,
                doctor_id: undefined,
                patient_id: undefined,
                room_id: undefined,
                treatment_type_id: undefined,
            };
            extraFilters.value.dates = [];
            setUrlData();
        };

        const onTableSearch = () => {
            setUrlData();
        };

        onMounted(() => {
            getPrefetchData();
            setUrlData();
            
            // Listen for add appointment event from parent
            window.addEventListener('add-appointment', () => {
                crudVariables.addItem();
            });
        });

        const setUrlData = () => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "appointments";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            crudVariables.tableUrl.value = {
                url,
                filters: filters.value,
                extraFilters: extraFilters.value,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });

            fetchAppointmentStats();
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            formatDate,
            formatTime,
            getStatusColor,
            calculateEndTime,
            appointmentStats,
            editItem,
            viewItem,
            viewModalVisible,
            selectedAppointment,
            onCloseViewModal,
            filters,
            extraFilters,
            resetFilters,
            statusOptions,
            rooms,
            treatmentTypes,
            navigateToPatientDetails,
            loadingStats,
            onTableSearch,
        };
    },
};
</script>
