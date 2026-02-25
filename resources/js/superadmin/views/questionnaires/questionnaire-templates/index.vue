<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.questionnaire_templates`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <a-button type="primary" @click="addItem">
                    <PlusOutlined />
                    {{ $t("questionnaire_templates.add") }}
                </a-button>
                <a-button
                    v-if="table.selectedRowKeys.length > 0"
                    type="primary"
                    @click="showSelectedDeleteConfirm"
                    danger
                >
                    <template #icon><DeleteOutlined /></template>
                    {{ $t("common.delete") }}
                </a-button>
            </a-space>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'superadmin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.questionnaire_templates`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
        />
        <!-- Search Bar -->
        <a-row class="mt-5 mb-4">
            <a-col :span="24">
                <a-row justify="end" align="middle">
                    <a-col 
                        :xs="24"
                        :sm="16"
                        :md="12"
                        :lg="8"
                        :xl="6"
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
                </a-row>
            </a-col>
        </a-row>

        <!-- Cards Grid -->
        <a-row :gutter="[16, 16]" v-if="!table.loading && table.data.length > 0">
            <a-col 
                v-for="record in table.data" 
                :key="record.xid"
                :xs="24" 
                :sm="12" 
                :md="8" 
                :lg="6" 
                :xl="6"
            >
                <a-card 
                    class="questionnaire-card rounded-xl transition-all duration-300 cursor-pointer border border-gray-200 min-h-[280px]"
                    :class="{ 'border-blue-500 shadow-blue-200 shadow-md': table.selectedRowKeys.includes(record.xid) }"
                    hoverable
                >
                    <!-- Card Header -->
                    <template #title>
                        <div class="flex items-center justify-between mb-2">
                            <a-checkbox 
                                :checked="table.selectedRowKeys.includes(record.xid)"
                                @click.stop="toggleCardSelection(record.xid)"
                                class="mr-2"
                            />
                            <a-tag 
                                :color="record.code ? 'blue' : 'default'"
                                class="font-semibold text-xs rounded-md"
                            >
                                {{ record.code || 'N/A' }}
                            </a-tag>
                        </div>
                    </template>

                    <!-- Card Actions -->
                    <template #extra>
                        <a-dropdown>
                            <a-button type="text" size="small">
                                <MoreOutlined />
                            </a-button>
                            <template #overlay>
                                <a-menu>
                                    <a-menu-item @click="openBuilder(record)">
                                        <EyeOutlined />
                                        {{ $t("common.view") }}
                                    </a-menu-item>
                                    <a-menu-item @click="
                                    () => {
                                        $router.push({ name: 'superadmin.questionnaire-templates.builder', params: { xid: record.xid } })
                                    }">
                                        <FormOutlined />
                                        {{ $t("questionnaire_templates.builder") }}
                                    </a-menu-item>
                                    <a-menu-item @click="editItem(record)">
                                        <EditOutlined />
                                        {{ $t("common.edit") }}
                                    </a-menu-item>
                                    <a-menu-item @click="duplicateItem(record)">
                                        <CopyOutlined />
                                        {{ $t("common.duplicate") }}
                                    </a-menu-item>
                                    <a-menu-divider />
                                    <a-menu-item danger @click="showDeleteConfirm(record.xid)">
                                        <DeleteOutlined />
                                        {{ $t("common.delete") }}
                                    </a-menu-item>
                                </a-menu>
                            </template>
                        </a-dropdown>
                    </template>

                    <!-- Card Content -->
                    <div class="p-0">
                        <h3 class="text-base font-semibold mb-1 text-gray-900 leading-tight">{{ record.name || 'Untitled Template' }}</h3>
                        <p class="text-xs text-gray-500 mb-4">v{{ record.version || '1.1' }}</p>
                        
                        <div class="mb-4">
                            <div class="flex items-center mb-2 text-xs">
                                <span class="text-gray-500 mr-1 text-xs">{{ $t("questionnaire_templates.target_population") }}:</span>
                                <a-tag color="blue" size="small">{{ record.target_population || 'Todos' }}</a-tag>
                            </div>
                            
                            <div class="flex items-center mb-2 text-xs" v-if="record.estimated_duration">
                                <ClockCircleOutlined />
                                <span class="ml-1">{{ record.estimated_duration }} min</span>
                            </div>
                            
                            <div class="mb-2" v-if="record.description">
                                <span class="text-gray-600 text-xs leading-tight line-clamp-2">{{ record.description }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between mb-4 py-2 border-t border-b border-gray-100">
                            <div class="text-center flex-1">
                                <span class="block text-xs text-gray-500 mb-0.5">{{ $t("questionnaire_templates.sections") }}:</span>
                                <span class="block text-sm font-semibold text-gray-900">6</span>
                            </div>
                            <div class="text-center flex-1" v-if="record.normative_ref">
                                <span class="block text-xs text-gray-500 mb-0.5">{{ $t("questionnaire_templates.normative_ref") }}:</span>
                                <span class="block text-sm font-semibold text-gray-900">{{ record.normative_ref }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-3 md:flex-row flex-col gap-2 md:gap-0">
                            <div class="flex gap-1 flex-wrap justify-center md:justify-start">
                                <a-tag v-if="record.is_active" color="success" size="small">
                                    {{ $t("common.active") }}
                                </a-tag>
                                <a-tag v-else color="error" size="small">
                                    {{ $t("common.inactive") }}
                                </a-tag>
                                
                                <a-tag v-if="record.is_evergreen" color="blue" size="small">
                                    {{ $t("common.evergreen") }}
                                </a-tag>
                            </div>
                            
                            <div class="flex gap-2 justify-center md:justify-end">
                                <a-button 
                                    type="primary" 
                                    size="small" 
                                    @click.stop="viewItem(record)"
                                    ghost
                                    class="h-7 text-xs px-2"
                                >
                                    <EyeOutlined />
                                    {{ $t("common.view") }}
                                </a-button>
                                <a-button 
                                    type="primary" 
                                    size="small" 
                                    @click.stop="editItem(record)"
                                    class="h-7 text-xs px-2"
                                >
                                    <EditOutlined />
                                    {{ $t("common.edit") }}
                                </a-button>
                            </div>
                        </div>
                    </div>
                </a-card>
            </a-col>
        </a-row>

        <!-- Loading State -->
        <a-row v-if="table.loading" class="mt-5">
            <a-col :span="24" class="text-center py-12">
                <a-spin size="large" />
                <p class="mt-3">{{ $t("common.loading") }}</p>
            </a-col>
        </a-row>

        <!-- Empty State -->
        <a-row v-if="!table.loading && table.data.length === 0" class="mt-5">
            <a-col :span="24" class="text-center py-12">
                <a-empty :description="$t('common.no_data')" />
            </a-col>
        </a-row>

        <!-- Pagination -->
        <a-row v-if="!table.loading && table.data.length > 0" class="mt-4">
            <a-col :span="24" class="text-center">
                <a-pagination
                    v-model:current="table.pagination.current"
                    v-model:pageSize="table.pagination.pageSize"
                    :total="table.pagination.total"
                    :show-size-changer="true"
                    :show-quick-jumper="true"
                    :show-total="(total, range) => `${range[0]}-${range[1]} of ${total} items`"
                    @change="handleTableChange"
                    @showSizeChange="handleTableChange"
                />
            </a-col>
        </a-row>
    </admin-page-table-content>
</template>

<script>
import { onMounted, ref } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    EyeOutlined,
    CopyOutlined,
    MoreOutlined,
    ClockCircleOutlined,
    FormOutlined,
} from "@ant-design/icons-vue";
import fields from "./fields";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        EyeOutlined,
        CopyOutlined,
        MoreOutlined,
        ClockCircleOutlined,
        FormOutlined,
        AddEdit,
        AdminPageHeader,
    },
    setup() {
        const { permsArray, formatDateTime } = common();
        const { url, addEditUrl, initData, columns, filterableColumns } = fields();
        const crudVariables = crud(); 

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "questionnaire_templates";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [];

            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
            };

            crudVariables.hashableColumns.value = [];

            crudVariables.fetch({
                page: 1,
            });
        };

        const toggleCardSelection = (xid) => {
            const index = crudVariables.table.selectedRowKeys.indexOf(xid);
            if (index > -1) {
                crudVariables.table.selectedRowKeys.splice(index, 1);
            } else {
                crudVariables.table.selectedRowKeys.push(xid);
            }
        };

        const viewItem = (record) => {
            // Add view functionality
            console.log('View item:', record);
        };

        const duplicateItem = (record) => {
            // Add duplicate functionality
            console.log('Duplicate item:', record);
        };

        const handleTableChange = (pagination, filters, sorter) => {
            crudVariables.fetch({
                page: pagination.current,
                pageSize: pagination.pageSize,
            });
        };

        return {
            columns,
            ...crudVariables,
            filterableColumns,
            permsArray,
            formatDateTime,
            setUrlData,
            toggleCardSelection,
            viewItem,
            duplicateItem,
            handleTableChange,
        };
    },
};
</script>

<style scoped>
/* Keep only the deep selectors that can't be replaced with Tailwind */
:deep(.ant-card-head) {
    padding: 12px 16px 8px 16px;
    border-bottom: none;
    min-height: auto;
}

:deep(.ant-card-head-title) {
    padding: 0;
    font-size: 14px;
}

:deep(.ant-card-body) {
    padding: 0 16px 16px 16px;
}

:deep(.ant-card-extra) {
    padding: 0;
}
</style>
