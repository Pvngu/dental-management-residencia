<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="pageHeaderTitle" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <!-- Add button based on active tab -->
                <template v-if="activeTab === 'table'">
                    <template
                        v-if="
                            permsArray.includes('appointments_create') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-button type="primary" @click="addAppointmentItem">
                            <PlusOutlined />
                            {{ $t("appointments.add") }}
                        </a-button>
                    </template>
                </template>
                
                <template v-if="activeTab === 'today'">
                    <template
                        v-if="
                            permsArray.includes('appointments_create') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-button type="primary" @click="addAppointmentItem">
                            <PlusOutlined />
                            {{ $t("appointments.add") }}
                        </a-button>
                    </template>
                </template>
                
                <template v-if="activeTab === 'calendar'">
                    <template
                        v-if="
                            permsArray.includes('appointments_create') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-button type="primary" @click="addAppointmentItem">
                            <PlusOutlined />
                            {{ $t("appointments.add") }}
                        </a-button>
                    </template>
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
                    {{ $t(`menu.appointments`) }}
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
                    <a-tab-pane key="today">
                        <template #tab>
                            <span>
                                <UnorderedListOutlined />
                                {{ $t("appointments.todays") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="table">
                        <template #tab>
                            <span>
                                <TableOutlined />
                                {{ $t("appointments.all_appointments") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <!-- <a-tab-pane key="calendar">
                        <template #tab>
                            <span>
                                <CalendarOutlined />
                                {{ $t("appointments.calendar_view") }}
                            </span>
                        </template>
                    </a-tab-pane> -->
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
import { PlusOutlined, TableOutlined, CalendarOutlined, UnorderedListOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { useI18n } from 'vue-i18n';

export default {
    components: {
        PlusOutlined,
        TableOutlined,
        CalendarOutlined,
        UnorderedListOutlined,
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
                case 'admin.appointments.today':
                    return 'today';
                case 'admin.appointments.calendar':
                    return 'calendar';
                case 'admin.appointments.table':
                default:
                    return 'table';
            }
        });
        
        // Computed property for page header title
        const pageHeaderTitle = computed(() => {
            const titleMap = {
                'table': 'menu.appointments',
                'today': 'appointments.todays_appointments',
                'calendar': 'appointments.calendar_view',
            };
            return t(titleMap[activeTab.value] || 'menu.appointments');
        });

        // Handle tab change - navigate to actual routes
        const onTabChange = (key) => {
            const routeMap = {
                'table': 'admin.appointments.table',
                'today': 'admin.appointments.today',
                'calendar': 'admin.appointments.calendar',
            };
            router.push({ name: routeMap[key] });
        };
        
        // Add item handler
        const addAppointmentItem = () => {
            // Trigger add action in appointments component
            window.dispatchEvent(new CustomEvent('add-appointment'));
        };

        return {
            permsArray,
            activeTab,
            onTabChange,
            pageHeaderTitle,
            addAppointmentItem,
        };
    },
}
</script>
