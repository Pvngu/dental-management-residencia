<template>
    <div class="table-responsive">
        <a-table
            :row-selection="{
                selectedRowKeys: table.selectedRowKeys,
                onChange: onRowSelectChange,
                getCheckboxProps: (record) => ({
                    disabled:
                        ((panelType == 'admin' &&
                            (permsArray.includes('currencies_delete') ||
                                permsArray.includes('admin'))) ||
                            panelType == 'superadmin') &&
                        appSetting.x_currency_id != record.xid
                            ? false
                            : true,
                    name: record.xid,
                }),
            }"
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
                    <a-col :xs="24" :sm="24" :md="16" :lg="12" :xl="10">
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
                                show-search
                                @change="onTableSearch"
                                @search="onTableSearch"
                                :loading="table.filterLoading"
                            />
                        </a-input-group>
                    </a-col>
                </a-row>
            </template>
            <template #bodyCell="{ column, record }">
                <template v-if="column.dataIndex === 'action'">
                    <a-button
                        v-if="
                            (panelType == 'admin' &&
                                (permsArray.includes('currencies_edit') ||
                                    permsArray.includes('admin'))) ||
                            panelType == 'superadmin'
                        "
                        type="primary"
                        @click="editItem(record)"
                        style="margin-left: 4px"
                    >
                        <EditOutlined />
                    </a-button>
                    <a-button
                        v-if="
                            ((panelType == 'admin' &&
                                (permsArray.includes('currencies_delete') ||
                                    permsArray.includes('admin'))) ||
                                panelType == 'superadmin') &&
                            appSetting.x_currency_id != record.xid
                        "
                        type="primary"
                        @click="showDeleteConfirm(record.xid)"
                        style="margin-left: 4px"
                        danger
                    >
                        <DeleteOutlined />
                    </a-button>
                </template>
            </template>
        </a-table>
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
</template>

<script>
import { onMounted } from "vue";
import { EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";

export default {
    props: {
        panelType: {
            type: String,
            default: "admin",
        },
    },
    components: {
        EditOutlined,
        DeleteOutlined,
        AddEdit,
    },
    setup(props) {
        const { permsArray, appSetting } = common();
        const { url, addEditUrl, initData, columns, filterableColumns } =
            fields(props.panelType);
        const crudVariables = crud();

        onMounted(() => {
            crudVariables.tableUrl.value = {
                url,
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "currency";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
        });

        return {
            permsArray,
            appSetting,
            columns,
            ...crudVariables,
            filterableColumns,
        };
    },
};
</script>
