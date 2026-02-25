<template>
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
                                <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
                                    <a-input-search
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
                                        permsArray.includes('doctor_departments_edit') ||
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
                                        (permsArray.includes('doctor_departments_delete') ||
                                            permsArray.includes('admin')) &&
                                        (!record.children || record.children.length == 0)
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
</template>

<script>
import { ref, onMounted, onUnmounted } from "vue";
import { PlusOutlined, EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        AddEdit,
    },
    setup() {
        const { url, addEditUrl, initData, columns, filterableColumns } = fields();
        const { permsArray } = common();
        const crudVariables = crud();
        
        // Add event listener for add department action
        const handleAddDepartment = () => {
            crudVariables.addItem();
        };

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;
            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "doctor_departments";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            setUrlData();
            
            // Listen for add department event
            window.addEventListener('add-department', handleAddDepartment);
        });
        
        onUnmounted(() => {
            window.removeEventListener('add-department', handleAddDepartment);
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
            };

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            setUrlData
        };
    },
}
</script>
