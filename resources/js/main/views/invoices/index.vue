<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.invoices`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <template
                    v-if="
                        permsArray.includes('invoices_create') ||
                        permsArray.includes('admin')
                    "
                >
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("invoices.add") }}
                    </a-button>
                </template>
                <a-button
                    v-if="
                        table.selectedRowKeys.length > 0 &&
                        (permsArray.includes('invoices_delete') ||
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
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.invoices`) }}
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

        <!-- Summary Cards -->
        <a-row :gutter="[16,16]" class="mb-4 mt-5">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('invoices.total_invoices')"
                    :value="invoiceStats.totalInvoices || 0"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('invoices.pending_invoices')"
                    :value="invoiceStats.pendingInvoices || 0"
                    color="#faad14"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('invoices.paid_invoices')"
                    :value="invoiceStats.paidInvoices || 0"
                    color="#52c41a"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('invoices.total_amount')"
                    :value="formatCurrency(invoiceStats.totalAmount || 0)"
                    color="#1890ff"
                />
            </a-col>
        </a-row>

        <!-- Filter Tabs -->
        <a-row class="mt-5">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.status" 
                    @change="setUrlData" 
                    centered
                    type="card"
                    class="table-tab-filters"
                >
                    <a-tab-pane key="">
                        <template #tab>
                            <span>
                                <FileOutlined />
                                {{ $t("common.all") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="pending">
                        <template #tab>
                            <span>
                                <ClockCircleOutlined />
                                {{ $t("invoices.status_pending") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="paid">
                        <template #tab>
                            <span>
                                <CheckCircleOutlined />
                                {{ $t("invoices.status_paid") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="overdue">
                        <template #tab>
                            <span>
                                <ExclamationCircleOutlined />
                                {{ $t("invoices.status_overdue") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="cancelled">
                        <template #tab>
                            <span>
                                <CloseCircleOutlined />
                                {{ $t("invoices.status_cancelled") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>

        <!-- Main Table -->
        <a-row class="mt-5">
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
                        :row-selection="{
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onSelectedRowKeysChange
                        }"
                    >
                        <template #title>
                            <a-row justify="end" align="middle" class="table-header">
                                <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
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
                                    <Filters 
                                        @onReset="resetFilters"
                                        :filters="filters"
                                    >
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
                            <template v-if="column.dataIndex === 'invoice_number'">
                                <a @click="viewItem(record)">{{ record.invoice_number }}</a>
                            </template>
                            <template v-if="column.dataIndex === 'date_of_issue'">
                                {{ formatDate(record.date_of_issue) }}
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ $t(`invoices.status_${record.status}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'total_payable'">
                                {{ formatCurrency(record.total_payable) }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('invoices_edit') ||
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
                                        permsArray.includes('invoices_delete') ||
                                        permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                    danger
                                >
                                    <template #icon><DeleteOutlined /></template>
                                </a-button>
                                <a-button
                                    type="primary"
                                    @click="printInvoice(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><PrinterOutlined /></template>
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
import { ref, onMounted } from "vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import {
    PlusOutlined,
    DeleteOutlined,
    FileOutlined,
    EditOutlined,
    PrinterOutlined,
    ClockCircleOutlined,
    CheckCircleOutlined,
    ExclamationCircleOutlined,
    CloseCircleOutlined
} from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import StateWidget from "../../../common/components/common/card/StateWidget.vue";
import Filters from "../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../common/components/common/calendar/DateRangePicker.vue";
import dayjs from "dayjs";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        FileOutlined,
        EditOutlined,
        PrinterOutlined,
        ClockCircleOutlined,
        CheckCircleOutlined,
        ExclamationCircleOutlined,
        CloseCircleOutlined,
        AdminPageHeader,
        AddEdit,
        StateWidget,
        Filters,
        DateRangePicker
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
            getPrefetchData
        } = fields();
        const { permsArray } = common();
        const crudVariables = crud();
        const invoiceStats = ref({
            totalInvoices: 0,
            pendingInvoices: 0,
            paidInvoices: 0,
            totalAmount: 0
        });
        const filters = ref({
            status: "",
        });
        const extraFilters = ref({
            dates: []
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;

                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "invoices";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
                fetchInvoiceStats();
            });
        });

        const fetchInvoiceStats = () => {
            axiosAdmin.get('invoice-stats').then((response) => {
                invoiceStats.value = response.data;
            }).catch(() => {
                // If the endpoint doesn't exist yet, use dummy data
                invoiceStats.value = {
                    totalInvoices: 0,
                    pendingInvoices: 0,
                    paidInvoices: 0,
                    totalAmount: 0
                };
            });
        };

        const setUrlData = () => {
            let finalUrl = url;
            
            // Add date filter if available
            if (extraFilters.value.dates && extraFilters.value.dates.length === 2) {
                finalUrl += `&dates=${extraFilters.value.dates}`;
            }
            
            crudVariables.tableUrl.value = {
                url: finalUrl,
                filters: filters.value,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                status: "",
            };
            extraFilters.value = {
                dates: []
            };
            setUrlData();
        };

        const formatCurrency = (value) => {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(value || 0);
        };

        const formatDate = (date) => {
            return dayjs(date).format('YYYY-MM-DD');
        };

        const getStatusColor = (status) => {
            const colors = {
                pending: 'orange',
                paid: 'green',
                overdue: 'red',
                cancelled: 'gray'
            };
            return colors[status] || 'blue';
        };

        const printInvoice = (record) => {
            // Placeholder for invoice printing functionality
            // In a real application, this would redirect to a print-friendly view or generate a PDF
            window.alert(`Printing invoice ${record.invoice_number}`);
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            resetFilters,
            filters,
            extraFilters,
            formatCurrency,
            formatDate,
            getStatusColor,
            printInvoice,
            invoiceStats
        };
    },
};
</script>

<style scoped>
.summary-cards {
    margin-bottom: 24px;
}
</style>
