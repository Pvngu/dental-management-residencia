<template>
    <a-list item-layout="horizontal" :data-source="sortedNotifications" style="width: 100%;">
        <template #renderItem="{ item }">
            <a-list-item
                @click="handleNotificationClick(item)"
                :class="{
                    'list-hover': item.type == 'seguimiento_added' && (item.data?.route || item.data?.url),
                    'unread-item': !item.is_read,
                    'no-flex': item.is_read,
                    'clickable-notification': item.type == 'seguimiento_added' && (item.data?.route || item.data?.url),
                    'important-notification': item.is_important
                }"
            >
                <a-list-item-meta>
                    <template #title>
                        <span v-if="item.type == 'seguimiento_added'">
                            <ExclamationCircleOutlined v-if="item.is_important" class="important-icon" />
                            {{ item.data?.message || 'Nueva notificación' }}
                        </span>
                        <span v-else>
                            <ExclamationCircleOutlined v-if="item.is_important" class="important-icon" />
                            {{ item.data?.message || 'Nueva notificación' }}
                        </span>
                    </template>
                    <template #description>
                        <div class="notification-details">
                            <div>{{ formatRelativeTime(item.created_at) }}</div>
                            <div v-if="item.type == 'seguimiento_added' && item.data?.ente_nombre" class="text-sm text-gray-500">
                                Ente: {{ item.data.ente_nombre }}
                            </div>
                            <div v-if="item.type == 'seguimiento_added' && item.data?.tipo_seguimiento" class="text-sm text-gray-500">
                                Tipo: {{ item.data.tipo_seguimiento }}
                            </div>
                        </div>
                    </template>
                    <template #avatar>
                        <a-avatar>
                            <BellOutlined />
                        </a-avatar>
                    </template>
                </a-list-item-meta>
                <template #actions>
                    <a-radio @click.stop="markAsRead(item)" 
                    v-if="!item.is_read" />
                </template>
            </a-list-item>
        </template>
        <slot></slot>
    </a-list>
</template>

<script>
import common from '../../../common/composable/common';
import { BellOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue';
import { useRouter } from 'vue-router';
import { computed } from 'vue';

const axiosAdmin = window.axiosAdmin;

export default {
    props: {
        notifications: {
            default: {}
        },
        unreadNotifications: {
            default: 0
        }
    },
    components: {
        BellOutlined,
        ExclamationCircleOutlined
    },
    setup(props, { emit }) {
        const { formatRelativeTime } = common();

        // Sort notifications: important first, then unread, then by date
        const sortedNotifications = computed(() => {
            return [...props.notifications].sort((a, b) => {
                // First priority: important notifications
                if (a.is_important && !b.is_important) return -1;
                if (!a.is_important && b.is_important) return 1;
                
                // Second priority: unread notifications
                if (!a.is_read && b.is_read) return -1;
                if (a.is_read && !b.is_read) return 1;
                
                // Third priority: most recent first
                return new Date(b.created_at) - new Date(a.created_at);
            });
        });

        const getNotificationIcon = (type) => {
            switch(type) {
                case 'seguimiento_added':
                    return 'audit';
                default:
                    return 'bell';
            }
        };

        const handleNotificationClick = (item) => {
            // Handle routing based on notification data
            if (item.type === 'seguimiento_added' && item.data?.route) {
                const route = item.data.route;
                if (route.name && route.params) {
                    router.push({ name: route.name, params: route.params });
                } else if (item.data?.url) {
                    // Fallback to URL if route object is not available
                    router.push(item.data.url);
                }
            }
            
            // Mark as read when clicked
            if (!item.is_read) {
                markAsRead(item);
            }
        };

        const markAsRead = (item) => {
            if (item.is_read) return; // Already read, no need to call API
            if (!item.xid) {
                console.error('Notification missing xid:', item);
                return;
            }

            axiosAdmin.post(`notifications/${item.xid}/read`).then((res) => {
                if(res.data.success) {
                    item.is_read = true;
                    item.read_at = new Date();
                    emit('markAsRead');
                }
            }).catch((error) => {
                console.error('Error marking notification as read:', error);
            });
        }

        const markAllAsRead = () => {
            if(props.unreadNotifications !== 0)
                axiosAdmin.post(`notifications/mark-all-read`).then((res) => {
                    if(res.data.success) {
                        props.notifications.forEach((item) => {
                            item.is_read = true;
                            item.read_at = new Date();
                        });
                        emit('markAllAsRead');
                    }
            });
        }

        const router = useRouter();
        
        return {
            formatRelativeTime,
            markAsRead,
            markAllAsRead,
            getNotificationIcon,
            handleNotificationClick,
            sortedNotifications,
            router
        };
    },
};
</script>

<style scoped>
.notification-details {
    line-height: 1.4;
}

.text-sm {
    font-size: 12px;
}

.text-gray-500 {
    color: #6b7280;
}

.clickable-notification {
    cursor: pointer;
    transition: background-color 0.2s;
}

.clickable-notification:hover {
    background-color: #f3f4f6;
}

.important-notification {
    border-left: 3px solid #ff4d4f;
    background-color: #fff2f0;
}

.important-notification:hover {
    background-color: #ffe7e5;
}

.important-icon {
    color: #ff4d4f;
    margin-right: 6px;
    font-size: 16px;
}
</style>