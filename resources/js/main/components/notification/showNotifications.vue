<template>
    <a-drawer
        :open="visible"
        :closable="true"
        :visible="false"
        :width="500"
        rootClassName="modern-drawer"
        class="notification-drawer"
    >
        <template #title>
            <span>
                {{ $t("notification.notifications") }}
            </span>
            <a-badge
                :number-style="{
                    backgroundColor: '#fff',
                    color: '#999',
                    boxShadow: '0 0 0 1px #d9d9d9 inset',
                }"
                :count="unreadNotifications"
                class="ml-1"
            />
        </template>
        <template #extra>
            <a-typography-link
                v-if="unreadNotifications !== 0"
                @click.stop="$refs.notificationList.markAllAsRead()"
            >
                {{ $t("notification.mark_all_as_read") }}
            </a-typography-link>
        </template>
        <NotificationList
            :notifications="notifications"
            @onClose="visible = false"
            @markAllAsRead="$emit('markAllAsRead')"
            @markAsRead="$emit('markAsRead')"
            ref="notificationList"
        >
            <a-row
                v-if="loadMoreVisible"
                style="width: 100%"
                justify="center"
                class="py-2"
            >
                <a-divider class="mt-0" />
                <a-col>
                    <a-button @click="$emit('fetchMoreNotifications')">
                        {{ $t("notification.load_more") }}
                    </a-button>
                </a-col>
            </a-row>
        </NotificationList>
    </a-drawer>
</template>

<script>
import { defineComponent, onMounted, ref } from "vue";
import NotificationList from "./NotificationList.vue";
import common from "../../../common/composable/common";

export default defineComponent({
    props: [
        "visible",
        "notifications",
        "unreadNotifications",
        "loadMoreVisible",
    ],
    components: {
        NotificationList,
    },
});
</script>

<style>
.notification-drawer .ant-drawer-wrapper-body .ant-drawer-body {
    padding: 8px;
}
</style>
