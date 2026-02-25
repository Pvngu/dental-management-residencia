<template>
    <div>
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="() => {
                onCloseAddEdit();
                formData.adjustment_items = [];
            }"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            :adjustmentReasons="adjustmentReasons"
            :itemColumns="itemColumns"
        />

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
                                            <a-form-item :label="$t('inventory_adjustment.adjustment_reason')">
                                                <a-select
                                                    v-model:value="filters.reason"
                                                    :placeholder="
                                                        $t('common.select_default_text', [
                                                            $t('inventory_adjustment.adjustment_reason'),
                                                        ])
                                                    "
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    optionFilterProp="title"
                                                    show-search
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="reason in adjustmentReasons"
                                                        :key="reason.xid"
                                                        :title="reason.name"
                                                        :value="reason.xid"
                                                    >
                                                        {{ reason.name }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>

                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'description'">
                                <a-typography-paragraph
                                    :ellipsis="{
                                        rows: 2,
                                        expandable: true,
                                        symbol: $t('common.more'),
                                    }"
                                    :content="record.description"
                                />
                            </template>
                            <template v-if="column.dataIndex === 'reason'">
                                <a-typography-paragraph
                                    :ellipsis="{
                                        rows: 2,
                                        expandable: true,
                                        symbol: $t('common.more'),
                                    }"
                                    :content="record.adjustment_reason ? record.adjustment_reason.name : '-'"
                                />
                            </template>
                            <template v-if="column.dataIndex === 'date'">
                                {{ formatDate(record.date) }}
                            </template>
                            <template v-if="column.dataIndex === 'created_at'">
                                {{ formatDateTime(record.created_at) }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('inventory_adjustments_edit') || 
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
                                        permsArray.includes('inventory_adjustments_delete') || 
                                        permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                    danger
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
import { ref, onMounted } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
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
            itemColumns,
            filterableColumns,
            hashableColumns,
            adjustmentReasons,
            getPrefetchData,
        } = fields();
        const { permsArray, formatDate, formatDateTime } = common();
        const crudVariables = crud();
        const filters = ref({
            reason: undefined,
        });
        const extraFilters = ref({
            dates: [],
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;

                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "inventory_adjustment";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
            });
            
            // Listen for add inventory adjustment event from parent
            window.addEventListener('add-inventory-adjustment', crudVariables.addItem);
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters: filters.value,
                dates: extraFilters.value.dates.length > 0 ? extraFilters.value.dates.join(",") : "",
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                reason: undefined,
            };
            extraFilters.value = {
                dates: [],
            };
            setUrlData();
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
            adjustmentReasons,
            itemColumns,
            formatDate,
            formatDateTime,
        };
    },
};
</script>