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
        />

        <a-row>
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.payment_status" 
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
                    <a-tab-pane key="paid">
                        <template #tab>
                            <span>
                                <CheckCircleOutlined />
                                {{ $t("purchase_medicine.paid") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="unpaid">
                        <template #tab>
                            <span>
                                <CloseCircleOutlined />
                                {{ $t("purchase_medicine.unpaid") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="partial">
                        <template #tab>
                            <span>
                                <ExclamationCircleOutlined />
                                {{ $t("purchase_medicine.partial") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
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
                                            <a-form-item :label="$t('purchase_medicine.payment_type')">
                                                <a-select
                                                    v-model:value="filters.payment_type"
                                                    :placeholder="
                                                        $t('common.select_default_text', [
                                                            $t('purchase_medicine.payment_type'),
                                                        ])
                                                    "
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="paymentType in paymentTypes"
                                                        :key="paymentType.key"
                                                        :value="paymentType.key"
                                                    >
                                                        {{ paymentType.value }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>

                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'delivery_date'">
                                {{ formatDate(record.delivery_date) }}
                            </template>
                            <template v-if="column.dataIndex === 'payment_type'">
                                {{ getPaymentTypeName(record.payment_type) }}
                            </template>
                            <template v-if="column.dataIndex === 'payment_status'">
                                <a-tag :color="getPaymentStatusColor(record.payment_status)">
                                    {{ getPaymentStatusName(record.payment_status) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'total'">
                                {{ formatAmountCurrency(record.total) }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('purchase_medicine_edit') || 
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
                                        permsArray.includes('purchase_medicine_delete') || 
                                        permsArray.includes('admin')
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
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
    FileOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined,
    ExclamationCircleOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        FileOutlined,
        CheckCircleOutlined,
        CloseCircleOutlined,
        ExclamationCircleOutlined,
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
            paymentTypes,
            paymentStatuses,
            getPrefetchData,
        } = fields();
        const { permsArray, formatDate, formatAmountCurrency } = common();
        const crudVariables = crud();
        const filters = ref({
            payment_status: "",
            payment_type: undefined,
        });
        const extraFilters = ref({
            dates: [],
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;

                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "purchase_medicine";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
            });
            
            // Listen for add event from parent
            window.addEventListener('add-purchase', crudVariables.addItem);
        });
        
        onBeforeUnmount(() => {
            window.removeEventListener('add-purchase', crudVariables.addItem);
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
                payment_status: "",
                payment_type: undefined,
            };
            extraFilters.value = {
                dates: [],
            };
            setUrlData();
        };

        const getPaymentTypeName = (type) => {
            const found = paymentTypes.value.find(item => item.key === type);
            return found ? found.value : type;
        };

        const getPaymentStatusName = (status) => {
            const found = paymentStatuses.value.find(item => item.key === status);
            return found ? found.value : status;
        };

        const getPaymentStatusColor = (status) => {
            switch (status) {
                case 'paid':
                    return 'success';
                case 'unpaid':
                    return 'error';
                case 'partial':
                    return 'warning';
                default:
                    return 'default';
            }
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            paymentTypes,
            paymentStatuses,
            filters,
            extraFilters,
            setUrlData,
            resetFilters,
            getPaymentTypeName,
            getPaymentStatusName,
            getPaymentStatusColor,

            formatAmountCurrency,
            formatDate,
        };
    },
};
</script>