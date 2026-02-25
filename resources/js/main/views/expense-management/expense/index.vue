<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.expenses`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <template v-if="permsArray.includes('expenses_create') || permsArray.includes('admin')">
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("expenses.add") }}
                    </a-button>
                </template>
                <a-button v-if="table.selectedRowKeys.length > 0 && (permsArray.includes('expenses_delete') || permsArray.includes('admin'))" type="primary" @click="showSelectedDeleteConfirm" danger>
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
                    {{ $t(`menu.expenses`) }}
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
                                <a-col class="ml-2">
                                    <Filters 
                                        @onReset="resetFilters"
                                        :filters="filters"
                                    >
                                        <a-col :span="24">
                                            <a-form-item :label="$t('expenses.category_id')">
                                                <a-select
                                                    v-model:value="filters.category_id"
                                                    :placeholder="$t('common.select_default_text', [$t('expenses.category_id')])"
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    optionFilterProp="title"
                                                    show-search
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="category in expenseCategories"
                                                        :key="category.xid"
                                                        :title="category.name"
                                                        :value="category.xid"
                                                    >
                                                        {{ category.name }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                            <a-form-item :label="$t('expenses.payment_type')">
                                                <a-select
                                                    v-model:value="filters.payment_type"
                                                    :placeholder="$t('common.select_default_text', [$t('expenses.payment_type')])"
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="type in paymentTypes"
                                                        :key="type.value"
                                                        :value="type.value"
                                                    >
                                                        {{ type.label }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
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
                            <template v-if="column.dataIndex === 'category'">
                                {{ record.category ? record.category.name : "" }}
                            </template>
                            <template v-if="column.dataIndex === 'notes'">
                                <p style="text-align: justify; white-space: wrap;" >
                                    <a-typography-paragraph
                                        :ellipsis="{
                                            rows: 2,
                                            expandable: true,
                                            symbol: $t('common.more'),
                                        }"
                                        :content="record.notes"
                                    />
                                </p>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="permsArray.includes('expenses_edit') || permsArray.includes('admin')"
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="(permsArray.includes('expenses_delete') || permsArray.includes('admin')) && (!record.children || record.children.length == 0)"
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
import { ref, onMounted } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { PlusOutlined, DeleteOutlined, EditOutlined, FileOutlined } from "@ant-design/icons-vue";
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
            getPrefetchData,
            expenseCategories,
            paymentTypes
        } = fields();
        const { permsArray } = common();
        const crudVariables = crud();
        
        const filters = ref({
            category_id: undefined,
            payment_type: undefined,
        });
        
        const extraFilters = ref({
            dates: [],
        });

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.table.filterableColumns = filterableColumns;

                crudVariables.crudUrl.value = addEditUrl;
                crudVariables.langKey.value = "expenses";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
            });
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters: filters.value,
                dates: extraFilters.value.dates.length ? extraFilters.value.dates.join(',') : '',
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };
        
        const resetFilters = () => {
            filters.value = {
                category_id: undefined,
                payment_type: undefined,
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
            expenseCategories,
            paymentTypes,
        };
    },
};
</script>
