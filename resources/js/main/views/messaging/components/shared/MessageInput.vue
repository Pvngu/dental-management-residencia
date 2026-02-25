<template>
    <div class="message-input-container p-4 border-t bg-white">
        <div
            class="border border-gray-300 rounded-lg p-2 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition-all"
        >
            <a-textarea
                :value="modelValue"
                @update:value="$emit('update:modelValue', $event)"
                :placeholder="placeholder"
                :auto-size="{ minRows: 2, maxRows: 6 }"
                :bordered="false"
                :disabled="disabled"
                @pressEnter="handlePressEnter"
                class="!resize-none !shadow-none"
            />
            <div class="flex justify-between items-center mt-2 px-2">
                <div class="flex gap-2 items-center">
                    <a-button
                        type="text"
                        size="small"
                        class="text-gray-500 hover:text-blue-600"
                        @click="$emit('attach')"
                        :disabled="disabled"
                    >
                        <template #icon><PaperClipOutlined /></template>
                        {{ attachText }}
                    </a-button>
                    
                    <!-- Channel Selector -->
                    <a-radio-group 
                        v-if="showChannelSelector"
                        :value="channel" 
                        @change="handleChannelChange"
                        size="small"
                        button-style="solid"
                    >
                        <a-radio-button value="sms">
                            <MessageOutlined /> SMS
                        </a-radio-button>
                        <a-radio-button value="whatsapp">
                            <WhatsAppOutlined /> WhatsApp
                        </a-radio-button>
                    </a-radio-group>
                </div>
                <a-button
                    type="primary"
                    :class="[
                        channel === 'whatsapp' 
                            ? 'bg-green-600 hover:bg-green-500 border-green-600' 
                            : 'bg-blue-600 hover:bg-blue-500 border-blue-600'
                    ]"
                    @click="handleSend"
                    :disabled="!modelValue.trim() || disabled"
                    :loading="loading"
                >
                    <template #icon>
                        <WhatsAppOutlined v-if="channel === 'whatsapp'" />
                        <SendOutlined v-else />
                    </template>
                    {{ channel === 'whatsapp' ? 'Send WhatsApp' : sendText }}
                </a-button>
            </div>
        </div>
        <div v-if="showCharCount" class="mt-2 text-xs text-gray-500 text-right">
            {{ modelValue.length }} / {{ maxChars }}
        </div>
    </div>
</template>

<script setup>
import { PaperClipOutlined, SendOutlined, MessageOutlined, WhatsAppOutlined } from "@ant-design/icons-vue";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Type a message...",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    attachText: {
        type: String,
        default: "Attach file",
    },
    sendText: {
        type: String,
        default: "Send Message",
    },
    showCharCount: {
        type: Boolean,
        default: false,
    },
    maxChars: {
        type: Number,
        default: 160,
    },
    showChannelSelector: {
        type: Boolean,
        default: true,
    },
    channel: {
        type: String,
        default: "sms",
    },
});

const emit = defineEmits(["update:modelValue", "send", "attach", "update:channel"]);

const handlePressEnter = (e) => {
    if (e && e.shiftKey) return; // Allow shift+enter for new line
    if (e) e.preventDefault();
    handleSend();
};

const handleSend = () => {
    if (!props.modelValue.trim() || props.disabled || props.loading) return;
    emit("send");
};

const handleChannelChange = (e) => {
    emit("update:channel", e.target.value);
};
</script>
