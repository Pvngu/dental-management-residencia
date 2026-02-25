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
                        :row-selection="{
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onRowSelectChange,
                            getCheckboxProps: (record) => ({
                                disabled:
                                    (permsArray.includes('patients_delete') ||
                                        permsArray.includes('admin')) &&
                                    record.xid != user.xid
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
                        :row-class-name="() => 'clickable-row'"
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
                                            v-model:value="extraFilters.filterColumn"
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
                                            v-model:value="extraFilters.searchString"
                                            show-search
                                            @change="fetchSearchData"
                                            @search="fetchSearchData"
                                            :loading="table.filterLoading"
                                            :placeholder="$t('common.placeholder_search_text', [$t('user.name') + ', ' + $t('user.email') + ', ' + $t('user.phone') + ', ' + $t('user.address')])"
                                        />
                                    </a-input-group>
                                </a-col>
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, text, record }">
                            <template v-if="column.dataIndex === 'patient'">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <user-info :user="record.user" :avatarSize="48" :showEmail="true" />
                                        <!-- isClickable @onClick="navigateToPatientDetails(record)" -->
                                        <!-- <a-badge 
                                            v-if="record.unread_messages_count > 0"
                                            :count="record.unread_messages_count"
                                            :number-style="{ backgroundColor: '#52c41a', fontSize: '10px', minWidth: '18px', height: '18px', lineHeight: '18px' }"
                                            class="absolute -top-1 -right-1 cursor-pointer"
                                            @click.stop="navigateToPatientMessages(record)"
                                            :title="$t('patients.unread_messages')"
                                        >
                                            <MessageOutlined 
                                                class="text-sm text-blue-500"
                                            />
                                        </a-badge> -->
                                    </div>
                                </div>
                            </template>
                            <template v-if="column.dataIndex === 'phone'">
                                {{ record?.user?.phone }}
                            </template>
                            <template v-if="column.dataIndex === 'address'">
                                <p v-if="record?.user?.default_address?.full_address" style="text-align: justify; white-space: wrap;">
                                    <a-typography-paragraph
                                        :ellipsis="{
                                            rows: 1,
                                            expandable: true,
                                            symbol: $t('common.more'),
                                        }"
                                        :content="record?.user?.default_address?.full_address"
                                    />
                                </p>
                                <span v-else class="text-gray-400">—</span>
                            </template>
                            <template v-if="column.dataIndex === 'date_of_birth'">
                                {{ formatDate(record?.user?.date_of_birth) }}
                            </template>
                            <template v-if="column.dataIndex === 'updated_at'">
                                {{ formatDate(record.updated_at) }}
                            </template>
                            <template v-if="column.dataIndex === 'created_at'">
                                {{ formatDate(record.created_at) }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('patients_edit') ||
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
                                        (permsArray.includes('patients_delete') ||
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
</template>
<script>
import { useRouter } from "vue-router";
import { onMounted, onUnmounted, ref } from "vue";
import { PlusOutlined, EditOutlined, DeleteOutlined, FileOutlined, ScheduleOutlined, MessageOutlined } from "@ant-design/icons-vue";
import fields from "./fields";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import AddEdit from "./AddEdit.vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import { debounce } from "lodash-es";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        FileOutlined,
        AddEdit,
        UserInfo,
        ScheduleOutlined,
        MessageOutlined,
    },
    setup() {
        const {
            indexUrl,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
        } = fields();
        const crudVariables = crud();
        const { permsArray, statusColors, formatDate, user } = common();
        const sampleFileUrl = window.config.staff_member_sample_file;
        const extraFilters = ref({
            searchString: undefined,
            filterColumn: undefined,
        });
        const filters = ref({
            status: "",
        });
        const router = useRouter();

        onMounted(() => {
            setUrlData();
            
            // Listen for add event from parent
            window.addEventListener('add-patient', handleAddEvent);
        });

        onUnmounted(() => {
            window.removeEventListener('add-patient', handleAddEvent);
        });

        const handleAddEvent = () => {
            crudVariables.addItem();
        };

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url: indexUrl,
                extraFilters,
                filters,
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "patients";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];
        };

        const fetchSearchData = debounce(() => {
            setUrlData();
        }, 400);

        const navigateToPatientDetails = (record) => {
            router.push({ 
                name: 'admin.patients.detail', 
                params: { id: record.xid } 
            });
        };

        const navigateToPatientMessages = (record) => {
            router.push({ 
                name: 'admin.patients.detail', 
                params: { 
                    id: record.xid,
                    tab: 'communication-records',
                    childtab: 'messages'
                } 
            });
        };

        return {
            user,
            columns,
            filterableColumns,
            permsArray,
            statusColors,
            formatDate,

            ...crudVariables,
            sampleFileUrl,
            setUrlData,

            extraFilters,
            fetchSearchData,
            navigateToPatientDetails,
            navigateToPatientMessages,
            filters,
        };
    },
};
</script>
