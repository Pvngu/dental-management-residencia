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
                        {{ $t("mail_management.register_mail") }}
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
                    @change="setUrlData"
                >
                    <a-tab-pane key="received">
                        <template #tab>
                            <span>
                                <InboxOutlined />
                                {{ $t("mail_management.incoming") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="dispatched">
                        <template #tab>
                            <span>
                                <SendOutlined />
                                {{ $t("mail_management.outgoing") }}
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
                        :row-selection="{
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onRowSelectChange,
                            getCheckboxProps: (record) => ({
                                disabled: false,
                                name: record.xid,
                            }),
                        }"
                        bordered
                        size="middle"
                    >
                        <template #title>
                            <a-row justify="end" align="middle" class="table-header">
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
                                    <a-input-group compact>
                                        <a-select
                                            style="width: 25%"
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
                                <a-col class="ml-2">
                                    <Filters @onReset="resetFilters" :filters="filters">
                                        <a-col :span="24" v-if="filters.postal_type === 'dispatched'">
                                            <a-form-item :label="$t('mail_management.status')">
                                                <a-select
                                                    v-model:value="filters.status"
                                                    :placeholder="$t('common.select_default_text', [$t('mail_management.status')])"
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option value="Pending">
                                                        {{ $t("mail_management.status_pending") }}
                                                    </a-select-option>
                                                    <a-select-option value="Shipped">
                                                        {{ $t("mail_management.status_shipped") }}
                                                    </a-select-option>
                                                    <a-select-option value="In Transit">
                                                        {{ $t("mail_management.status_in_transit") }}
                                                    </a-select-option>
                                                    <a-select-option value="Delivered">
                                                        {{ $t("mail_management.status_delivered") }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                        <a-col :span="24">
                                            <a-form-item :label="$t('common.date')">
                                                <DateRangePicker
                                                    @dateTimeChanged="
                                                        (changedDateTime) => {
                                                            extraFilters.dates = changedDateTime;
                                                            setUrlData();
                                                        }
                                                    "
                                                />
                                            </a-form-item>
                                        </a-col>
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'reference_no'">
                                {{ record.reference_no || '-' }}
                            </template>

                            <template v-if="column.dataIndex === 'sender_name'">
                                {{ record.sender_name || '-' }}
                            </template>

                            <template v-if="column.dataIndex === 'organization'">
                                {{ record.organization || '-' }}
                            </template>

                            <template v-if="column.dataIndex === 'assigned_to'">
                                <span v-if="record.assignedTo && record.assignedTo.user">
                                    {{ `${record.assignedTo.user.first_name} ${record.assignedTo.user.last_name}` }}
                                </span>
                                <span v-else>{{ record.assigned_to || '-' }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'sender_by'">
                                <span v-if="record.sender && record.sender.user">
                                    {{ `${record.sender.user.first_name} ${record.sender.user.last_name}` }}
                                </span>
                                <span v-else>{{ record.sender && record.sender.name ? record.sender.name : (record.sender_by || '-') }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'dispatched_by'">
                                <span v-if="record.dispatched_by && record.dispatched_by.user">
                                    {{ `${record.dispatched_by.user.first_name} ${record.dispatched_by.user.last_name}` }}
                                </span>
                                <span v-else>{{ record.dispatched_by && record.dispatched_by.name ? record.dispatched_by.name : (record.dispatched_by || '-') }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'mail_type'">
                                {{ record.mail_type && record.mail_type.name ? record.mail_type.name : (record.mail_type || '-') }}
                            </template>

                            <template v-if="column.dataIndex === 'creator'">
                                <span v-if="record.creator && record.creator.user">
                                    {{ `${record.creator.user.first_name} ${record.creator.user.last_name}` }}
                                </span>
                                <span v-else>{{ record.creator && record.creator.name ? record.creator.name : '-' }}</span>
                            </template>

                            <template v-if="column.dataIndex === 'address'">
                                <span>
                                    {{ Array.isArray(record.address) ? record.address.join(', ') : (record.address || '-') }}
                                </span>
                            </template>

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
                            
                            <template v-if="column.dataIndex === 'date'">
                                {{ formatDate(record.date) }}
                            </template>

                            <template v-if="column.dataIndex === 'received_by'">
                                <span v-if="record.received_by && record.received_by.name">
                                    {{ `${record.received_by.name} ${record.received_by.last_name}` }}
                                </span>
                                <span v-else>{{ record.received_by || '-' }}</span>
                            </template>
                            <template v-if="column.dataIndex === 'patient'">
                                <span v-if="record.patient">
                                    {{ record.patient.user ? `${record.patient.user.first_name} ${record.patient.user.last_name}` : (record.patient.name || '-') }}
                                </span>
                                <span v-else>-</span>
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ record.status }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-dropdown>
                                    <a-button type="text" shape="circle">
                                        <template #icon><EllipsisOutlined /></template>
                                    </a-button>
                                    <template #overlay>
                                        <a-menu>
                                            <a-menu-item
                                                v-if="record.file"
                                                @click="downloadFile(record)"
                                            >
                                                <DownloadOutlined /> {{ $t("common.download") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    permsArray.includes('postal_receive_edit') ||
                                                    permsArray.includes('postal_dispatch_edit') ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="editItem(record)"
                                            >
                                                <EditOutlined /> {{ $t("common.edit") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    permsArray.includes('postal_receive_delete') ||
                                                    permsArray.includes('postal_dispatch_delete') ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="showDeleteConfirm(record.xid)"
                                            >
                                                <DeleteOutlined /> {{ $t("common.delete") }}
                                            </a-menu-item>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import {
    PlusOutlined,
    DeleteOutlined,
    InboxOutlined,
    SendOutlined,
    EditOutlined,
    EllipsisOutlined,
    DownloadOutlined,
    LinkOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../common/components/common/calendar/DateRangePicker.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        InboxOutlined,
        SendOutlined,
        EditOutlined,
        EllipsisOutlined,
        DownloadOutlined,
        LinkOutlined,
        AdminPageHeader,
        AddEdit,
        Filters,
        DateRangePicker,
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            receivedColumns,
            dispatchedColumns,
            filterableColumns,
            hashableColumns,
            patients,
            getPrefetchData,
        } = fields();
        const { permsArray, formatDate, downloadS3File } = common();
        const crudVariables = crud();

        const filters = ref({
            postal_type: "received",
            status: "",
        });

        const extraFilters = ref({
            dates: [],
        });

        const currentColumns = computed(() => {
            return filters.value.postal_type === "received" 
                ? receivedColumns 
                : dispatchedColumns;
        });

        // Watch for postal_type changes to update form data
        watch(
            () => filters.value.postal_type,
            (newType) => {
                crudVariables.formData.value.postal_type = newType;
            }
        );

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

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;

                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "postal";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
            });
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters,
                extraFilters,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                postal_type: "received",
                status: "",
            };
            extraFilters.value = {
                dates: [],
            };
            setUrlData();
        };

        const getStatusColor = (status) => {
            const colors = {
                'Pending': 'default',
                'Shipped': 'processing',
                'In Transit': 'processing',
                'Received': 'success',
                'Delivered': 'success',
            };
            return colors[status] || 'default';
        };

        const downloadFile = (record) => {
            downloadS3File(record.file, 'postals');
        };

        return {
            ...crudVariables,
            permsArray,
            currentColumns,
            filterableColumns,
            setUrlData,
            filters,
            extraFilters,
            resetFilters,
            formatDate,
            getStatusColor,
            downloadFile,
            trackPackage,
            trackSelectedPackages,
            hasTrackablePackages,
            trackableCount,
            patients,
        };
    },
};
</script>