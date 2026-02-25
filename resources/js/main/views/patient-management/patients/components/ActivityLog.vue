<template>
    <div class="activity-log">
        <div class="mb-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold">
                {{ $t("activity_log.patient_activities") }}
            </h3>
            <a-button
                :icon="h(ReloadOutlined)"
                @click="refreshActivityLog"
                :loading="loading"
                size="small"
            >
                {{ $t("common.refresh") }}
            </a-button>
        </div>

        <!-- Filters -->
        <div class="mb-4">
            <a-row :gutter="[16, 16]">
                <a-col :xs="24" :sm="12" :md="8">
                    <a-select
                        v-model:value="filters.entity"
                        :placeholder="$t('activity_log.filter_by_entity')"
                        style="width: 100%"
                        allow-clear
                        @change="fetchActivities"
                    >
                        <a-select-option value="">{{
                            $t("common.all")
                        }}</a-select-option>
                        <a-select-option value="patients">{{
                            $t("menu.patients")
                        }}</a-select-option>
                        <a-select-option value="patient_messages">{{
                            $t("menu.messages")
                        }}</a-select-option>
                        <a-select-option value="patient_notes">{{
                            $t("menu.notes")
                        }}</a-select-option>
                        <a-select-option value="appointments">{{
                            $t("menu.appointments")
                        }}</a-select-option>
                        <a-select-option value="patient_files">{{
                            $t("patient_files.title")
                        }}</a-select-option>
                        <a-select-option value="addresses">{{
                            $t("addresses.title")
                        }}</a-select-option>
                        <a-select-option value="open_cases">{{
                            $t("open_cases.open_cases")
                        }}</a-select-option>
                    </a-select>
                </a-col>
                <a-col :xs="24" :sm="12" :md="8">
                    <a-select
                        v-model:value="filters.action"
                        :placeholder="$t('activity_log.filter_by_action')"
                        style="width: 100%"
                        allow-clear
                        @change="fetchActivities"
                    >
                        <a-select-option value="">{{
                            $t("common.all")
                        }}</a-select-option>
                        <a-select-option value="CREATED">{{
                            $t("activity_log.created")
                        }}</a-select-option>
                        <a-select-option value="UPDATED">{{
                            $t("activity_log.updated")
                        }}</a-select-option>
                        <a-select-option value="DELETED">{{
                            $t("activity_log.deleted")
                        }}</a-select-option>
                        <a-select-option value="ERROR">{{
                            $t("activity_log.error")
                        }}</a-select-option>
                    </a-select>
                </a-col>
                <a-col :xs="24" :sm="24" :md="8">
                    <a-range-picker
                        v-model:value="filters.dateRange"
                        style="width: 100%"
                        :placeholder="[
                            $t('common.start_date'),
                            $t('common.end_date'),
                        ]"
                        @change="fetchActivities"
                    />
                </a-col>
            </a-row>
        </div>

        <!-- Activity Timeline -->
        <a-spin :spinning="loading">
            <div
                v-if="activities.length === 0 && !loading"
                class="text-center py-8 text-gray-500"
            >
                <EyeOutlined class="text-4xl mb-2" />
                <p>{{ $t("activity_log.no_activities") }}</p>
            </div>

            <a-timeline v-else class="mt-4">
                <a-timeline-item
                    v-for="activity in activities"
                    :key="activity.id"
                    :color="getActivityColor(activity.action)"
                >
                    <template #dot>
                        <component
                            :is="getActivityIcon(activity.action)"
                            class="text-sm"
                        />
                    </template>
                    <div class="activity-item">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <p class="mb-1 font-medium text-gray-900">
                                    {{ activity.description }}
                                </p>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <a-tag
                                        :color="getEntityColor(activity.entity)"
                                        size="small"
                                    >
                                        {{ formatEntityName(activity.entity) }}
                                    </a-tag>
                                    <a-tag
                                        :color="getActionColor(activity.action)"
                                        size="small"
                                    >
                                        {{ activity.action }}
                                    </a-tag>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-400">
                                    {{ formatDate(activity.datetime) }}
                                </div>
                                <div
                                    v-if="activity.user"
                                    class="text-xs text-gray-500 mt-1"
                                >
                                    {{ getUserName(activity.user) }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div
                            v-if="activity.json_log && expandedItems.includes(activity.id)"
                            class="mt-2 p-2 bg-gray-50 rounded text-xs"
                        >
                            <pre>{{ formatJsonLog(activity.json_log) }}</pre>
                        </div>

                        <!-- Toggle Details Button -->
                        <a-button
                            v-if="activity.json_log && activity.json_log !== '{}'"
                            @click="toggleExpanded(activity.id)"
                            size="small"
                            type="link"
                            class="p-0 h-auto text-xs"
                        >
                            {{
                                expandedItems.includes(activity.id)
                                    ? $t("common.hide_details")
                                    : $t("common.show_details")
                            }}
                        </a-button>
                    </div>
                </a-timeline-item>
            </a-timeline>
        </a-spin>

        <!-- Load More Button -->
        <div
            v-if="hasMore && !loading"
            class="text-center mt-4"
        >
            <a-button @click="loadMore" :loading="loadingMore">
                {{ $t("common.load_more") }}
            </a-button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, h, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
    ReloadOutlined,
    EyeOutlined,
    PlusCircleOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    InfoCircleOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";

const axiosAdmin = window.axiosAdmin;

const props = defineProps({
    patientId: {
        type: String,
        required: true,
    },
    refreshTrigger: {
        type: [Boolean, Number],
        default: false,
    },
});

const { t } = useI18n();
const { formatDate } = common();

const loading = ref(false);
const loadingMore = ref(false);
const activities = ref([]);
const expandedItems = ref([]);
const currentPage = ref(1);
const hasMore = ref(true);

const filters = ref({
    entity: "",
    action: "",
    dateRange: null,
});

const fetchActivities = async (reset = true) => {
    if (reset) {
        loading.value = true;
        currentPage.value = 1;
        activities.value = [];
    } else {
        loadingMore.value = true;
    }

    try {
        const params = {
            patient_id: props.patientId,
            page: currentPage.value,
            limit: 20,
        };

        if (filters.value.entity) {
            params.entity = filters.value.entity;
        }

        if (filters.value.action) {
            params.action = filters.value.action;
        }

        if (filters.value.dateRange && filters.value.dateRange.length === 2) {
            params.start_date = filters.value.dateRange[0].format("YYYY-MM-DD");
            params.end_date = filters.value.dateRange[1].format("YYYY-MM-DD");
        }

        const response = await axiosAdmin.get("activity-logs", { params });

        if (reset) {
            activities.value = response.data;
        } else {
            activities.value.push(...response.data);
        }

        hasMore.value = response.data.length === params.limit;
    } catch (error) {
        console.error("Error fetching activity logs:", error);
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
};

const loadMore = () => {
    currentPage.value++;
    fetchActivities(false);
};

const refreshActivityLog = () => {
    fetchActivities(true);
};

const toggleExpanded = (activityId) => {
    const index = expandedItems.value.indexOf(activityId);
    if (index > -1) {
        expandedItems.value.splice(index, 1);
    } else {
        expandedItems.value.push(activityId);
    }
};

const getActivityIcon = (action) => {
    switch (action?.toUpperCase()) {
        case "CREATED":
            return PlusCircleOutlined;
        case "UPDATED":
            return EditOutlined;
        case "DELETED":
            return DeleteOutlined;
        case "ERROR":
            return ExclamationCircleOutlined;
        default:
            return InfoCircleOutlined;
    }
};

const getActivityColor = (action) => {
    switch (action?.toUpperCase()) {
        case "CREATED":
            return "green";
        case "UPDATED":
            return "blue";
        case "DELETED":
            return "red";
        case "ERROR":
            return "red";
        default:
            return "gray";
    }
};

const getEntityColor = (entity) => {
    switch (entity?.toLowerCase()) {
        case "patients":
            return "blue";
        case "patient_messages":
            return "green";
        case "patient_notes":
            return "orange";
        case "appointments":
            return "purple";
        case "patient_files":
            return "cyan";
        case "addresses":
            return "magenta";
        case "open_cases":
            return "red";
        default:
            return "default";
    }
};

const getActionColor = (action) => {
    switch (action?.toUpperCase()) {
        case "CREATED":
            return "success";
        case "UPDATED":
            return "processing";
        case "DELETED":
            return "error";
        case "ERROR":
            return "error";
        default:
            return "default";
    }
};

const formatEntityName = (entity) => {
    const entityMap = {
        patients: t("menu.patients"),
        patient_messages: t("menu.messages"),
        patient_notes: t("menu.notes"),
        appointments: t("menu.appointments"),
        patient_files: t("patient_files.title"),
        addresses: t("addresses.title"),
        open_cases: t("open_cases.open_cases"),
    };
    return entityMap[entity?.toLowerCase()] || entity;
};

const getUserName = (userJson) => {
    if (typeof userJson === "string") {
        try {
            const user = JSON.parse(userJson);
            return user.full_name || user.email || "Unknown User";
        } catch {
            return userJson;
        }
    }
    return userJson?.full_name || userJson?.email || "Unknown User";
};

const formatJsonLog = (jsonLog) => {
    if (typeof jsonLog === "string") {
        try {
            return JSON.stringify(JSON.parse(jsonLog), null, 2);
        } catch {
            return jsonLog;
        }
    }
    return JSON.stringify(jsonLog, null, 2);
};

// Watch for refresh trigger changes
watch(() => props.refreshTrigger, () => {
    fetchActivities(true);
});

onMounted(() => {
    fetchActivities(true);
});
</script>

<style scoped>
.activity-log {
    max-height: 600px;
    overflow-y: auto;
}

.activity-item {
    margin-bottom: 8px;
}

:deep(.ant-timeline-item-content) {
    margin-left: 8px;
}

:deep(.ant-timeline-item-tail) {
    border-left: 2px solid #f0f0f0;
}

:deep(.ant-timeline-item-head) {
    background-color: white;
    border: 2px solid #f0f0f0;
    padding: 4px;
}

:deep(.ant-timeline-item-head-custom) {
    line-height: 1;
}
</style>