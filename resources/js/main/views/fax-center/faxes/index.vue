<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t('menu.fax_center')" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <template
                    v-if="
                        permsArray.includes('faxes_create') ||
                        permsArray.includes('admin')
                    "
                >
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("fax.send_fax") }}
                    </a-button>
                </template>
                <a-button
                    v-if="
                        table.selectedRowKeys.length > 0 &&
                        (permsArray.includes('faxes_delete') ||
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
                    {{ $t("menu.fax_center") }}
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
            :insuranceProviders="insuranceProviders"
        />

        <!-- Filters tabs -->
        <a-row class="mt-5">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.direction"
                    @change="setUrlData"
                >
                    <a-tab-pane key="">
                        <template #tab>
                            <span>
                                <FileOutlined />
                                {{ $t("common.all") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="outbound">
                        <template #tab>
                            <span>
                                <SendOutlined />
                                {{ $t("fax.outbound") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="inbound">
                        <template #tab>
                            <span>
                                <InboxOutlined />
                                {{ $t("fax.inbound") }}
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
                                disabled: false,
                                name: record.xid,
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
                                        <a-col :span="24">
                                            <a-form-item :label="$t('fax.status')">
                                                <a-select
                                                    v-model:value="filters.status"
                                                    :placeholder="$t('common.select_default_text', [$t('fax.status')])"
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option value="queued">
                                                        {{ $t("fax.status_queued") }}
                                                    </a-select-option>
                                                    <a-select-option value="sending">
                                                        {{ $t("fax.status_sending") }}
                                                    </a-select-option>
                                                    <a-select-option value="sent">
                                                        {{ $t("fax.status_sent") }}
                                                    </a-select-option>
                                                    <a-select-option value="failed">
                                                        {{ $t("fax.status_failed") }}
                                                    </a-select-option>
                                                    <a-select-option value="received">
                                                        {{ $t("fax.status_received") }}
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
                            <template v-if="column.dataIndex === 'created_at'">
                                {{ formatDateTime(record.created_at) }}
                            </template>
                            <template v-if="column.dataIndex === 'direction'">
                                <a-tag :color="record.direction === 'outbound' ? 'blue' : 'green'">
                                    <SendOutlined v-if="record.direction === 'outbound'" />
                                    <InboxOutlined v-else />
                                    {{ $t(`fax.${record.direction}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ $t(`fax.status_${record.status}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'patient'">
                                <span v-if="record.patient">
                                    {{ record.patient.user ? `${record.patient.user.first_name} ${record.patient.user.last_name}` : '-' }}
                                </span>
                                <span v-else>-</span>
                            </template>
                            <template v-if="column.dataIndex === 'insurance_provider'">
                                <span v-if="record.insurance_provider">
                                    {{ record.insurance_provider.name }}
                                </span>
                                <span v-else>-</span>
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
                                                @click="downloadFax(record)"
                                            >
                                                <DownloadOutlined /> {{ $t("common.download") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    (permsArray.includes('faxes_resend') ||
                                                        permsArray.includes('admin')) &&
                                                    record.status === 'failed'
                                                "
                                                @click="resendFax(record.xid)"
                                            >
                                                <RedoOutlined /> {{ $t("fax.resend") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    permsArray.includes('faxes_edit') ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="editItem(record)"
                                            >
                                                <EditOutlined /> {{ $t("common.edit") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    permsArray.includes('faxes_delete') ||
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
import { ref, onMounted } from "vue";
import { notification } from "ant-design-vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import {
    PlusOutlined,
    DeleteOutlined,
    FileOutlined,
    SendOutlined,
    InboxOutlined,
    EditOutlined,
    EllipsisOutlined,
    DownloadOutlined,
    RedoOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
const axiosAdmin = window.axiosAdmin;

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        FileOutlined,
        SendOutlined,
        InboxOutlined,
        EditOutlined,
        EllipsisOutlined,
        DownloadOutlined,
        RedoOutlined,
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
            columns,
            filterableColumns,
            hashableColumns,
            insuranceProviders,
            getPrefetchData,
        } = fields();
        const { permsArray, formatDateTime, downloadS3File } = common();
        const crudVariables = crud();

        const filters = ref({
            direction: "",
            status: "",
        });

        const extraFilters = ref({
            dates: [],
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;

                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "fax";
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
                direction: "",
                status: "",
            };
            extraFilters.value = {
                dates: [],
            };
            setUrlData();
        };

        const getStatusColor = (status) => {
            const colors = {
                queued: 'default',
                sending: 'processing',
                sent: 'success',
                failed: 'error',
                received: 'success',
            };
            return colors[status] || 'default';
        };

        const downloadFax = (record) => {
            downloadS3File(record.file, 'faxes');
        };

        const resendFax = (xid) => {
            axiosAdmin
                .post(`faxes/${xid}/resend`)
                .then((response) => {
                    notification.success({
                        message: t('common.success'),
                        description: t('fax.resend_success'),
                    });
                    setUrlData();
                })
                .catch((error) => {
                    notification.error({
                        message: t('common.error'),
                        description: t('fax.resend_error'),
                    });
                });
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            filters,
            extraFilters,
            resetFilters,
            formatDateTime,
            getStatusColor,
            downloadFax,
            resendFax,
            insuranceProviders,
        };
    },
};
</script>
