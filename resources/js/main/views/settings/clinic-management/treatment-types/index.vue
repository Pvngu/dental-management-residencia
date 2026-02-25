<template>
    <div class="treatment-types-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-button
                v-if="
                    permsArray.includes('treatment_types_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                @click="addItem"
            >
                <PlusOutlined />
                {{ $t("treatment_types.add") }}
            </a-button>
            <a-button
                v-if="
                    table.selectedRowKeys.length > 0 &&
                    (permsArray.includes('treatment_types_delete') ||
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
                            !permsArray.includes('treatment_types_delete') &&
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
                        <a-col class="ml-2">
                            <Filters @onReset="resetFilters" :filters="filters">
                                <a-col :span="24">
                                    <a-form-item
                                        :label="$t('treatment_types.category')"
                                    >
                                        <a-select
                                            v-model:value="filters.category"
                                            :placeholder="
                                                $t(
                                                    'common.select_default_text',
                                                    [
                                                        $t(
                                                            'treatment_types.category',
                                                        ),
                                                    ],
                                                )
                                            "
                                            :allowClear="true"
                                            style="width: 100%"
                                            optionFilterProp="title"
                                            show-search
                                            @change="setUrlData"
                                        >
                                            <a-select-option
                                                v-for="category in categoryOptions"
                                                :key="category"
                                                :title="category"
                                                :value="category"
                                            >
                                                {{ category }}
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                    <a-form-item
                                        :label="$t('treatment_types.status')"
                                    >
                                        <a-select
                                            v-model:value="filters.is_active"
                                            :placeholder="
                                                $t(
                                                    'common.select_default_text',
                                                    [
                                                        $t(
                                                            'treatment_types.status',
                                                        ),
                                                    ],
                                                )
                                            "
                                            :allowClear="true"
                                            style="width: 100%"
                                            optionFilterProp="title"
                                            show-search
                                            @change="setUrlData"
                                        >
                                            <a-select-option
                                                :key="true"
                                                :title="$t('common.enabled')"
                                                :value="true"
                                            >
                                                {{ $t("common.enabled") }}
                                            </a-select-option>
                                            <a-select-option
                                                :key="false"
                                                :title="$t('common.disabled')"
                                                :value="false"
                                            >
                                                {{ $t("common.disabled") }}
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                            </Filters>
                        </a-col>
                    </a-row>
                </template>
                <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'is_active'">
                        <a-tag :color="record.is_active ? 'success' : 'error'">
                            {{
                                record.is_active
                                    ? $t("common.enabled")
                                    : $t("common.disabled")
                            }}
                        </a-tag>
                    </template>
                    <template v-if="column.dataIndex === 'description'">
                        <p style="text-align: justify; white-space: wrap">
                            <a-typography-paragraph
                                :ellipsis="{
                                    rows: 2,
                                    expandable: true,
                                    symbol: $t('common.more'),
                                }"
                                :content="record.description"
                            />
                        </p>
                    </template>
                    <template v-if="column.dataIndex === 'price'">
                        {{ formatAmountCurrency(record.price) }}
                    </template>
                    <template v-if="column.dataIndex === 'duration_minutes'">
                        {{ record.duration_minutes }}
                        {{ $t("common.min") }}
                    </template>
                    <template v-if="column.dataIndex === 'action'">
                        <a-button
                            v-if="
                                permsArray.includes('treatment_types_edit') ||
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
                                permsArray.includes('treatment_types_delete') ||
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
    </div>
</template>
<script>
import { ref, onMounted } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../../common/components/common/select/Filters.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        Filters,
    },
    setup() {
        const { permsArray, formatAmountCurrency } = common();
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            categoryOptions,
        } = fields();
        const crudVariables = crud();

        const filters = ref({
            category: "",
            is_active: "",
        });

        onMounted(() => {
            crudVariables.tableUrl.value = {
                url,
                filters,
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "treatment_types";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters,
            };

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                category: "",
                is_active: "",
            };
            setUrlData();
        };

        return {
            permsArray,
            columns,
            ...crudVariables,
            filterableColumns,
            filters,
            setUrlData,
            resetFilters,
            categoryOptions,
            formatAmountCurrency,
        };
    },
};
</script>
