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
            :items="items"
            :itemCategories="itemCategories"
            :itemBrands="itemBrands"
        />
        <a-row>
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.status"
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
                    <a-tab-pane key="true">
                        <template #tab>
                            <span>
                                <CheckOutlined />
                                {{ $t("common.active") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="false">
                        <template #tab>
                            <span>
                                <CloseOutlined />
                                {{ $t("common.inactive") }}
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
                            <template v-if="column.dataIndex === 'discount_type'">
                                {{ $t(`promotions.${record.discount_type}`) }}
                            </template>
                            <template v-if="column.dataIndex === 'discount_value'">
                                {{ record.discount_value }} {{ record.discount_type === 'percentage' ? '%' : '$' }}
                            </template>
                            <template v-if="column.dataIndex === 'is_active'">
                                <a-tag :color="record.is_active ? 'success' : 'error'">
                                    {{ record.is_active ? $t("common.active") : $t("common.inactive") }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('promotions_edit') || permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="
                                        (permsArray.includes('promotions_delete') ||
                                            permsArray.includes('admin'))
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
import { ref, onMounted, computed } from "vue";

import {
    PlusOutlined,
    DeleteOutlined,
    FileOutlined,
    CheckOutlined,
    CloseOutlined,
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
        FileOutlined,
        CheckOutlined,
        CloseOutlined,
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
            filterableColumns,
            hashableColumns,
            items,
            itemCategories,
            itemBrands,
            getPrefetchData,
        } = fields();
        const { permsArray } = common();
        const crudVariables = crud();

        const filters = ref({
            status: "",
        });

        const extraFilters = ref({
            dates: [],
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;
                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "promotions";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
            });
            
            // Listen for add promotion event from parent
            window.addEventListener('add-promotion', crudVariables.addItem);
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
                status: "",
            };
            extraFilters.value.dates = [];
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
            items,
            itemCategories,
            itemBrands,
        };
    },
};
</script>
