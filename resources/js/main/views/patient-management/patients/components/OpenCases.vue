<template>
    <div class="open-cases-tab">
        <a-spin :spinning="loading">
            <!-- Header with Add Case Button -->
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">
                    {{ $t("open_cases.patient_cases") }}
                </h3>
                <a-button
                    type="primary"
                    @click="handleAddCase"
                    v-if="
                        permsArray.includes('open_cases_create') ||
                        permsArray.includes('admin')
                    "
                >
                    <template #icon><PlusOutlined /></template>
                    {{ $t("open_cases.add") }}
                </a-button>
            </div>

            <!-- No Cases State -->
            <div v-if="patientCases.length === 0" class="text-center py-12">
                <a-empty :description="$t('open_cases.no_cases_for_patient')">
                    <a-button
                        type="primary"
                        @click="handleAddCase"
                        v-if="
                            permsArray.includes('open_cases_create') ||
                            permsArray.includes('admin')
                        "
                    >
                        {{ $t("open_cases.create_first_case") }}
                    </a-button>
                </a-empty>
            </div>

            <!-- Cases List -->
            <a-row v-else :gutter="[16, 16]">
                <a-col
                    v-for="caseItem in patientCases"
                    :key="caseItem.xid"
                    :xs="24"
                    :sm="24"
                    :md="12"
                    :lg="12"
                    :xl="12"
                >
                    <a-card
                        :bordered="true"
                        class="case-card"
                        :class="[
                            `priority-${caseItem.priority}`,
                            `status-${caseItem.status}`,
                        ]"
                        hoverable
                    >
                        <!-- Card Header with Priority Badge -->
                        <div class="card-header mb-3">
                            <a-tag
                                :color="getPriorityColor(caseItem.priority)"
                                class="priority-badge"
                            >
                                {{ $t(`open_cases.${caseItem.priority}`) }}
                            </a-tag>
                            <a-tag
                                :color="getStatusColor(caseItem.status)"
                                class="ml-2"
                            >
                                {{ $t(`open_cases.${caseItem.status}`) }}
                            </a-tag>
                        </div>

                        <!-- Case Title -->
                        <h3 class="case-title mb-2">
                            {{ caseItem.title }}
                        </h3>

                        <!-- Case Description -->
                        <p class="case-description mb-3">
                            <a-typography-paragraph
                                :ellipsis="{
                                    rows: 3,
                                    expandable: true,
                                    symbol: $t('common.more'),
                                }"
                                :content="caseItem.description"
                            />
                        </p>

                        <!-- Date Info -->
                        <div class="date-info mb-3">
                            <ClockCircleOutlined class="mr-1" />
                            <span class="text-gray-500">
                                {{ formatDateTime(caseItem.created_at) }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card-actions mb-3">
                            <a-space>
                                <a-button
                                    v-if="
                                        permsArray.includes(
                                            'open_cases_edit'
                                        ) || permsArray.includes('admin')
                                    "
                                    type="primary"
                                    size="small"
                                    @click="handleEditCase(caseItem)"
                                >
                                    <template #icon><EditOutlined /></template>
                                    {{ $t("common.edit") }}
                                </a-button>
                                <a-button
                                    v-if="
                                        permsArray.includes(
                                            'open_cases_delete'
                                        ) || permsArray.includes('admin')
                                    "
                                    type="primary"
                                    danger
                                    size="small"
                                    @click="handleDeleteCase(caseItem.xid)"
                                >
                                    <template #icon
                                        ><DeleteOutlined
                                    /></template>
                                </a-button>
                            </a-space>
                        </div>

                        <!-- History Section -->
                        <a-collapse
                            v-if="caseItem.histories && caseItem.histories.length > 0"
                            :bordered="false"
                            class="case-history-collapse"
                        >
                            <a-collapse-panel
                                key="1"
                                :header="$t('open_cases.view_history')"
                            >
                                <a-timeline mode="left" class="history-timeline">
                                    <a-timeline-item
                                        v-for="history in caseItem.histories"
                                        :key="history.xid"
                                        :color="getHistoryColor(history.action)"
                                    >
                                        <template #dot>
                                            <component :is="getHistoryIcon(history.action)" />
                                        </template>
                                        <div class="history-item">
                                            <div class="history-action">
                                                <strong>{{ getHistoryActionText(history.action) }}</strong>
                                            </div>
                                            <div v-if="history.field_name" class="history-details">
                                                <span class="field-name">{{ $t(`open_cases.${history.field_name}`) }}:</span>
                                                <span class="old-value">{{ history.old_value }}</span>
                                                <ArrowRightOutlined class="mx-1" />
                                                <span class="new-value">{{ history.new_value }}</span>
                                            </div>
                                            <div class="history-meta">
                                                <UserOutlined class="mr-1" />
                                                <span>{{ history.user?.name || $t('common.system') }}</span>
                                                <ClockCircleOutlined class="ml-2 mr-1" />
                                                <span>{{ formatDateTime(history.created_at) }}</span>
                                            </div>
                                        </div>
                                    </a-timeline-item>
                                </a-timeline>
                            </a-collapse-panel>
                        </a-collapse>
                    </a-card>
                </a-col>
            </a-row>
        </a-spin>

        <!-- Add/Edit Case Drawer -->
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="handleCaseSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { Modal } from "ant-design-vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ClockCircleOutlined,
    ExclamationCircleOutlined,
    UserOutlined,
    ArrowRightOutlined,
    CheckCircleOutlined,
    SyncOutlined,
    FileAddOutlined,
    CloseCircleOutlined,
    RiseOutlined,
    FallOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";
import fields from "../../../open-cases/fields";
import AddEdit from "../../../open-cases/AddEdit.vue";

const props = defineProps({
    patientId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['casesUpdated']);

const { t } = useI18n();
const { permsArray, formatDateTime } = common();
const { url: fieldsUrl, addEditUrl, initData } = fields();
const axiosAdmin = window.axiosAdmin;

const loading = ref(false);
const patientCases = ref([]);
const addEditVisible = ref(false);
const addEditType = ref("add");
const formData = ref({ ...initData });
const viewData = ref({});

const pageTitle = computed(() => {
    return addEditType.value === "add"
        ? t("open_cases.add")
        : t("open_cases.edit");
});

const successMessage = computed(() => {
    return addEditType.value === "add"
        ? t("open_cases.created")
        : t("open_cases.updated");
});

onMounted(() => {
    fetchPatientCases();
});

const fetchPatientCases = () => {
    loading.value = true;
    axiosAdmin
        .get(`${addEditUrl}?patient_id=${props.patientId}&fields=id,xid,title,description,priority,status,x_patient_id,created_at,updated_at`)
        .then((res) => {
            patientCases.value = res.data.data || [];
        })
        .catch((error) => {
            console.error("Error fetching patient cases:", error);
            patientCases.value = [];
        })
        .finally(() => {
            loading.value = false;
        });
};

const handleAddCase = () => {
    addEditType.value = "add";
    formData.value = {
        ...initData,
        patient_id: props.patientId,
    };
    viewData.value = {};
    addEditVisible.value = true;
};

const handleEditCase = (caseItem) => {
    addEditType.value = "edit";
    formData.value = {
        ...caseItem,
        patient_id: caseItem.x_patient_id,
        _method: "PUT",
    };
    viewData.value = caseItem;
    addEditVisible.value = true;
};

const handleDeleteCase = (caseId) => {
    Modal.confirm({
        title: t("open_cases.delete_message"),
        icon: h(ExclamationCircleOutlined),
        okText: t("common.yes"),
        okType: "danger",
        cancelText: t("common.no"),
        onOk() {
            return axiosAdmin.delete(`${addEditUrl}/${caseId}`).then(() => {
                fetchPatientCases();
                emit('casesUpdated'); // Notify parent to refresh active cases count
            });
        },
    });
};

const handleCaseSuccess = () => {
    addEditVisible.value = false;
    fetchPatientCases();
    emit('casesUpdated'); // Notify parent to refresh active cases count
};

const onCloseAddEdit = () => {
    addEditVisible.value = false;
    formData.value = { ...initData };
    viewData.value = {};
};

const getPriorityColor = (priority) => {
    const colors = {
        critical: "red",
        high: "orange",
        medium: "blue",
        low: "green",
    };
    return colors[priority] || "default";
};

const getStatusColor = (status) => {
    const colors = {
        open: "blue",
        in_progress: "orange",
        resolved: "green",
        closed: "default",
    };
    return colors[status] || "default";
};

const getHistoryColor = (action) => {
    const colors = {
        created: "blue",
        updated: "gray",
        status_changed: "purple",
        priority_changed: "orange",
        resolved: "green",
        reopened: "orange",
        deleted: "red",
        restored: "blue",
    };
    return colors[action] || "gray";
};

const getHistoryIcon = (action) => {
    const icons = {
        created: FileAddOutlined,
        updated: SyncOutlined,
        status_changed: SyncOutlined,
        priority_changed: action === "priority_changed" ? RiseOutlined : FallOutlined,
        resolved: CheckCircleOutlined,
        reopened: SyncOutlined,
        deleted: CloseCircleOutlined,
        restored: SyncOutlined,
    };
    return icons[action] || SyncOutlined;
};

const getHistoryActionText = (action) => {
    const texts = {
        created: t('open_cases.history_created'),
        updated: t('open_cases.history_updated'),
        status_changed: t('open_cases.history_status_changed'),
        priority_changed: t('open_cases.history_priority_changed'),
        resolved: t('open_cases.history_resolved'),
        reopened: t('open_cases.history_reopened'),
        deleted: t('open_cases.history_deleted'),
        restored: t('open_cases.history_restored'),
    };
    return texts[action] || action;
};
</script>

<script>
import { h } from "vue";
export default {
    name: "OpenCases",
};
</script>

<style scoped>
.open-cases-tab {
    padding: 16px 0;
}

.case-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.case-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.case-card.priority-critical {
    border-left: 4px solid #ff4d4f;
}

.case-card.priority-high {
    border-left: 4px solid #ff7a45;
}

.case-card.priority-medium {
    border-left: 4px solid #1890ff;
    padding-bottom: 8px;
}

.case-history-collapse {
    margin-top: 8px;
    background: transparent;
}

.case-history-collapse :deep(.ant-collapse-item) {
    border: none;
    background: #f9fafb;
    border-radius: 4px;
}

.case-history-collapse :deep(.ant-collapse-header) {
    padding: 8px 12px !important;
    font-size: 13px;
    font-weight: 500;
    color: #1890ff;
}

.case-history-collapse :deep(.ant-collapse-content) {
    background: #f9fafb;
    border-top: 1px solid #e8eaed;
}

.case-history-collapse :deep(.ant-collapse-content-box) {
    padding: 12px;
}

.history-timeline :deep(.ant-timeline-item) {
    padding-bottom: 16px;
}

.history-item {
    font-size: 13px;
}

.history-action {
    margin-bottom: 4px;
    color: #262626;
}

.history-details {
    margin-bottom: 6px;
    color: #595959;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.history-details .field-name {
    font-weight: 500;
    margin-right: 6px;
}

.history-details .old-value {
    color: #ff4d4f;
    text-decoration: line-through;
}

.history-details .new-value {
    color: #52c41a;
    font-weight: 500;
}

.history-meta {
    font-size: 12px;
    color: #8c8c8c;
    display: flex;
    align-items: center;
}

.case-card.priority-low {
    border-left: 4px solid #52c41a;
}

.card-header {
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.priority-badge {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
}

.case-title {
    font-size: 16px;
    font-weight: 600;
    color: #262626;
    margin-bottom: 8px;
    line-height: 1.4;
}

.case-description {
    color: #595959;
    font-size: 14px;
    line-height: 1.6;
}

.date-info {
    font-size: 12px;
    color: #8c8c8c;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.card-actions {
    margin-top: auto;
    padding-top: 12px;
}
</style>
