<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.rooms`)" class="p-0!" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.rooms`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <RoomLayout />
    </admin-page-table-content>
</template>

<script>
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import RoomLayout from "./components/RoomLayout.vue";

export default {
    components: {
        AdminPageHeader,
        RoomLayout,
    },
};
</script>
