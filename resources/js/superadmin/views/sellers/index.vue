<template>
    <SuperAdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.sellers`)" class="p-0!" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'superadmin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.sellers`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </SuperAdminPageHeader>

    <a-row>
        <a-col :span="24">
            <a-card class="page-content-container">
                <template #extra>
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("seller.add") }}
                    </a-button>
                </template>
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
                                    <a-row
                                        justify="end"
                                        align="middle"
                                        class="table-header"
                                    >
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
                                                    :placeholder="
                                                        $t(
                                                            'common.select_default_text',
                                                            [''],
                                                        )
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
                                                    @change="onTableSearch"
                                                    @search="onTableSearch"
                                                    :loading="table.loading"
                                                />
                                            </a-input-group>
                                        </a-col>
                                    </a-row>
                                </template>
                                <template #bodyCell="{ column, text, record }">
                                    <template v-if="column.dataIndex === 'name'">
                                        {{
                                            record.user
                                                ? [
                                                      record.user.name,
                                                      record.user.last_name,
                                                  ]
                                                      .filter(Boolean)
                                                      .join(" ")
                                                : ""
                                        }}
                                    </template>
                                    <template v-if="column.dataIndex === 'email'">
                                        {{ record.user ? record.user.email : "" }}
                                    </template>
                                    <template v-if="column.dataIndex === 'phone'">
                                        {{ record.user ? record.user.phone : "" }}
                                    </template>
                                    <template v-if="column.dataIndex === 'status'">
                                        <a-tag :color="statusColors[record.status]">
                                            {{ $t("common." + record.status) }}
                                        </a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'created_at'">
                                        {{ formatDateTime(record.created_at) }}
                                    </template>
                                    <template v-if="column.dataIndex === 'action'">
                                        <a-button
                                            type="primary"
                                            @click="editItem(record)"
                                            style="margin-left: 4px"
                                        >
                                            <template #icon><EditOutlined /></template>
                                        </a-button>
                                        <a-button
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
            </a-card>
        </a-col>
    </a-row>
</template>

<script>
import { ref, onMounted } from "vue";
import SuperAdminPageHeader from "../../layouts/SuperAdminPageHeader.vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        SuperAdminPageHeader,
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
        const { formatDateTime, statusColors } = common();
        const crudVariables = crud();

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "seller";
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

        const editItem = (record) => {
            crudVariables.formData.value = {
                name: record.user?.name || "",
                last_name: record.user?.last_name || "",
                email: record.user?.email || "",
                phone: record.user?.phone || "",
                gender: record.user?.gender || undefined,
                date_of_birth: record.user?.date_of_birth || undefined,
                status: record.user?.status || "enabled",
                address: record.user?.address || "",
                commission_rate: record.commission_rate || "",
                profile_image: undefined,
                profile_image_url: record.user?.profile_image_url || undefined,
            };

            crudVariables.editItem(record);
        };

        return {
            ...crudVariables,
            columns,
            filterableColumns,
            setUrlData,
            formatDateTime,
            statusColors,
            editItem,
        };
    },
};
</script>
