<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header title="Calendar" class="p-0!" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item> Calendar </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <a-row class="mt-5">
            <a-col :span="24">
                <div class="table-responsive">
                    <a-card class="page-content-container mt-5 mb-20">
                        <div class="calendar-container">
                            <Calendar
                                :calendarData="calendarData"
                                :loading="loading"
                                @changeDate="onDateChange"
                            />
                        </div>
                        <a-spin v-if="loading" class="calendar-spinner" />
                    </a-card>
                </div>
            </a-col>
        </a-row>
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted } from "vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import crud from "../../../common/composable/crud";
import common from "../../../common/composable/common";
import Calendar from "../../../common/components/common/calendar/FullCalendar.vue";

export default {
    components: {
        AdminPageHeader,
        Calendar,
    },
    setup() {
        const { permsArray, globalSetting } = common();
        const crudVariables = crud();
        const calendarData = ref([]);
        const loading = ref(false);
        const startDate = ref("");
        const endDate = ref("");
        const filters = ref({});

        onMounted(() => {
            // Initial load will be handled by onDateChange when calendar initializes
        });

        const onDateChange = (start, end) => {
            startDate.value = start;
            endDate.value = end;
            setUrlData();
        };

        const setUrlData = () => {
            if (!startDate.value || !endDate.value) return;

            loading.value = true;
            const eventsPromise = axiosAdmin.get(`calendar`, {
                params: {
                    start: startDate.value,
                    end: endDate.value,
                },
            });

            Promise.all([eventsPromise]).then(([eventsResponse]) => {
                calendarData.value = eventsResponse.data;
                loading.value = false;
            });
        };

        return {
            ...crudVariables,
            permsArray,
            loading,
            calendarData,
            filters,
            setUrlData,
            onDateChange,
        };
    },
};
</script>
