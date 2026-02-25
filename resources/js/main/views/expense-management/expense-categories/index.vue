<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.expense_categories`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <template v-if="permsArray.includes('expense_categories_create') || permsArray.includes('admin')">
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("expense_categories.add") }}
                    </a-button>
                </template>
                <a-button v-if="table.selectedRowKeys.length > 0 && (permsArray.includes('expense_categories_delete') || permsArray.includes('admin'))" type="primary" @click="showSelectedDeleteConfirm" danger>
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
                    {{ $t(`menu.expense_categories`) }}
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
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, record }">

                            <template v-if="column.dataIndex === 'description'">
                                <p style="text-align: justify; white-space: wrap;" >
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
                            <template v-if="column.dataIndex === 'is_active'">
                                <a-tag :color="record.is_active ? 'green' : 'red'">
                                    {{ record.is_active ? $t('common.active') : $t('common.inactive') }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="permsArray.includes('expense_categories_edit') || permsArray.includes('admin')"
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="(permsArray.includes('expense_categories_delete') || permsArray.includes('admin')) && (!record.children || record.children.length == 0)"
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
import { PlusOutlined, DeleteOutlined, EditOutlined } from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        AdminPageHeader,
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
        const crudVariables = crud();

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "expense_categories";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
        };
    },
};
</script>