<template>
    <div
        class="message-center-container h-full flex flex-col bg-white rounded-lg shadow-sm overflow-hidden"
    >
        <!-- Tabs for filtering messages -->
        <div class="message-tabs px-4 border-b border-gray-200 bg-white">
            <a-tabs 
                v-model:activeKey="currentTab" 
                @change="handleTabChange"
                class="message-filter-tabs"
            >
                <a-tab-pane key="all" :tab="$t('common.all')" />
                <a-tab-pane key="sms" tab="SMS" />
                <a-tab-pane key="whatsapp" tab="WhatsApp" />
            </a-tabs>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar / Conversation List -->
            <div class="w-1/3 border-r border-gray-200 flex flex-col min-w-[300px]">
                <ConversationList
                    :conversations="filteredConversations"
                    :selected-id="selectedConversationId"
                    :loading="loadingConversations"
                    @select="selectConversation"
                    @filter-change="handleFilterChange"
                    @search="handleSearch"
                />
            </div>

            <!-- Main Chat Area -->
            <div class="flex-1 flex flex-col bg-gray-50">
                <ChatArea
                    v-if="selectedConversation"
                    :conversation="selectedConversationWithFilteredMessages"
                    :loading="loadingMessages"
                    :sending="sendingMessage"
                    :selected-channel="currentChannel"
                    :current-tab="currentTab"
                    @send="handleSendMessage"
                    @channel-change="handleChannelChange"
                />
                <div
                    v-else
                    class="flex-1 flex items-center justify-center text-gray-400 flex-col"
                >
                    <MessageOutlined class="text-6xl mb-4 opacity-50" />
                    <p class="text-lg">{{ $t("messages.select_conversation") }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { message } from "ant-design-vue";
import { MessageOutlined } from "@ant-design/icons-vue";
import ConversationList from "./components/ConversationList.vue";
import ChatArea from "./components/ChatArea.vue";

const { t } = useI18n();

// State
const conversations = ref([]);
const selectedConversationId = ref(null);
const loadingConversations = ref(false);
const loadingMessages = ref(false);
const sendingMessage = ref(false);
const currentFilter = ref("all");
const searchQuery = ref("");
const currentTab = ref("all");
const currentChannel = ref("sms");

// Computed
const selectedConversation = computed(() => {
    return conversations.value.find(
        (c) => c.id === selectedConversationId.value
    );
});

const filteredConversations = computed(() => {
    // Backend now handles channel filtering when we pass the channel parameter,
    // so we can return conversations directly
    return conversations.value;
});

const selectedConversationWithFilteredMessages = computed(() => {
    if (!selectedConversation.value) return null;
    
    const conversation = { ...selectedConversation.value };
    
    if (currentTab.value === "all" || !conversation.messages) {
        return conversation;
    }
    
    // Filter messages by selected channel
    conversation.messages = conversation.messages.filter(
        message => message.channel === currentTab.value
    );
    
    return conversation;
});

// Methods
const fetchConversations = async () => {
    loadingConversations.value = true;
    try {
        const params = new URLSearchParams();
        if (searchQuery.value) {
            params.append("search", searchQuery.value);
        }
        if (currentFilter.value !== "all") {
            params.append("filter", currentFilter.value);
        }
        // Add channel filter when not on "all" tab
        if (currentTab.value !== "all") {
            params.append("channel", currentTab.value);
        }
        
        const response = await axiosAdmin.get(`messages/conversations?${params.toString()}`);
        
        if (response.success) {
            conversations.value = response.data.map(conv => ({
                ...conv,
                messages: [], // Messages will be loaded when conversation is selected
            }));
            
            // If we had a selected conversation, keep it selected if still in list
            if (selectedConversationId.value) {
                const stillExists = conversations.value.find(c => c.id === selectedConversationId.value);
                if (!stillExists && conversations.value.length > 0) {
                    selectConversation(conversations.value[0].id);
                }
            }
        }
    } catch (error) {
        console.error("Error fetching conversations:", error);
        message.error(t("messages.error_loading_conversations"));
    } finally {
        loadingConversations.value = false;
    }
};

const fetchMessages = async (patientId) => {
    if (!patientId) return;
    
    loadingMessages.value = true;
    try {
        const response = await axiosAdmin.get(`patients/${patientId}/messages`);
        
        console.log('Fetch messages response:', response);
        
        // Find and update the conversation with messages
        const conversation = conversations.value.find(c => c.id === patientId);
        if (conversation) {
            // axiosAdmin interceptor returns response.data directly, so response is already the data
            // Handle both cases: response is array or response.data is array
            const messagesData = Array.isArray(response) ? response : (Array.isArray(response.data) ? response.data : []);
            
            console.log('Messages data to transform:', messagesData);
            
            if (messagesData.length === 0) {
                console.log('No messages found for patient:', patientId);
                conversation.messages = [];
                return;
            }
            
            conversation.messages = messagesData.map(msg => {
                console.log('Transforming message:', msg);
                return {
                    id: msg.xid || msg.id,
                    text: msg.message,
                    sender: msg.direction === 'outbound' ? 'doctor' : 'patient',
                    time: formatMessageTime(msg.created_at),
                    status: msg.status,
                    channel: msg.channel || 'sms', // Default to SMS if no channel specified
                    sentBy: msg.sent_by?.name || msg.sentBy?.name || null,
                };
            });
            
            console.log('Loaded messages for conversation:', conversation.patientName, 'count:', conversation.messages.length, 'messages:', conversation.messages);
        } else {
            console.warn('Conversation not found for patientId:', patientId);
        }
    } catch (error) {
        console.error("Error fetching messages:", error.response || error);
        message.error(t("messages.error_loading_messages"));
    } finally {
        loadingMessages.value = false;
    }
};

const markAsRead = async (patientId) => {
    try {
        await axiosAdmin.post(`patients/${patientId}/messages/mark-as-read`);
        
        // Update local unread count
        const conversation = conversations.value.find(c => c.id === patientId);
        if (conversation) {
            conversation.unread = 0;
        }
    } catch (error) {
        console.error("Error marking messages as read:", error);
    }
};

const selectConversation = async (id) => {
    selectedConversationId.value = id;
    
    // Fetch messages for this conversation
    await fetchMessages(id);
    
    // Mark messages as read
    const conv = conversations.value.find((c) => c.id === id);
    if (conv && conv.unread > 0) {
        await markAsRead(id);
    }
};

const handleTabChange = (key) => {
    console.log('Tab changed to:', key);
    currentTab.value = key;
    // Set the current channel based on the tab, default to SMS for "all"
    currentChannel.value = key === "all" ? "sms" : key;
    
    // Refetch conversations with the new channel filter
    fetchConversations();
};

const handleSendMessage = async ({ text, channel }) => {
    if (!selectedConversation.value) return;

    // Use the channel from the current tab if it's not "all", otherwise use the passed channel
    const messageChannel = currentTab.value !== "all" ? currentTab.value : channel;

    sendingMessage.value = true;
    
    // Create temporary message object for optimistic UI update
    const tempMessage = {
        id: `temp-${Date.now()}`,
        text: text,
        sender: "doctor",
        time: new Date().toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
        }),
        status: 'pending',
        channel: messageChannel,
    };
    
    // Add message to list immediately with pending status
    selectedConversation.value.messages.push(tempMessage);
    const tempMessageIndex = selectedConversation.value.messages.length - 1;
    
    // Update conversation preview
    selectedConversation.value.lastMessage = text;
    selectedConversation.value.lastMessageTime = "Just now";
    
    try {
        const response = await axiosAdmin.post("patients/messages/send", {
            patient_id: selectedConversation.value.id,
            message: text,
            phone: selectedConversation.value.patientPhone,
            channel: messageChannel,
        });

        if (response.data?.success) {
            // Replace temp message with actual response
            selectedConversation.value.messages[tempMessageIndex] = {
                id: response.data.data?.xid || tempMessage.id,
                text: text,
                sender: "doctor",
                time: formatMessageTime(response.data.data?.created_at || new Date().toISOString()),
                status: response.data.data?.status || 'sent',
                channel: messageChannel,
            };
            
            const channelLabel = messageChannel === 'whatsapp' ? 'WhatsApp' : 'SMS';
            message.success(t("messages.message_sent_via", { channel: channelLabel }));
        } else {
            // Update message status to failed
            selectedConversation.value.messages[tempMessageIndex] = {
                ...tempMessage,
                status: 'failed',
            };
            
            message.error(response.data?.message || t("messages.error_sending_message"));
        }
    } catch (error) {
        console.error("Error sending message:", error);
        
        // Update message status to failed
        selectedConversation.value.messages[tempMessageIndex] = {
            ...tempMessage,
            status: 'failed',
        };
        
        message.error(error.response?.data?.message || t("messages.error_sending_message"));
    } finally {
        sendingMessage.value = false;
    }
};

const handleFilterChange = (filter) => {
    currentFilter.value = filter;
    fetchConversations();
};

const handleSearch = (query) => {
    searchQuery.value = query;
    fetchConversations();
};

const handleChannelChange = (channel) => {
    currentChannel.value = channel;
};

const formatMessageTime = (datetime) => {
    if (!datetime) return '';
    
    const date = new Date(datetime);
    const now = new Date();
    const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return date.toLocaleDateString([], { weekday: 'long' });
    } else {
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }
};

// Lifecycle
onMounted(() => {
    fetchConversations();
});
</script>

<style scoped>
.message-center-container {
    height: calc(100vh - 66px);
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
