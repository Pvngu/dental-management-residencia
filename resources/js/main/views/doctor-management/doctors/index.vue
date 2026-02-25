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
        :departments="departments"
        :specialists="specialists"
        :selectedDoctorId="selectedDoctorId"
    />
    
    <!-- Doctor summary Cards -->
    <a-row :gutter="[16,16]" class="mb-4 w-full">
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('doctors.total_doctors')"
                    :value="doctorStats?.totalDoctors || 0"
                    :loading="loading"
                />
            </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('doctors.top_specialist')"
                :value="doctorStats?.topSpecialist || '-'"
                :loading="loading"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('doctors.avg_appointment_charge')"
                :value="formatAmountCurrency(doctorStats?.avgAppointmentCharge || 0)"
                :loading="loading"
            />
        </a-col>
        <a-col :xs="24" :sm="12" :md="6" :lg="6">
            <StateWidget
                :title="$t('doctors.available_today')"
                :value="doctorStats?.availableToday || 0"
                :loading="loading"
            />
        </a-col>
    </a-row>
    
    <!-- Table -->
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
                    bordered
                    size="middle"
                >
                    <template #title>
                        <a-row justify="end" align="middle" class="table-header">
                            <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
                                <a-input-search
                                    v-model:value="extraFilters.searchString"
                                    :placeholder="$t('common.search')"
                                    show-search
                                    @search="fetchSearchData"
                                    @change="fetchSearchData"
                                    :loading="table.loading"
                                />
                            </a-col>
                            <a-col class="ml-2">
                                <Filters 
                                    @onReset="resetFilters"
                                    :filters="filters"
                                >
                                    <a-col :span="24">
                                        <a-form-item :label="$t('doctors.departments')">
                                            <a-select
                                                v-model:value="filters.doctor_department_id"
                                                :placeholder="$t('common.select_default_text', [$t('doctors.departments')])"
                                                :allowClear="true"
                                                style="width: 100%"
                                                optionFilterProp="name"
                                                show-search
                                                @change="setUrlData"
                                            >
                                                <a-select-option
                                                    v-for="department in departments"
                                                    :key="department.xid"
                                                    :title="department.name"
                                                    :value="department.xid"
                                                >
                                                    {{ department.name }}
                                                </a-select-option>
                                            </a-select>
                                        </a-form-item>
                                        <a-form-item :label="$t('doctors.status')">
                                            <a-select
                                                v-model:value="filters.status"
                                                :placeholder="$t('common.select_default_text', [$t('doctors.status')])"
                                                :allowClear="true"
                                                style="width: 100%"
                                                optionFilterProp="title"
                                                show-search
                                                @change="setUrlData"
                                            >
                                                <a-select-option
                                                    v-for="status in statuses"
                                                    :key="status.value"
                                                    :title="status.label"
                                                    :value="status.value"
                                                >
                                                    {{ status.label }}
                                                </a-select-option>
                                            </a-select>
                                        </a-form-item>
                                    </a-col>
                                </Filters>
                            </a-col>
                        </a-row>
                    </template>
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'doctor'">
                            <user-info :user="record.user" :showEmail="true" />
                        </template>
                        <template v-if="column.dataIndex === 'status' && record.user">
                            <a-tag :color="statusColors[record.user.status]">
                                {{ $t(`common.${record.user.status}`) }}
                            </a-tag>
                        </template>
                        <template v-if="column.dataIndex === 'qualification'">
                            {{ record.qualification }}
                        </template>
                        <template v-if="column.dataIndex === 'color'">
                            <div class="flex items-center gap-2">
                                <div 
                                    class="w-6 h-6 rounded border border-gray-300" 
                                    :style="{ backgroundColor: record.color || '#3B82F6' }"
                                ></div>
                                <span>{{ record.color || '#3B82F6' }}</span>
                            </div>
                        </template>
                        <template v-if="column.dataIndex === 'action'">
                            <a-button
                                v-if="
                                    permsArray.includes('doctors_edit') ||
                                    permsArray.includes('admin')
                                "
                                type="primary"
                                @click="handleEdit(record)"
                                style="margin-left: 4px"
                            >
                                <template #icon><EditOutlined /></template>
                            </a-button>
                            <a-button
                                v-if="
                                    (permsArray.includes('doctors_delete') ||
                                        permsArray.includes('admin')) &&
                                    (!record.children || record.children.length == 0)
                                "
                                type="primary"
                                @click="showDeleteConfirm(record.xid)"
                                style="margin-left: 4px"
                            >
                                <template #icon><DeleteOutlined /></template>
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>
        </a-col>
    </a-row>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { PlusOutlined, EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import { useStatsQuery } from "../../../../common/composable/useStatsQuery";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../common/components/common/select/Filters.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import StateWidget from "../../../../common/components/common/card/StateWidget.vue";
import { debounce } from "lodash-es";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        Filters,
        UserInfo,
        StateWidget,
    },
    setup() {
        const { url, addEditUrl, initData, columns, filterableColumns, statuses, departments, specialists, getPrefetchData, hashableColumns } = fields();
        const { permsArray, statusColors, formatAmountCurrency } = common();
        const crudVariables = crud();
        const selectedDoctorId = ref(null);
        
        const {
            stats: doctorStats, 
            isLoading: statsLoading, 
            refetch: refetchStats 
        } = useStatsQuery("/doctors/stats", {
            initialData: {
                totalDoctors: 0,
                topSpecialist: '-',
                avgAppointmentCharge: 0,
                availableToday: 0
            },
        });
        
        const filters = ref({
            doctor_department_id: undefined,
            status: undefined,
        });

        const extraFilters = ref({
            searchString: undefined,
        });

        const setUrlData = () => {
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "doctors";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.tableUrl.value = {
                url,
                filters,
                extraFilters,
            };

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                doctor_department_id: undefined,
                status: undefined,
            };
            extraFilters.value = {
                searchString: undefined,
            };
            setUrlData();
        };

        const fetchSearchData = debounce(() => {
            setUrlData();
        }, 400);
        
        const handleEdit = (record) => {
            crudVariables.editItem(record);
            selectedDoctorId.value = record.xid;
        };
        
        const handleAddDoctor = () => {
            crudVariables.addItem();
        };
        
        // Override addEditSuccess to refresh stats
        const originalAddEditSuccess = crudVariables.addEditSuccess;
        crudVariables.addEditSuccess = (id) => {
            originalAddEditSuccess(id);
            refetchStats();
        };
        
        // Initialize the table on mount
        onMounted(() => {
            getPrefetchData();
            setUrlData(); // Enables query and fetches if no cache, uses cache if available
            
            window.addEventListener('add-doctor', handleAddDoctor);
        });
        
        onUnmounted(() => {
            window.removeEventListener('add-doctor', handleAddDoctor);
        });

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            statuses,
            filters,
            extraFilters,
            setUrlData,
            resetFilters,
            fetchSearchData,
            statusColors,
            departments,
            specialists,
            formatAmountCurrency,
            handleEdit,
            doctorStats,
            selectedDoctorId,
            loading: statsLoading,
        };
    },
};
</script>
