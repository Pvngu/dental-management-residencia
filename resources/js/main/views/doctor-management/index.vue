<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="pageHeaderTitle" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <!-- Add button based on active tab -->
                <template v-if="activeTab === 'table'">
                    <a-button
                        v-if="permsArray.includes('doctors_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addDoctorItem"
                    >
                        <PlusOutlined />
                        {{ $t("doctors.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'departments'">
                    <a-button
                        v-if="permsArray.includes('doctor_departments_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addDepartmentItem"
                    >
                        <PlusOutlined />
                        {{ $t("doctor_departments.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'schedules'">
                    <a-button
                        v-if="permsArray.includes('doctor_schedules_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addScheduleItem"
                    >
                        <PlusOutlined />
                        {{ $t("doctor_schedules.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'holidays'">
                    <a-button
                        v-if="permsArray.includes('doctor_holidays_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addHolidayItem"
                    >
                        <PlusOutlined />
                        {{ $t("doctor_holidays.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'breaks'">
                    <a-button
                        v-if="permsArray.includes('doctor_breaks_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addBreakItem"
                    >
                        <PlusOutlined />
                        {{ $t("doctor_breaks.add") }}
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
                    <a-tab-pane key="table">
                        <template #tab>
                            <span>
                                <TableOutlined />
                                {{ $t("common.doctors") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="departments">
                        <template #tab>
                            <span>
                                <TeamOutlined />
                                {{ $t("menu.doctor_departments") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="schedules">
                        <template #tab>
                            <span>
                                <ScheduleOutlined />
                                {{ $t("menu.doctor_schedules") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="holidays">
                        <template #tab>
                            <span>
                                <CalendarOutlined />
                                {{ $t("menu.doctor_holidays") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="breaks">
                        <template #tab>
                            <span>
                                <RestOutlined />
                                {{ $t("menu.doctor_breaks") }}
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
import { onMounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import { PlusOutlined, TableOutlined, CalendarOutlined, UnorderedListOutlined, TeamOutlined, ScheduleOutlined, RestOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { useI18n } from 'vue-i18n'

export default {
    components: {
        PlusOutlined,
        TableOutlined,
        CalendarOutlined,
        UnorderedListOutlined,
        TeamOutlined,
        ScheduleOutlined,
        RestOutlined,
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
            switch(routeName) {
                case 'admin.doctors.departments':
                    return 'departments';
                case 'admin.doctors.schedules':
                    return 'schedules';
                case 'admin.doctors.holidays':
                    return 'holidays';
                case 'admin.doctors.breaks':
                    return 'breaks';
                default:
                    return 'table';
            }
        });
        
        // Computed property for page header title
        const pageHeaderTitle = computed(() => {
            const titleMap = {
                'table': 'menu.doctors',
                'departments': 'menu.doctor_departments',
                'schedules': 'menu.doctor_schedules',
                'holidays': 'menu.doctor_holidays',
                'breaks': 'menu.doctor_breaks',
            };
            return t(titleMap[activeTab.value] || 'menu.doctors');
        });

        // Handle tab change - navigate to actual routes
        const onTabChange = (key) => {
            const routeMap = {
                'table': 'admin.doctors.index',
                'departments': 'admin.doctors.departments',
                'schedules': 'admin.doctors.schedules',
                'holidays': 'admin.doctors.holidays',
                'breaks': 'admin.doctors.breaks',
            };
            router.push({ name: routeMap[key] });
        };
        
        // Add item handlers for child components
        const addDoctorItem = () => {
            // Trigger add action in doctors component
            window.dispatchEvent(new CustomEvent('add-doctor'));
        };
        
        const addDepartmentItem = () => {
            // Trigger add action in departments component
            window.dispatchEvent(new CustomEvent('add-department'));
        };
        
        const addScheduleItem = () => {
            // Trigger add action in schedules component
            window.dispatchEvent(new CustomEvent('add-schedule'));
        };
        
        const addHolidayItem = () => {
            // Trigger add action in holidays component
            window.dispatchEvent(new CustomEvent('add-holiday'));
        };
        
        const addBreakItem = () => {
            // Trigger add action in breaks component
            window.dispatchEvent(new CustomEvent('add-break'));
        };

        return {
            permsArray,
            activeTab,
            onTabChange,
            pageHeaderTitle,
            addDoctorItem,
            addDepartmentItem,
            addScheduleItem,
            addHolidayItem,
            addBreakItem,
        };
    },
}
</script>
