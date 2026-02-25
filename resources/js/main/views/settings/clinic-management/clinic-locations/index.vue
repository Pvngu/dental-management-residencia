<template>
    <div class="clinic-locations-container">
        <div class="mb-4 flex justify-between align-items-center">
            <div class="clinic-limit-info">
                <a-typography-text type="secondary">
                    {{ $t("clinic_location.clinic_count_info", {
                        current: clinicLimitInfo.current_count || 0,
                        max: clinicLimitInfo.max_allowed || 1
                    }) }}
                </a-typography-text>
            </div>
            <div class="flex gap-2">
                <a-tooltip v-if="!clinicLimitInfo.can_add_more" placement="bottomLeft" :title="$t('clinic_location.limit_reached_message')">
                    <a-button
                        v-if="
                            permsArray.includes('clinic_locations_create') ||
                            permsArray.includes('admin')
                        "
                        type="primary"
                        :disabled="!clinicLimitInfo.can_add_more"
                        @click="addItem"
                    >
                        <PlusOutlined />
                        {{ $t("clinic_location.add") }}
                    </a-button>
                </a-tooltip>
                <a-button
                    v-else-if="
                        permsArray.includes('clinic_locations_create') ||
                        permsArray.includes('admin')
                    "
                    type="primary"
                    @click="addItem"
                >
                    <PlusOutlined />
                    {{ $t("clinic_location.add") }}
                </a-button>
                <a-button
                    v-if="
                        table.selectedRowKeys.length > 0 &&
                        (permsArray.includes('clinic_locations_delete') ||
                            permsArray.includes('admin'))
                    "
                    type="primary"
                    @click="showSelectedDeleteConfirm"
                    danger
                >
                    <DeleteOutlined />
                    {{ $t("common.delete") }}
                </a-button>
            </div>
        </div>
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
                    getCheckboxProps: () => ({
                        disabled:
                            !permsArray.includes('clinic_locations_delete') &&
                            !permsArray.includes('admin'),
                    }),
                }"
                bordered
                size="middle"
            >
                <template #title>
                    <a-row justify="end" align="middle" class="table-header">
                        <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
                            <a-input-group compact>
                                <a-select
                                    style="width: 25%"
                                    v-model:value="table.searchColumn"
                                    :placeholder="
                                        $t('common.select_default_text', [''])
                                    "
                                >
                                    <a-select-option
                                        v-for="filterableColumn in filterableColumns"
                                        :key="filterableColumn.key"
                                    >
                                        {{ filterableColumn.value }}
                                    </a-select-option>
                                </a-select>
                                <a-input-search
                                    style="width: 75%"
                                    v-model:value="table.searchString"
                                    :placeholder="$t('common.search')"
                                    show-search
                                    @search="onTableSearch"
                                    @change="onTableSearch"
                                    :loading="table.loading"
                                />
                            </a-input-group>
                        </a-col>
                    </a-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'status'">
                        <a-tag :color="record.status ? 'green' : 'red'">
                            {{
                                record.status
                                    ? $t("common.enabled")
                                    : $t("common.disabled")
                            }}
                        </a-tag>
                    </template>
                    <template v-if="column.dataIndex === 'action'">
                        <a-button
                            v-if="
                                permsArray.includes('clinic_locations_edit') ||
                                permsArray.includes('admin')
                            "
                            type="primary"
                            @click="editItem(record)"
                            style="margin-left: 4px"
                        >
                            <template #icon><EditOutlined /></template>
                        </a-button>
                        <a-button
                            v-if="
                                permsArray.includes(
                                    'clinic_locations_delete',
                                ) || permsArray.includes('admin')
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
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
} from "@ant-design/icons-vue";
import { notification, Modal } from "ant-design-vue";
import common from "../../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        AddEdit,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
        } = fields();
        const { permsArray } = common();

        // Use global axiosAdmin
        const axiosAdminInstance = window.axiosAdmin;

        // State management
        const table = ref({
            data: [],
            loading: false,
            pagination: {
                current: 1,
                pageSize: 10,
                total: 0,
                showSizeChanger: true,
                showQuickJumper: true,
                pageSizeOptions: ['10', '25', '50', '100'],
            },
            searchString: "",
            searchColumn: "",
            selectedRowKeys: [],
            filterableColumns: filterableColumns,
        });

        const clinicLimitInfo = ref({
            current_count: 0,
            max_allowed: 1,
            can_add_more: true,
            remaining_slots: 0
        });

        // Add/Edit state
        const addEditVisible = ref(false);
        const addEditType = ref("add");
        const formData = ref({ ...initData });
        const viewData = ref({});
        const pageTitle = ref("");
        const successMessage = ref("");

        // Fetch clinic locations data
        const fetchData = (params = {}) => {
            table.value.loading = true;
            
            const requestParams = {
                limit: params.pageSize || table.value.pagination.pageSize,
                offset: params.current ? (params.current - 1) * (params.pageSize || table.value.pagination.pageSize) : 0,
                order: 'id desc',
                ...params,
            };

            // Add search parameters if they exist
            if (table.value.searchString) {
                requestParams.search = table.value.searchString;
                if (table.value.searchColumn) {
                    requestParams.search_column = table.value.searchColumn;
                }
            }

            axiosAdminInstance.get(url, { params: requestParams })
                .then((response) => {
                    const responseData = response.data;
                    table.value.data = responseData.data;
                    clinicLimitInfo.value = responseData.clinic_limit_info;
                    
                    table.value.pagination = {
                        ...table.value.pagination,
                        current: Math.floor(responseData.pagination.offset / responseData.pagination.limit) + 1,
                        pageSize: responseData.pagination.limit,
                        total: responseData.pagination.total,
                    };
                })
                .catch((error) => {
                    console.error('Error fetching clinic locations:', error);
                    notification.error({
                        message: "Error",
                        description: "Failed to fetch clinic locations",
                    });
                })
                .finally(() => {
                    table.value.loading = false;
                });
        };

        onMounted(() => {
            fetchData();
        });

        // Table event handlers
        const handleTableChange = (pagination, filters, sorter) => {
            fetchData({
                current: pagination.current,
                pageSize: pagination.pageSize,
            });
        };

        const onTableSearch = () => {
            fetchData({ current: 1 }); // Reset to first page on search
        };

        const onRowSelectChange = (selectedRowKeys) => {
            table.value.selectedRowKeys = selectedRowKeys;
        };

        // Add/Edit functions
        const addItem = () => {
            addEditType.value = "add";
            pageTitle.value = `Add ${columns.find(c => c.dataIndex === 'name')?.title || 'Clinic Location'}`;
            successMessage.value = "Clinic location added successfully";
            formData.value = { ...initData };
            addEditVisible.value = true;
        };

        const editItem = (record) => {
            addEditType.value = "edit";
            pageTitle.value = `Edit ${columns.find(c => c.dataIndex === 'name')?.title || 'Clinic Location'}`;
            successMessage.value = "Clinic location updated successfully";
            formData.value = { ...record };
            viewData.value = { ...record };
            addEditVisible.value = true;
        };

        const onCloseAddEdit = () => {
            addEditVisible.value = false;
            formData.value = { ...initData };
            viewData.value = {};
        };

        const addEditSuccess = () => {
            onCloseAddEdit();
            fetchData(); // Refresh data after successful add/edit
        };

        // Delete functions
        const showDeleteConfirm = (xid) => {
            Modal.confirm({
                title: 'Are you sure?',
                content: 'This action cannot be undone.',
                onOk: () => {
                    deleteRecord(xid);
                }
            });
        };

        const showSelectedDeleteConfirm = () => {
            if (table.value.selectedRowKeys.length === 0) return;
            
            Modal.confirm({
                title: 'Are you sure?',
                content: `Delete ${table.value.selectedRowKeys.length} selected items?`,
                onOk: () => {
                    deleteMultipleRecords();
                }
            });
        };

        const deleteRecord = (xid) => {
            axiosAdminInstance.delete(`${addEditUrl}/${xid}`)
                .then(() => {
                    notification.success({
                        message: "Success",
                        description: "Clinic location deleted successfully",
                    });
                    fetchData(); // Refresh data
                })
                .catch((error) => {
                    console.error('Error deleting clinic location:', error);
                    notification.error({
                        message: "Error", 
                        description: "Failed to delete clinic location",
                    });
                });
        };

        const deleteMultipleRecords = () => {
            const deletePromises = table.value.selectedRowKeys.map(xid => 
                axiosAdminInstance.delete(`${addEditUrl}/${xid}`)
            );

            Promise.all(deletePromises)
                .then(() => {
                    notification.success({
                        message: "Success",
                        description: `${table.value.selectedRowKeys.length} clinic locations deleted successfully`,
                    });
                    table.value.selectedRowKeys = [];
                    fetchData(); // Refresh data
                })
                .catch((error) => {
                    console.error('Error deleting clinic locations:', error);
                    notification.error({
                        message: "Error",
                        description: "Failed to delete some clinic locations",
                    });
                });
        };

        return {
            table,
            clinicLimitInfo,
            permsArray,
            columns,
            filterableColumns,
            
            // Add/Edit
            addEditVisible,
            addEditType,
            formData,
            viewData,
            pageTitle,
            successMessage,
            addEditUrl,
            
            // Functions
            fetchData,
            handleTableChange,
            onTableSearch,
            onRowSelectChange,
            addItem,
            editItem,
            addEditSuccess,
            onCloseAddEdit,
            showDeleteConfirm,
            showSelectedDeleteConfirm,
        };
    },
};
</script>
