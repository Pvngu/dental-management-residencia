<template>
    <div class="message-wrapper mb-4 flex flex-col">
        <div
            :class="[
                'message-bubble max-w-[70%] p-3 rounded-lg',
                status === 'failed'
                    ? 'self-end bg-red-100 border-2 border-red-400 text-red-800 rounded-br-none'
                    : isOutbound && channel === 'whatsapp'
                    ? 'self-end bg-green-500 text-white rounded-br-none shadow-md'
                    : isOutbound && channel === 'sms' 
                    ? 'self-end bg-blue-500 text-white rounded-br-none shadow-md'
                    : !isOutbound && channel === 'whatsapp'
                    ? 'self-start bg-green-50 border-2 border-green-200 text-gray-800 rounded-bl-none'
                    : 'self-start bg-white border border-gray-200 text-gray-800 rounded-bl-none',
            ]"
        >
            <div class="message-content">
                <p
                    class="whitespace-pre-wrap break-words m-0 text-sm leading-relaxed"
                >
                    {{ text }}
                </p>
                <img
                    v-if="image"
                    :src="image"
                    class="mt-2 rounded-lg max-w-full h-auto"
                />
            </div>
        </div>
        <div
            :class="[
                'message-meta text-xs mt-1 flex items-center gap-2',
                isOutbound
                    ? 'self-end text-gray-400'
                    : 'self-start text-gray-400',
            ]"
        >
            <!-- Channel indicator -->
            <span v-if="channel" class="flex items-center gap-1">
                <WhatsAppOutlined v-if="channel === 'whatsapp'" class="text-green-500" />
                <MessageOutlined v-else class="text-blue-400" />
            </span>
            <span>{{ time }}</span>
            <span v-if="status && isOutbound" :class="[
                'flex items-center gap-1',
                status === 'failed' ? 'text-red-500 font-medium' : ''
            ]">
                <CheckCircleOutlined v-if="status === 'sent'" class="text-green-500" />
                <ClockCircleOutlined v-else-if="status === 'pending'" class="text-yellow-500" />
                <CloseCircleOutlined v-else-if="status === 'failed'" class="text-red-500" />
                {{ statusText }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import {
    CheckCircleOutlined,
    ClockCircleOutlined,
    CloseCircleOutlined,
    WhatsAppOutlined,
    MessageOutlined,
} from "@ant-design/icons-vue";

const props = defineProps({
    text: {
        type: String,
        default: "",
    },
    image: {
        type: String,
        default: null,
    },
    time: {
        type: String,
        default: "",
    },
    isOutbound: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
    },
    channel: {
        type: String,
        default: "sms",
    },
});

const statusText = computed(() => {
    if (!props.status) return '';
    const statusMap = {
        'pending': 'Sending...',
        'sent': 'Sent',
        'delivered': 'Delivered',
        'failed': 'Failed',
        'received': 'Received',
    };
    return statusMap[props.status] || props.status;
});
</script>

<style scoped>
.message-bubble {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
