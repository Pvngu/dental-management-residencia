<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header title="Registro de Actividad" class="p-0!" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        Dashboard
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    Registro de Actividad
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <ActivityLogTable 
            showTableSearch
        />
    </admin-page-table-content>
</template>

<script>
import { EyeOutlined, PlayCircleOutlined, DownOutlined } from "@ant-design/icons-vue";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import ActivityLogTable from "../../components/activity-log/index.vue";

export default {
    components: {
        EyeOutlined,
        PlayCircleOutlined,
        DownOutlined,

        AdminPageHeader,
        ActivityLogTable,
    },
    setup() {
        return {};
    },
};
</script>
