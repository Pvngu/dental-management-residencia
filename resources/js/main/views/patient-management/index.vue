<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="pageHeaderTitle" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <!-- Add button based on active tab -->
                <template v-if="activeTab === 'patients'">
                    <a-button
                        v-if="permsArray.includes('patients_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addPatientsItem"
                    >
                        <PlusOutlined />
                        {{ $t("patients.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'patient_files'">
                    <a-button
                        v-if="permsArray.includes('patient_files_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addPatientFilesItem"
                    >
                        <PlusOutlined />
                        {{ $t("patient_files.add") }}
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
                    {{ pageHeaderTitle }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <admin-page-table-content>
        <!-- View Tabs -->
        <a-row class="mt-5">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="activeTab"
                    @change="onTabChange"
                    centered
                    type="card"
                    class="table-tab-filters"
                >
                    <a-tab-pane key="patients">
                        <template #tab>
                            <span>
                                <UserOutlined />
                                {{ $t("menu.patients") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="patient_files">
                        <template #tab>
                            <span>
                                <FileOutlined />
                                {{ $t("menu.patient_files") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>
        <!-- Content rendered via router -->
        <router-view></router-view>
    </admin-page-table-content>
</template>

<script>
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import { PlusOutlined, UserOutlined, FileOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { useI18n } from 'vue-i18n'

export default {
    components: {
        PlusOutlined,
        UserOutlined,
        FileOutlined,
        AdminPageHeader,
    },
    setup() {
        const { permsArray } = common();
        const router = useRouter();
        const route = useRoute();
        
        const { t } = useI18n();
        
        // Determine active tab based on current route
        const activeTab = computed(() => {
            const routeName = route.name;
            if (routeName.includes('patient_files')) {
                return 'patient_files';
            }
            return 'patients';
        });
        
        // Computed property for page header title
        const pageHeaderTitle = computed(() => {
            const titleMap = {
                'patients': 'menu.patients',
                'patient_files': 'menu.patient_files',
            };
            return t(titleMap[activeTab.value] || 'menu.patients');
        });

        // Handle tab change - navigate to actual routes
        const onTabChange = (key) => {
            const routeMap = {
                'patients': 'admin.patients.index',
                'patient_files': 'admin.patient_files.index',
            };
            router.push({ name: routeMap[key] });
        };
        
        // Add item handlers for child components
        const addPatientsItem = () => {
            // Trigger add action in patients component
            window.dispatchEvent(new CustomEvent('add-patient'));
        };
        
        const addPatientFilesItem = () => {
            // Trigger add action in patient files component
            window.dispatchEvent(new CustomEvent('add-patient-file'));
        };

        return {
            permsArray,
            activeTab,
            onTabChange,
            pageHeaderTitle,
            addPatientsItem,
            addPatientFilesItem,
        };
    },
}
</script>
