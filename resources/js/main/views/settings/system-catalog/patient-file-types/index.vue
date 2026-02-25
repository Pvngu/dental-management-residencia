<template>
    <div class="patient-file-types-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-button
                v-if="
                    permsArray.includes('patient_file_types_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                @click="addItem"
            >
                <PlusOutlined />
                {{ $t("patient_file_types.add") }}
            </a-button>
            <a-button
                v-if="
                    table.selectedRowKeys.length > 0 &&
                    (permsArray.includes('patient_file_types_delete') ||
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
                            !permsArray.includes('patient_file_types_delete') &&
                            !permsArray.includes('admin'),
                    }),
                }"
                bordered
                size="middle"
            >
                <template #title>
                    <a-row justify="end" align="middle" class="table-header">
                        <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
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
                        <p style="text-align: justify; white-space: wrap;">
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
                    <template v-if="column.dataIndex === 'action'">
                        <a-button
                            v-if="
                                permsArray.includes('patient_file_types_edit') ||
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
                                permsArray.includes(
                                    'patient_file_types_delete',
                                ) || permsArray.includes('admin')
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
    DeleteOutlined,
    EditOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../../common/composable/crud";
import common from "../../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
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
            crudVariables.langKey.value = "patient_file_types";
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
