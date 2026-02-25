<template>
    <a-modal
        :open="visible"
        :title="$t('open_cases.case_details')"
        :footer="null"
        width="700px"
        centered
    >
        <a-spin :spinning="loading">
            <div v-if="caseData" class="case-modal-content">
                <!-- Priority and Status Tags -->
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <a-tag
                            :color="getPriorityColor(caseData.priority)"
                            class="priority-badge mr-2"
                        >
                            <ExclamationCircleOutlined class="mr-1" />
                            {{ $t(`open_cases.${caseData.priority}`) }}
                        </a-tag>
                        <a-tag :color="getStatusColor(caseData.status)">
                            {{ $t(`open_cases.${caseData.status}`) }}
                        </a-tag>
                    </div>
                    <div class="text-gray-500 text-sm">
                        <ClockCircleOutlined class="mr-1" />
                        {{ formatDateTime(caseData.created_at) }}
                    </div>
                </div>

                <!-- Case Title -->
                <h2 class="text-xl font-semibold mb-4">
                    {{ caseData.title }}
                </h2>

                <!-- Case Description -->
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                        {{ $t("open_cases.description") }}
                    </h4>
                    <p class="text-base text-gray-700 whitespace-pre-wrap">
                        {{ caseData.description }}
                    </p>
                </div>

                <!-- Patient Information -->
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-gray-600 mb-3">
                        {{ $t("patients.patient") }}
                    </h4>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <UserOutlined class="mr-2 text-gray-500" />
                            <span class="font-medium">
                                {{ caseData.patient?.name }}
                                {{ caseData.patient?.last_name }}
                            </span>
                        </div>
                        <div class="flex items-center">
                            <PhoneOutlined class="mr-2 text-gray-500" />
                            <span>{{ caseData.patient?.phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline Info -->
                <div class="timeline-info">
                    <a-timeline>
                        <a-timeline-item color="blue">
                            <template #dot>
                                <ClockCircleOutlined />
                            </template>
                            <p class="text-sm">
                                <span class="font-semibold">{{
                                    $t("common.created_at")
                                }}</span>
                                <br />
                                {{ formatDateTime(caseData.created_at) }}
                            </p>
                        </a-timeline-item>
                        <a-timeline-item
                            v-if="caseData.updated_at !== caseData.created_at"
                            color="green"
                        >
                            <template #dot>
                                <ClockCircleOutlined />
                            </template>
                            <p class="text-sm">
                                <span class="font-semibold">{{
                                    $t("common.updated_at")
                                }}</span>
                                <br />
                                {{ formatDateTime(caseData.updated_at) }}
                            </p>
                        </a-timeline-item>
                    </a-timeline>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end gap-2">
                    <a-button
                        v-if="
                            permsArray.includes('open_cases_edit') ||
                            permsArray.includes('admin')
                        "
                        type="primary"
                        @click="handleEdit"
                    >
                        <template #icon><EditOutlined /></template>
                        {{ $t("common.edit") }}
                    </a-button>
                    <a-button @click="handleClose">
                        {{ $t("common.close") }}
                    </a-button>
                </div>
            </div>
        </a-spin>
    </a-modal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
    UserOutlined,
    PhoneOutlined,
    ClockCircleOutlined,
    ExclamationCircleOutlined,
    EditOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    caseData: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:visible", "edit"]);

const { t } = useI18n();
const { permsArray, formatDateTime } = common();

const loading = ref(false);

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

const handleEdit = () => {
    emit("edit", props.caseData);
    handleClose();
};

const handleClose = () => {
    emit("update:visible", false);
};
</script>

<style scoped>
.case-modal-content {
    padding: 8px 0;
}

.priority-badge {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
}

.timeline-info {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.space-y-2 > * + * {
    margin-top: 0.5rem;
}
</style>
