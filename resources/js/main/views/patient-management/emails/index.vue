<template>
    <div>
        <a-row :gutter="16" class="mb-4">
            <a-col :span="24">
                <a-space style="width: 100%; justify-content: end;">
                    <a-button
                        type="primary"
                        @click="addItem"
                        v-if="
                            permsArray.includes('emails_create') ||
                            permsArray.includes('admin')
                        "
                    >
                        <template #icon><PlusOutlined /></template>
                        {{ $t("common.compose_email") }}
                    </a-button>
                </a-space>
            </a-col>
        </a-row>

        <!-- Filter Tabs -->
        <a-row class="mb-3">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.status"
                    @change="handleFilterChange"
                    class="email-filter-tabs"
                >
                    <template #rightExtra>
                        <a-button
                            type="text"
                            @click="setUrlData"
                        >
                            <template #icon><ReloadOutlined /></template>
                        </a-button>
                    </template>
                    <a-tab-pane key="">
                        <template #tab>
                            <span>
                                <FileOutlined />
                                {{ $t("common.all") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="draft">
                        <template #tab>
                            <span>
                                <EditOutlined />
                                {{ $t("common.draft") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="sent">
                        <template #tab>
                            <span>
                                <MailOutlined />
                                {{ $t("common.sent") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="failed">
                        <template #tab>
                            <span>
                                <CloseCircleOutlined />
                                {{ $t("common.failed") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>

        <!-- Email Cards List -->
        <a-spin :spinning="table.loading">
            <div v-if="table.data && table.data.length > 0" class="email-list">
                <div
                    v-for="email in table.data"
                    :key="email.xid"
                    class="email-card"
                    @click="viewEmail(email)"
                >
                    <div class="email-card-content">
                        <!-- Left: Sender Info -->
                        <div class="email-sender">
                            <a-avatar
                                v-if="email.sent_by_user && email.sent_by_user.profile_image_url"
                                :src="email.sent_by_user.profile_image_url"
                                :size="40"
                            />
                            <a-avatar
                                v-else
                                :size="40"
                                :style="{ backgroundColor: getAvatarColor(email.sent_by_user ? email.sent_by_user.name : 'System') }"
                            >
                                {{ getInitials(email.sent_by_user ? email.sent_by_user.name : 'System') }}
                            </a-avatar>
                        </div>

                        <!-- Middle: Email Details -->
                        <div class="email-details">
                            <div class="email-header">
                                <span class="email-sender-name">
                                    {{ email.sent_by_user ? email.sent_by_user.name : 'System' }}
                                </span>
                                <span class="email-time">{{ formatEmailTime(email.sent_at || email.created_at) }}</span>
                            </div>
                            <div class="email-subject">
                                {{ email.subject }}
                                <a-tag
                                    :color="getStatusColor(email.status)"
                                    size="small"
                                    class="ml-2"
                                >
                                    {{ getStatusText(email.status) }}
                                </a-tag>
                            </div>
                            <div class="email-preview">
                                {{ truncateText(email.body, 120) }}
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="email-actions" @click.stop>
                            <a-dropdown :trigger="['click']">
                                <a-button type="text" size="small">
                                    <MoreOutlined />
                                </a-button>
                                <template #overlay>
                                    <a-menu>
                                        <a-menu-item @click="viewEmail(email)">
                                            <EyeOutlined /> {{ $t("common.view") }}
                                        </a-menu-item>
                                        <a-menu-item
                                            v-if="email.status === 'draft'"
                                            @click="editItem(email)"
                                        >
                                            <EditOutlined /> {{ $t("common.edit") }}
                                        </a-menu-item>
                                        <a-menu-divider />
                                        <a-menu-item
                                            danger
                                            @click="showDeleteConfirm(email.xid)"
                                            v-if="
                                                permsArray.includes('emails_delete') ||
                                                permsArray.includes('admin')
                                            "
                                        >
                                            <DeleteOutlined /> {{ $t("common.delete") }}
                                        </a-menu-item>
                                    </a-menu>
                                </template>
                            </a-dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <a-empty
                v-else
                :description="$t('common.no_data')"
                style="margin: 40px 0"
            />
        </a-spin>

        <!-- Pagination -->
        <a-row v-if="table.data && table.data.length > 0" class="mt-4">
            <a-col :span="24" class="text-right">
                <a-pagination
                    v-model:current="table.pagination.current"
                    v-model:page-size="table.pagination.pageSize"
                    :total="table.pagination.total"
                    :show-size-changer="true"
                    :show-total="(total) => `Total ${total} emails`"
                    @change="handleTableChange"
                />
            </a-col>
        </a-row>

        <CreateModal
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

        <!-- Email View Modal -->
        <a-modal
            v-model:open="viewModalVisible"
            :title="selectedEmail?.subject"
            width="800px"
            :footer="null"
        >
            <div v-if="selectedEmail">
                <div class="email-modal-header mb-4">
                    <div class="flex items-center gap-3">
                        <a-avatar
                            v-if="selectedEmail.sent_by_user && selectedEmail.sent_by_user.profile_image_url"
                            :src="selectedEmail.sent_by_user.profile_image_url"
                            :size="48"
                        />
                        <a-avatar
                            v-else
                            :size="48"
                            :style="{ backgroundColor: getAvatarColor(selectedEmail.sent_by_user ? selectedEmail.sent_by_user.name : 'System') }"
                        >
                            {{ getInitials(selectedEmail.sent_by_user ? selectedEmail.sent_by_user.name : 'System') }}
                        </a-avatar>
                        <div>
                            <div class="font-medium">
                                {{ selectedEmail.sent_by_user ? selectedEmail.sent_by_user.name : 'System' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                To: {{ selectedEmail.recipient }}
                            </div>
                        </div>
                    </div>
                </div>

                <a-descriptions bordered :column="1" size="small" class="mb-4">
                    <a-descriptions-item :label="$t('common.status')">
                        <a-tag :color="getStatusColor(selectedEmail.status)">
                            {{ getStatusText(selectedEmail.status) }}
                        </a-tag>
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('common.sent_date')" v-if="selectedEmail.sent_at">
                        {{ formatDate(selectedEmail.sent_at) }}
                    </a-descriptions-item>
                </a-descriptions>

                <div class="email-body-content">
                    <div class="font-medium mb-2">{{ $t('common.body') }}</div>
                    <div class="email-body-text">
                        {{ selectedEmail.body }}
                    </div>
                </div>
            </div>
        </a-modal>
    </div>
</template>

<script>
import { ref, onMounted, onActivated, watch, computed } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
    EyeOutlined,
    MailOutlined,
    FileOutlined,
    CloseCircleOutlined,
    MoreOutlined,
    ReloadOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import CreateModal from "./CreateModal.vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

dayjs.extend(relativeTime);

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        EyeOutlined,
        MailOutlined,
        FileOutlined,
        CloseCircleOutlined,
        MoreOutlined,
        ReloadOutlined,
        CreateModal,
    },
    props: {
        patientId: {
            type: String,
            required: true,
        },
    },
    setup(props) {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
        } = fields();
        const { permsArray, formatDate } = common();
        const crudVariables = crud();

        const filters = ref({
            status: "",
            patient_id: props.patientId,
        });

        const viewModalVisible = ref(false);
        const selectedEmail = ref(null);

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "emails";
            crudVariables.initData.value = {
                ...initData,
                patient_id: props.patientId,
            };
            crudVariables.formData.value = {
                ...initData,
                patient_id: props.patientId,
            };

            setUrlData();
        });

        // Refetch when component becomes active (e.g., when tab is switched)
        onActivated(() => {
            if (crudVariables.tableUrl.value) {
                setUrlData();
            }
        });

        // Watch for patientId changes
        watch(() => props.patientId, (newId) => {
            if (newId) {
                filters.value.patient_id = newId;
                crudVariables.initData.value.patient_id = newId;
                crudVariables.formData.value.patient_id = newId;
                setUrlData();
            }
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const handleFilterChange = (key) => {
            filters.value.status = key;
            setUrlData();
        };

        const getStatusColor = (status) => {
            const colors = {
                draft: "default",
                scheduled: "blue",
                sent: "green",
                failed: "red",
            };
            return colors[status] || "default";
        };

        const getStatusText = (status) => {
            const statusMap = {
                draft: "Draft",
                scheduled: "Scheduled",
                sent: "Sent",
                failed: "Failed",
            };
            return statusMap[status] || status;
        };

        const viewEmail = (email) => {
            selectedEmail.value = email;
            viewModalVisible.value = true;
        };

        const addItem = () => {
            crudVariables.formData.value = {
                ...initData,
                patient_id: props.patientId,
            };
            crudVariables.addEditType.value = "add";
            crudVariables.addEditVisible.value = true;
            crudVariables.pageTitle.value = "Compose Email";
            crudVariables.successMessage.value = "Email created successfully";
        };

        const editItem = (record) => {
            crudVariables.formData.value = { ...record };
            crudVariables.addEditType.value = "edit";
            crudVariables.addEditVisible.value = true;
            crudVariables.pageTitle.value = "Edit Email";
            crudVariables.successMessage.value = "Email updated successfully";
        };

        const handleTableChange = (pagination) => {
            crudVariables.fetch({
                page: pagination.current || pagination,
            });
        };

        const truncateText = (text, length) => {
            if (!text) return '';
            if (text.length <= length) return text;
            return text.substring(0, length) + '...';
        };

        const getInitials = (name) => {
            if (!name) return 'S';
            const parts = name.split(' ');
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        };

        const getAvatarColor = (name) => {
            if (!name) return '#1890ff';
            const colors = [
                '#f56a00', '#7265e6', '#ffbf00', '#00a2ae',
                '#1890ff', '#52c41a', '#fa8c16', '#eb2f96',
                '#722ed1', '#13c2c2'
            ];
            let hash = 0;
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        };

        const formatEmailTime = (dateTime) => {
            if (!dateTime) return '';
            const date = dayjs(dateTime);
            const now = dayjs();
            
            if (date.isSame(now, 'day')) {
                return date.format('h:mm A');
            }
            if (date.isSame(now, 'year')) {
                return date.format('MMM D');
            }
            return date.format('MMM D, YYYY');
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            formatDate,
            filters,
            handleFilterChange,
            setUrlData,
            getStatusColor,
            getStatusText,
            viewModalVisible,
            selectedEmail,
            viewEmail,
            addItem,
            editItem,
            handleTableChange,
            truncateText,
            getInitials,
            getAvatarColor,
            formatEmailTime,
        };
    },
};
</script>

<style scoped>
.email-filter-tabs :deep(.ant-tabs-nav) {
    margin-bottom: 0;
}

.email-list {
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    overflow: hidden;
}

.email-card {
    border-bottom: 1px solid #e8e8e8;
    background: white;
    cursor: pointer;
    transition: all 0.2s;
}

.email-card:last-child {
    border-bottom: none;
}

.email-card:hover {
    background: #fafafa;
    box-shadow: inset 0 0 0 1px #1890ff;
}

.email-card-content {
    display: flex;
    align-items: flex-start;
    padding: 16px 20px;
    gap: 16px;
}

.email-sender {
    flex-shrink: 0;
}

.email-details {
    flex: 1;
    min-width: 0;
}

.email-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.email-sender-name {
    font-weight: 500;
    font-size: 14px;
    color: #262626;
}

.email-time {
    font-size: 12px;
    color: #8c8c8c;
    flex-shrink: 0;
    margin-left: 12px;
}

.email-subject {
    font-size: 14px;
    color: #262626;
    margin-bottom: 4px;
    font-weight: 400;
}

.email-preview {
    font-size: 13px;
    color: #595959;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.email-actions {
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.2s;
}

.email-card:hover .email-actions {
    opacity: 1;
}

.email-modal-header {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 16px;
}

.email-body-content {
    margin-top: 16px;
}

.email-body-text {
    white-space: pre-wrap;
    padding: 16px;
    background: #fafafa;
    border-radius: 4px;
    border: 1px solid #f0f0f0;
    max-height: 400px;
    overflow-y: auto;
    line-height: 1.6;
}
</style>
