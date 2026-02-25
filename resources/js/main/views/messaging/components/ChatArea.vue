<template>
    <div class="chat-area flex flex-col h-full">
        <!-- Header -->
        <div
            class="p-4 border-b border-gray-200 bg-white flex justify-between items-center shadow-sm z-10"
        >
            <div class="flex items-center gap-3">
                <a-avatar
                    :size="40"
                    :src="conversation.avatar"
                    :style="{
                        backgroundColor: conversation.avatar
                            ? 'transparent'
                            : '#1890ff',
                    }"
                >
                    <template v-if="!conversation.avatar">
                        {{ getInitial(conversation.patientName) }}
                    </template>
                </a-avatar>
                <div>
                    <h3 class="font-bold text-gray-900 m-0">
                        {{ conversation.patientName }}
                    </h3>
                    <p class="text-sm text-gray-500 m-0">
                        {{ conversation.patientPhone }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a-button shape="circle">
                    <template #icon><BookOutlined /></template>
                </a-button>
                <a-button shape="circle">
                    <template #icon><PrinterOutlined /></template>
                </a-button>
            </div>
        </div>

        <!-- Messages -->
        <div
            class="flex-1 overflow-y-auto p-6 bg-gray-50"
            ref="messagesContainer"
        >
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center h-full">
                <a-spin size="large" />
            </div>
            
            <template v-else>
                <div v-for="msg in conversation.messages" :key="msg.id">
                    <MessageBubble
                        :text="msg.text"
                        :image="msg.image"
                        :time="msg.time"
                        :is-outbound="msg.sender === 'doctor'"
                        :status="msg.status"
                        :channel="msg.channel"
                    />
                </div>

                <div
                    v-if="conversation.messages?.length === 0"
                    class="text-center text-gray-400 mt-10"
                >
                    <MessageOutlined class="text-4xl mb-2" />
                    <p>{{ $t("messages.no_messages_yet") }}</p>
                </div>
            </template>
        </div>

        <!-- Input Area -->
        <MessageInput
            v-model:modelValue="newMessage"
            @send="handleSend"
            :placeholder="getPlaceholderText()"
            :loading="sending"
            :disabled="sending"
            :show-channel-selector="currentTab === 'all'"
            :channel="localChannel"
            @update:channel="handleChannelChange"
        />
    </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import {
    BookOutlined,
    PrinterOutlined,
    MessageOutlined,
} from "@ant-design/icons-vue";
import MessageBubble from "./shared/MessageBubble.vue";
import MessageInput from "./shared/MessageInput.vue";

const { t } = useI18n();

const props = defineProps({
    conversation: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    sending: {
        type: Boolean,
        default: false,
    },
    selectedChannel: {
        type: String,
        default: "sms",
    },
    currentTab: {
        type: String,
        default: "all",
    },
});

const emit = defineEmits(["send", "channel-change"]);

const newMessage = ref("");
const messagesContainer = ref(null);
const localChannel = ref(props.selectedChannel);

const getPlaceholderText = () => {
    const channelName = localChannel.value === "whatsapp" ? "WhatsApp" : "SMS";
    return `${t("messages.type_message")} (${channelName})`;
};

const handleChannelChange = (newChannel) => {
    localChannel.value = newChannel;
    emit("channel-change", newChannel);
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    });
};

const handleSend = () => {
    if (!newMessage.value.trim()) return;

    emit("send", { text: newMessage.value, channel: localChannel.value });
    newMessage.value = "";
    scrollToBottom();
};

const getInitial = (name) => {
    if (!name) return "?";
    return name.charAt(0).toUpperCase();
};

watch(
    () => props.selectedChannel,
    (newVal) => {
        localChannel.value = newVal;
    }
);

watch(
    () => props.conversation.id,
    () => {
        scrollToBottom();
    }
);

watch(
    () => props.conversation.messages?.length,
    () => {
        scrollToBottom();
    }
);

watch(
    () => props.loading,
    (newVal) => {
        if (!newVal) {
            scrollToBottom();
        }
    }
);

onMounted(() => {
    scrollToBottom();
});
</script>

<style scoped>
/* Custom scrollbar for chat area */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}
</style>
