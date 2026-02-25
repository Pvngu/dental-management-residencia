<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t('menu.front_office')" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <template
                    v-if="
                        permsArray.includes('postal_receive_create') ||
                        permsArray.includes('postal_dispatch_create') ||
                        permsArray.includes('admin')
                    "
                >
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("postal.add") }}
                    </a-button>
                </template>
                <a-button
                    v-if="
                        table.selectedRowKeys.length > 0 &&
                        (permsArray.includes('postal_receive_delete') ||
                            permsArray.includes('postal_dispatch_delete') ||
                            permsArray.includes('admin'))
                    "
                    type="primary"
                    @click="showSelectedDeleteConfirm"
                    danger
                >
                    <template #icon><DeleteOutlined /></template>
                    {{ $t("common.delete") }}
                </a-button>
            </a-space>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t("menu.dashboard") }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.front_office") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
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

        <!-- Filters tabs -->
        <a-row class="mt-5">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.postal_type"
                    @change="onTabChange"
                >
                    <a-tab-pane key="received">
                        <template #tab>
                            <span>
                                <InboxOutlined />
                                {{ $t("menu.postal_receive") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="dispatched">
                        <template #tab>
                            <span>
                                <SendOutlined />
                                {{ $t("menu.postal_dispatch") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>

        <a-row>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="currentColumns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        :row-selection="filters.postal_type === 'dispatched' ? {
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onRowSelectChange,
                            getCheckboxProps: (record) => ({
                                name: record.xid,
                            }),
                        } : null"
                        bordered
                        size="middle"
                    >
                        <template #title>
                            <a-row justify="space-between" align="middle" class="table-header">
                                <a-col v-if="filters.postal_type === 'dispatched'">
                                    <a-button
                                        v-if="table.selectedRowKeys.length > 0 && hasTrackablePackages"
                                        type="primary"
                                        @click="trackSelectedPackages"
                                    >
                                        <template #icon><LinkOutlined /></template>
                                        {{ $t('postal.track_selected') }} ({{ trackableCount }})
                                    </a-button>
                                </a-col>
                                <a-col 
                                    :xs="21"
                                    :sm="16"
                                    :md="16"
                                    :lg="12"
                                    :xl="8"
                                >
                                    <a-input-search
                                        style="width: 100%"
                                        v-model:value="table.searchString"
                                        :placeholder="$t('common.search')"
                                        show-search
                                        @search="onTableSearch"
                                        @change="onTableSearch"
                                        :loading="table.loading"
                                    />
                                </a-col>
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'tracking_number' && filters.postal_type === 'dispatched'">
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span>{{ record.tracking_number || 'N/A' }}</span>
                                    <a-button
                                        v-if="record.tracking_number"
                                        type="link"
                                        size="small"
                                        @click="trackPackage(record.tracking_number)"
                                    >
                                        <template #icon><LinkOutlined /></template>
                                        Track
                                    </a-button>
                                </div>
                            </template>
                            <template v-if="column.dataIndex === 'status' && filters.postal_type === 'dispatched'">
                                <a-select
                                    v-model:value="record.status"
                                    style="width: 130px"
                                    size="small"
                                    @change="(value) => updateStatus(record, value)"
                                >
                                    <a-select-option value="Pending">
                                        <a-badge status="warning" text="Pending" />
                                    </a-select-option>
                                    <a-select-option value="Shipped">
                                        <a-badge status="processing" text="Shipped" />
                                    </a-select-option>
                                    <a-select-option value="In Transit">
                                        <a-badge status="default" text="In Transit" />
                                    </a-select-option>
                                    <a-select-option value="Received">
                                        <a-badge status="success" text="Received" />
                                    </a-select-option>
                                    <a-select-option value="Delivered">
                                        <a-badge status="success" text="Delivered" />
                                    </a-select-option>
                                </a-select>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        (filters.postal_type === 'received' && (permsArray.includes('postal_receive_edit') || permsArray.includes('admin'))) ||
                                        (filters.postal_type === 'dispatched' && (permsArray.includes('postal_dispatch_edit') || permsArray.includes('admin')))
                                    "
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="
                                        (filters.postal_type === 'received' && (permsArray.includes('postal_receive_delete') || permsArray.includes('admin'))) ||
                                        (filters.postal_type === 'dispatched' && (permsArray.includes('postal_dispatch_delete') || permsArray.includes('admin')))
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
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { useI18n } from "vue-i18n";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import {
    PlusOutlined,
    InboxOutlined,
    SendOutlined,
    DeleteOutlined,
    EditOutlined,
    LinkOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import { message } from "ant-design-vue";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        AdminPageHeader,
        PlusOutlined,
        InboxOutlined,
        SendOutlined,
        DeleteOutlined,
        EditOutlined,
        LinkOutlined,
        AddEdit,
    },
    setup() {
        const { t } = useI18n();
        const { permsArray } = common();
        const crudVariables = crud();
        const axiosAdmin = window.axiosAdmin;

        const filters = ref({
            postal_type: "received",
        });

        const receivedColumns = [
            {
                title: t("postal.from_title"),
                dataIndex: "from_title",
            },
            {
                title: t("postal.to_title"),
                dataIndex: "to_title",
            },
            {
                title: t("postal.reference_no"),
                dataIndex: "reference_no",
            },
            {
                title: t("common.date"),
                dataIndex: "date",
            },
            {
                title: t("common.address"),
                dataIndex: "address",
            },
            {
                title: t("postal.received_by"),
                dataIndex: "received_by",
            },
            {
                title: t("common.file"),
                dataIndex: "file",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ];

        const dispatchedColumns = [
            {
                title: t("postal.patient_name"),
                dataIndex: "patient_name",
            },
            {
                title: t("postal.type"),
                dataIndex: "type",
            },
            {
                title: t("postal.carrier"),
                dataIndex: "carrier",
            },
            {
                title: t("postal.tracking_number"),
                dataIndex: "tracking_number",
            },
            {
                title: t("postal.status"),
                dataIndex: "status",
            },
            {
                title: t("common.date"),
                dataIndex: "date",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ];

        const currentColumns = computed(() => {
            return filters.value.postal_type === "received"
                ? receivedColumns
                : dispatchedColumns;
        });

        const receivedInitData = {
            from_title: "",
            to_title: "",
            reference_no: "",
            date: "",
            address: "",
            postal_type: "received",
            received_by: "",
            dispatched_by: "",
            file: "",
        };

        const dispatchedInitData = {
            from_title: "",
            to_title: "",
            reference_no: "",
            date: "",
            address: "",
            postal_type: "dispatched",
            received_by: "",
            dispatched_by: "",
            file: "",
            patient_name: "",
            patient_id: undefined,
            type: "Package",
            carrier: "",
            tracking_number: "",
            status: "Pending",
        };

        onMounted(() => {
            crudVariables.langKey.value = "postal";
            setUrlData();
        });

        const setUrlData = () => {
            const baseUrl = "postals?fields=id,xid,from_title,to_title,reference_no,date,address,postal_type,received_by,dispatched_by,file,patient_name,patient_id,x_patient_id,type,carrier,tracking_number,status";
            
            crudVariables.tableUrl.value = {
                url: baseUrl,
                filters,
            };

            crudVariables.crudUrl.value = "postals";
            crudVariables.hashableColumns.value = ["patient_id"];
            
            // Set init data based on current tab
            const currentInitData = filters.value.postal_type === "received" 
                ? receivedInitData 
                : dispatchedInitData;
            
            crudVariables.initData.value = { ...currentInitData };
            crudVariables.formData.value = { ...currentInitData };

            crudVariables.fetch({
                page: 1,
            });
        };

        const onTabChange = () => {
            // Reset selection when changing tabs
            crudVariables.table.selectedRowKeys = [];
            setUrlData();
        };

        const trackPackage = (trackingNumber) => {
            if (trackingNumber) {
                const trackingUrl = `https://t.17track.net/en#nums=${trackingNumber}`;
                window.open(trackingUrl, "_blank");
            }
        };

        const trackSelectedPackages = () => {
            const selectedRecords = crudVariables.table.data.filter((record) =>
                crudVariables.table.selectedRowKeys.includes(record.xid)
            );

            const trackingNumbers = selectedRecords
                .filter(
                    (record) =>
                        record.tracking_number &&
                        record.tracking_number.trim() !== ""
                )
                .map((record) => record.tracking_number);

            if (trackingNumbers.length === 0) {
                message.warning(t("postal.no_tracking_numbers"));
                return;
            }

            const trackingUrl = `https://t.17track.net/en#nums=${trackingNumbers.join(",")}`;
            window.open(trackingUrl, "_blank");
        };

        const onRowSelectChange = (selectedRowKeys) => {
            crudVariables.table.selectedRowKeys = selectedRowKeys;
        };

        const hasTrackablePackages = computed(() => {
            const selectedRecords = crudVariables.table.data.filter((record) =>
                crudVariables.table.selectedRowKeys.includes(record.xid)
            );
            return selectedRecords.some(
                (record) =>
                    record.tracking_number &&
                    record.tracking_number.trim() !== ""
            );
        });

        const trackableCount = computed(() => {
            const selectedRecords = crudVariables.table.data.filter((record) =>
                crudVariables.table.selectedRowKeys.includes(record.xid)
            );
            return selectedRecords.filter(
                (record) =>
                    record.tracking_number &&
                    record.tracking_number.trim() !== ""
            ).length;
        });

        const updateStatus = (record, newStatus) => {
            const payload = {
                from_title: record.from_title,
                to_title: record.to_title,
                reference_no: record.reference_no,
                date: record.date.split("T")[0],
                address: record.address,
                postal_type: record.postal_type,
                received_by: record.received_by || "",
                dispatched_by: record.dispatched_by || "",
                file: record.file || "",
                patient_name: record.patient_name || "",
                type: record.type,
                carrier: record.carrier || "",
                tracking_number: record.tracking_number || "",
                status: newStatus,
            };

            axiosAdmin
                .put(`postals/${record.xid}`, payload)
                .then(() => {
                    message.success("Status updated successfully");
                    setUrlData();
                })
                .catch((error) => {
                    console.error("Failed to update status:", error);
                    message.error("Failed to update status");
                    // Revert the status back
                    setUrlData();
                });
        };

        return {
            ...crudVariables,
            permsArray,
            filters,
            currentColumns,
            setUrlData,
            onTabChange,
            trackPackage,
            trackSelectedPackages,
            onRowSelectChange,
            hasTrackablePackages,
            trackableCount,
            updateStatus,
        };
    },
};
</script>
