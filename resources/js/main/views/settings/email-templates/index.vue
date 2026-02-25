<template>
    <div class="email-templates-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-button
                v-if="
                    permsArray.includes('email_templates_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                @click="addItem"
            >
                <PlusOutlined />
                {{ $t("email_template.add") }}
            </a-button>
            <a-button
                v-if="
                    table.selectedRowKeys.length > 0 &&
                    (permsArray.includes('email_templates_delete') ||
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
                :row-selection="{
                    selectedRowKeys: table.selectedRowKeys,
                    onChange: onRowSelectChange,
                    getCheckboxProps: (record) => ({
                        disabled: false,
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
                        <a-col :xs="24" :sm="24" :md="16" :lg="12" :xl="8">
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
                    <template v-if="column.dataIndex === 'status'">
                        <UpdateStatus
                            :status="record.status"
                            :xid="record.xid"
                            @success="fetch"
                        />
                    </template>
                    <template v-if="column.dataIndex === 'action'">
                        <a-button
                            v-if="
                                permsArray.includes('email_templates_edit') ||
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
                                (permsArray.includes(
                                    'email_templates_delete',
                                ) ||
                                    permsArray.includes('admin')) &&
                                (!record.children ||
                                    record.children.length == 0)
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
import { onMounted } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import UpdateStatus from "./UpdateStatus.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
        UpdateStatus,
    },
    setup() {
        const { permsArray, appSetting } = common();
        const { url, addEditUrl, initData, columns, filterableColumns } =
            fields();
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
            crudVariables.langKey.value = "email_template";
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
