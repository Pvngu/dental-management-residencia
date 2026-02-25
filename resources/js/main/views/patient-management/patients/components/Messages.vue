<template>
    <!-- Message Filter Tabs -->
        <div class="message-tabs p-3 border-b bg-white">
            <a-tabs 
                v-model:activeKey="currentTab" 
                @change="handleTabChange"
            >
                <a-tab-pane key="all" :tab="$t('common.all')" />
                <a-tab-pane key="sms" tab="SMS" />
                <a-tab-pane key="whatsapp" tab="WhatsApp" />
            </a-tabs>
        </div>
    <div class="messages-container h-full flex flex-col">
        <!-- Messages Header -->
        <div class="messages-header p-4 border-b bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <MessageOutlined class="text-xl text-blue-500" />
                    <div>
                        <h3 class="font-semibold text-lg flex items-center gap-2">
                            {{ $t("patients.sms_messages") }}
                            <a-badge 
                                v-if="unreadCount > 0"
                                :count="unreadCount"
                                :number-style="{ backgroundColor: '#52c41a' }"
                            />
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ patientPhone || $t("patients.no_phone_number") }}
                        </p>
                    </div>
                </div>
                <a-tag v-if="!patientPhone" color="orange">
                    <WarningOutlined />
                    {{ $t("patients.phone_required") }}
                </a-tag>
            </div>
        </div>

        <!-- Messages List -->
        <div
            class="messages-list flex-1 overflow-y-auto p-4 bg-gray-50"
            ref="messagesContainer"
        >
            <a-spin :spinning="loading" class="w-full">
                <div
                    v-if="filteredMessages.length === 0 && !loading"
                    class="text-center py-8 text-gray-400"
                >
                    <MessageOutlined class="text-4xl mb-2" />
                    <p v-if="currentTab === 'all'">
                        {{ $t("patients.no_messages") }}
                    </p>
                    <p v-else>
                        {{ $t("patients.no_messages_channel", { channel: currentTab.toUpperCase() }) }}
                    </p>
                </div>

                <div v-for="message in filteredMessages" :key="message.id">
                    <MessageBubble
                        :text="message.message"
                        :time="formatDateTime(message.created_at)"
                        :is-outbound="message.direction === 'outbound'"
                        :status="message.status"
                        :status-text="getStatusText(message.status)"
                        :channel="message.channel"
                    />
                </div>
            </a-spin>
        </div>

        <!-- Message Input -->
        <MessageInput
            v-model:modelValue="newMessage"
            @send="handleSendMessage"
            :placeholder="getInputPlaceholder()"
            :disabled="!patientPhone || sending"
            :loading="sending"
            :send-text="getSendButtonText()"
            :show-char-count="true"
            :show-channel-selector="false"
            :channel="currentChannel"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from "vue";
import { useI18n } from "vue-i18n";
import { notification } from "ant-design-vue";
import { MessageOutlined, WarningOutlined } from "@ant-design/icons-vue";
import dayjs from "dayjs";
import MessageBubble from "../../../messaging/components/shared/MessageBubble.vue";
import MessageInput from "../../../messaging/components/shared/MessageInput.vue";

const props = defineProps({
    patientId: {
        type: String,
        required: true,
    },
    patientPhone: {
        type: String,
        default: null,
    },
    isVisible: {
        type: Boolean,
        default: false,
    },
});

const { t } = useI18n();

const emit = defineEmits(['update:unreadCount']);

const messages = ref([]);
const newMessage = ref("");
const loading = ref(false);
const sending = ref(false);
const messagesContainer = ref(null);
const unreadCount = ref(0);
const currentTab = ref("all");
const currentChannel = ref("sms");
let echoChannel = null;

// Computed properties
const filteredMessages = computed(() => {
    if (currentTab.value === "all") {
        return messages.value;
    }
    
    return messages.value.filter(message => {
        return (message.channel || 'sms') === currentTab.value;
    });
});

const formatDateTime = (dateTime) => {
    return dayjs(dateTime).format("MMM D, YYYY h:mm A");
};

const getStatusText = (status) => {
    const statusMap = {
        sent: t("patients.message_sent"),
        pending: t("patients.message_pending"),
        failed: t("patients.message_failed"),
        received: t("patients.message_received"),
    };
    return statusMap[status] || status;
};

const getInputPlaceholder = () => {
    if (!props.patientPhone) {
        return t('patients.add_phone_first');
    }
    
    const channelName = currentChannel.value === 'whatsapp' ? 'WhatsApp' : 'SMS';
    return `${t('patients.type_message')} (${channelName})`;
};

const getSendButtonText = () => {
    return currentChannel.value === 'whatsapp' 
        ? t('patients.send_whatsapp') || 'Send WhatsApp'
        : t('patients.send');
};

const handleTabChange = (key) => {
    currentTab.value = key;
    currentChannel.value = key === "all" ? "sms" : key;
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    });
};

const fetchMessages = async () => {
    loading.value = true;
    try {
        const response = await axiosAdmin.get(`patients/${props.patientId}/messages`);
        messages.value = response.map(msg => ({
            ...msg,
            channel: msg.channel || 'sms' // Default to SMS if no channel specified
        }));
        scrollToBottom();
        
        // Mark messages as read
        await markMessagesAsRead();
    } catch (error) {
        console.error("Error fetching messages:", error);
        notification.error({
            message: t("common.error"),
            description: t("patients.failed_load_messages"),
        });
    } finally {
        loading.value = false;
    }
};

const markMessagesAsRead = async () => {
    try {
        await axiosAdmin.post(`patients/${props.patientId}/messages/mark-as-read`);
        unreadCount.value = 0;
        emit('update:unreadCount', 0);
    } catch (error) {
        console.error("Error marking messages as read:", error);
    }
};

const fetchUnreadCount = async () => {
    try {
        const response = await axiosAdmin.get(`patients/${props.patientId}/messages/unread-count`);
        unreadCount.value = response.unread_count || 0;
        emit('update:unreadCount', response.unread_count || 0);
    } catch (error) {
        console.error("Error fetching unread count:", error);
    }
};

const handleSendMessage = async () => {
    const messageText = newMessage.value.trim();
    if (!messageText || !props.patientPhone || sending.value) {
        return;
    }

    // Use the channel from the current tab if it's not "all", otherwise use current channel
    const messageChannel = currentTab.value !== "all" ? currentTab.value : currentChannel.value;

    sending.value = true;
    
    // Create temporary message object for optimistic UI update
    const tempMessage = {
        xid: `temp-${Date.now()}`,
        message: messageText,
        direction: 'outbound',
        status: 'pending',
        channel: messageChannel,
        created_at: new Date().toISOString(),
    };
    
    // Add message to list immediately with pending status
    messages.value.push(tempMessage);
    const tempMessageIndex = messages.value.length - 1;
    newMessage.value = "";
    scrollToBottom();
    
    try {
        const response = await axiosAdmin.post(`patients/messages/send`, {
            patient_id: props.patientId,
            message: messageText,
            phone: props.patientPhone,
            channel: messageChannel
        });

        if (response.data.success) {
            // Replace temp message with actual response
            messages.value[tempMessageIndex] = {
                ...response.data.data,
                channel: messageChannel
            };
            scrollToBottom();

            const channelLabel = messageChannel === 'whatsapp' ? 'WhatsApp' : 'SMS';
            notification.success({
                message: t("common.success"),
                description: t("patients.message_sent_via", { channel: channelLabel }) || t("patients.message_sent_successfully"),
            });
        } else {
            // Update message status to failed
            messages.value[tempMessageIndex] = {
                ...tempMessage,
                status: 'failed',
            };
            scrollToBottom();
            
            notification.error({
                message: t("common.error"),
                description: response.data.message || t("patients.failed_send_message"),
            });
        }
    } catch (error) {
        console.error("Error sending message:", error);
        
        // Update message status to failed
        messages.value[tempMessageIndex] = {
            ...tempMessage,
            status: 'failed',
        };
        scrollToBottom();
        
        notification.error({
            message: t("common.error"),
            description: error.response?.data?.message || t("patients.failed_send_message"),
        });
    } finally {
        sending.value = false;
    }
};

const setupWebSocket = () => {
    if (!window.Echo || !props.patientId) {
        console.warn('WebSocket setup skipped:', { 
            hasEcho: !!window.Echo, 
            patientId: props.patientId 
        });
        return;
    }
    
    // Update auth token for WebSocket connection
    const token = localStorage.getItem('auth_token');
    if (token && window.Echo.connector && window.Echo.connector.pusher) {
        window.Echo.connector.pusher.config.auth = {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        };
    }
    
    const channelName = `patient.${props.patientId}`;
    console.log('Setting up WebSocket for channel:', channelName);
    
    // Subscribe to patient-specific channel
    echoChannel = window.Echo.private(channelName)
        .subscribed(() => {
            console.log('✅ Successfully subscribed to channel:', channelName);
        })
        .error((error) => {
            console.error('❌ Channel subscription error:', error);
        })
        .listen('.message.received', (data) => {
            console.log('🎉 New message received via WebSocket:', data);
            
            // Check if message already exists (only compare xid since id is hidden)
            const exists = data.message.xid && messages.value.some(
                m => m.xid === data.message.xid
            );
            
            if (!exists) {
                console.log('📥 Adding new message to list');
                // Ensure channel is set, default to SMS
                const messageWithChannel = {
                    ...data.message,
                    channel: data.message.channel || 'sms'
                };
                messages.value.push(messageWithChannel);
                scrollToBottom();
                
                // If it's an inbound message and user is actively viewing messages (not loading and has messages)
                if (data.message.direction === 'inbound') {
                    // Only mark as read if user is actively viewing the messages tab
                    if (props.isVisible) {
                        console.log('👁️ User is viewing messages tab, marking as read');
                        markMessagesAsRead();
                    } else {
                        console.log('📬 User not viewing messages tab, incrementing unread count');
                        unreadCount.value += 1;
                        emit('update:unreadCount', unreadCount.value);
                    }
                    
                    notification.info({
                        message: t("patients.messages"),
                        description: t("patients.message_received"),
                        duration: 3,
                    });
                }
            } else {
                console.log('⏭️ Message already exists, skipping');
            }
        })
        .listenForWhisper('typing', (e) => {
            console.log('User is typing:', e);
        });
    
    console.log('WebSocket listener set up for patient:', props.patientId);
};

const cleanupWebSocket = () => {
    if (echoChannel && window.Echo) {
        window.Echo.leave(`patient.${props.patientId}`);
        echoChannel = null;
        console.log('WebSocket listener cleaned up');
    }
};

// Watch for patient phone changes
watch(
    () => props.patientPhone,
    (newPhone) => {
        if (!newPhone) {
            messages.value = [];
        }
    }
);

// Watch for patientId changes
watch(
    () => props.patientId,
    () => {
        cleanupWebSocket();
        if (props.patientId) {
            fetchUnreadCount();
            setupWebSocket();
        }
    }
);

// Watch for visibility changes - mark messages as read when user opens the messages tab
watch(
    () => props.isVisible,
    (isVisible) => {
        if (isVisible && unreadCount.value > 0) {
            console.log('📖 Messages tab now visible, marking messages as read');
            markMessagesAsRead();
        }
    }
);

onMounted(() => {
    if (props.patientPhone) {
        fetchMessages();
    }
    fetchUnreadCount();
    setupWebSocket();
});

onUnmounted(() => {
    cleanupWebSocket();
});

// Expose unreadCount for parent component
defineExpose({
    unreadCount,
    fetchUnreadCount,
});
</script>

<style scoped>
.messages-container {
    height: calc(100vh - 100px);
    min-height: 500px;
}

.messages-list {
    background: linear-gradient(to bottom, #f9fafb 0%, #f3f4f6 100%);
}

.messages-list::-webkit-scrollbar {
    width: 6px;
}

.messages-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.messages-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.messages-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.message-filter-tabs {
    margin-bottom: 0;
}

.message-filter-tabs .ant-tabs-tab {
    margin-right: 8px;
}

.message-filter-tabs .ant-tabs-tab-btn {
    color: #666;
}

.message-filter-tabs .ant-tabs-tab-active .ant-tabs-tab-btn {
    color: #1890ff;
    font-weight: 500;
}

.message-tabs {
    background: #fafafa;
}
</style>
