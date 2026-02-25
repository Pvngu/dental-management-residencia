<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.open_cases`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <template
                    v-if="
                        permsArray.includes('open_cases_create') ||
                        permsArray.includes('admin')
                    "
                >
                    <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("open_cases.add") }}
                    </a-button>
                </template>
            </a-space>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.open_cases`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
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

        <!-- Statistics Cards -->
        <a-row :gutter="[16, 16]" class="mb-4 mt-5">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('open_cases.total_cases')"
                    :value="stats.total || 0"
                    :loading="loading"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('open_cases.critical')"
                    :value="stats.critical || 0"
                    :loading="loading"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('open_cases.high')"
                    :value="stats.high || 0"
                    :loading="loading"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('open_cases.open')"
                    :value="stats.open || 0"
                    :loading="loading"
                />
            </a-col>
        </a-row>

        <!-- Priority Tabs -->
        <a-row>
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.priority"
                    @change="setUrlData"
                    centered
                    type="card"
                    class="table-tab-filters"
                >
                    <a-tab-pane key="">
                        <template #tab>
                            <span>
                                <FileOutlined />
                                {{ $t("common.all") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="critical">
                        <template #tab>
                            <span>
                                <ExclamationCircleOutlined />
                                {{ $t("open_cases.critical") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="high">
                        <template #tab>
                            <span>
                                <WarningOutlined />
                                {{ $t("open_cases.high") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="medium">
                        <template #tab>
                            <span>
                                <InfoCircleOutlined />
                                {{ $t("open_cases.medium") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="low">
                        <template #tab>
                            <span>
                                <MinusCircleOutlined />
                                {{ $t("open_cases.low") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>

        <!-- Search and Cases Display -->
        <a-row>
            <a-col :span="24">
                <a-card>
                    <!-- Search Bar -->
                    <a-row justify="end" align="middle" class="mb-4">
                        <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
                            <a-input-search
                                style="width: 100%"
                                v-model:value="searchString"
                                :placeholder="$t('common.search')"
                                show-search
                                @search="onSearch"
                                @change="onSearch"
                                :loading="loading"
                            />
                        </a-col>
                    </a-row>

                    <!-- Cases Grid -->
                    <a-spin :spinning="loading">
                        <div
                            v-if="filteredCases.length === 0"
                            class="text-center py-8"
                        >
                            <a-empty
                                :description="$t('open_cases.no_cases_found')"
                            />
                        </div>
                        <a-row v-else :gutter="[16, 16]">
                            <a-col
                                v-for="caseItem in filteredCases"
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
                                            :color="
                                                getPriorityColor(
                                                    caseItem.priority
                                                )
                                            "
                                            class="priority-badge"
                                        >
                                            {{
                                                $t(
                                                    `open_cases.${caseItem.priority}`
                                                )
                                            }}
                                        </a-tag>
                                        <a-tag
                                            :color="
                                                getStatusColor(caseItem.status)
                                            "
                                            class="ml-2"
                                        >
                                            {{
                                                $t(
                                                    `open_cases.${caseItem.status}`
                                                )
                                            }}
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
                                                expandable: false,
                                            }"
                                            :content="caseItem.description"
                                        />
                                    </p>

                                    <!-- Patient Info -->
                                    <div class="patient-info mb-3">
                                        <UserOutlined class="mr-1" />
                                        <span class="patient-name">
                                            {{ caseItem.patient?.user?.name }}
                                            {{
                                                caseItem.patient?.user
                                                    ?.last_name
                                            }}
                                        </span>
                                        <br />
                                        <PhoneOutlined class="mr-1" />
                                        <span class="patient-phone">
                                            {{ caseItem.patient?.user?.phone }}
                                        </span>
                                    </div>

                                    <!-- Date Info -->
                                    <div class="date-info mb-3">
                                        <ClockCircleOutlined class="mr-1" />
                                        <span class="text-gray-500">
                                            {{
                                                formatDateTime(
                                                    caseItem.created_at
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="card-actions">
                                        <a-space>
                                            <a-button
                                                v-if="
                                                    permsArray.includes(
                                                        'open_cases_edit'
                                                    ) ||
                                                    permsArray.includes('admin')
                                                "
                                                type="primary"
                                                size="small"
                                                @click="editItem(caseItem)"
                                            >
                                                <template #icon
                                                    ><EditOutlined
                                                /></template>
                                                {{ $t("common.edit") }}
                                            </a-button>
                                            <a-button
                                                type="default"
                                                size="small"
                                                @click="viewPatient(caseItem)"
                                            >
                                                <template #icon
                                                    ><EyeOutlined
                                                /></template>
                                                {{
                                                    $t(
                                                        "open_cases.view_patient"
                                                    )
                                                }}
                                            </a-button>
                                            <a-button
                                                v-if="
                                                    permsArray.includes(
                                                        'open_cases_delete'
                                                    ) ||
                                                    permsArray.includes('admin')
                                                "
                                                type="primary"
                                                danger
                                                size="small"
                                                @click="
                                                    showDeleteConfirm(
                                                        caseItem.xid
                                                    )
                                                "
                                            >
                                                <template #icon
                                                    ><DeleteOutlined
                                                /></template>
                                            </a-button>
                                        </a-space>
                                    </div>
                                </a-card>
                            </a-col>
                        </a-row>
                    </a-spin>
                </a-card>
            </a-col>
        </a-row>
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import {
    PlusOutlined,
    DeleteOutlined,
    FileOutlined,
    UserOutlined,
    EditOutlined,
    EyeOutlined,
    ExclamationCircleOutlined,
    WarningOutlined,
    InfoCircleOutlined,
    MinusCircleOutlined,
    ClockCircleOutlined,
    PhoneOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import StateWidget from "../../../common/components/common/card/StateWidget.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        FileOutlined,
        UserOutlined,
        EditOutlined,
        EyeOutlined,
        ExclamationCircleOutlined,
        WarningOutlined,
        InfoCircleOutlined,
        MinusCircleOutlined,
        ClockCircleOutlined,
        PhoneOutlined,
        AdminPageHeader,
        AddEdit,
        StateWidget,
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
        const { permsArray, formatDateTime } = common();
        const crudVariables = crud();
        const router = useRouter();

        const filters = ref({
            priority: "",
        });

        const searchString = ref("");
        const loading = ref(false);
        const cases = ref([]);

        // Statistics
        const stats = computed(() => {
            const data = cases.value;
            return {
                total: data.length,
                critical: data.filter((c) => c.priority === "critical").length,
                high: data.filter((c) => c.priority === "high").length,
                open: data.filter((c) => c.status === "open").length,
            };
        });

        onMounted(() => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.tableUrl.value = { url };
            crudVariables.hashableColumns.value = hashableColumns;
            crudVariables.langKey.value = "open_cases";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            setUrlData();
        });

        watch(
            () => crudVariables.table.data,
            (newData) => {
                cases.value = newData;
            }
        );

        watch(
            () => crudVariables.table.loading,
            (newVal) => {
                loading.value = newVal;
            }
        );

        const setUrlData = () => {
            crudVariables.fetch();
        };

        const onSearch = () => {
            // Search is handled by computed property
        };

        // Filtered cases based on priority and search
        const filteredCases = computed(() => {
            let result = cases.value;

            // Filter by priority
            if (filters.value.priority) {
                result = result.filter(
                    (c) => c.priority === filters.value.priority
                );
            }

            // Filter by search string
            if (searchString.value) {
                const search = searchString.value.toLowerCase();
                result = result.filter(
                    (c) =>
                        c.title.toLowerCase().includes(search) ||
                        c.description.toLowerCase().includes(search) ||
                        c.patient?.name.toLowerCase().includes(search) ||
                        c.patient?.last_name.toLowerCase().includes(search) ||
                        c.patient?.phone.includes(search)
                );
            }

            return result;
        });

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

        const viewPatient = (caseItem) => {
            if (caseItem.patient?.xid) {
                router.push({
                    name: "admin.patients.detail",
                    params: { id: caseItem.patient.xid },
                });
            }
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            filters,
            searchString,
            onSearch,
            loading,
            cases,
            filteredCases,
            stats,
            getPriorityColor,
            getStatusColor,
            formatDateTime,
            viewPatient,
        };
    },
};
</script>

<style scoped>
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

.patient-info {
    font-size: 13px;
    color: #595959;
    padding: 8px 0;
    border-top: 1px solid #f0f0f0;
}

.patient-name {
    font-weight: 500;
}

.patient-phone {
    color: #8c8c8c;
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

.table-tab-filters :deep(.ant-tabs-tab) {
    padding: 8px 16px;
}

.table-tab-filters :deep(.ant-tabs-tab-active) {
    font-weight: 600;
}

@media (max-width: 768px) {
    .case-card {
        margin-bottom: 16px;
    }
}
</style>
