<template>
    <div class="conversation-list flex flex-col h-full bg-white">
        <!-- Header / Search -->
        <div class="p-4 border-b border-gray-200">
            <a-input-search
                v-model:value="searchQuery"
                :placeholder="$t('common.search')"
                allow-clear
                @search="onSearch"
                @change="onSearchChange"
            />
            <div class="flex gap-2 mt-4 overflow-x-auto pb-2 no-scrollbar">
                <a-button
                    size="small"
                    :type="filter === 'all' ? 'primary' : 'default'"
                    @click="setFilter('all')"
                    class="rounded-full"
                >
                    {{ $t("messages.all_conversations") }}
                </a-button>
                <a-button
                    size="small"
                    :type="filter === 'unread' ? 'primary' : 'default'"
                    @click="setFilter('unread')"
                    class="rounded-full"
                >
                    {{ $t("messages.unread") }}
                </a-button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex-1 flex items-center justify-center">
            <a-spin size="large" />
        </div>

        <!-- List -->
        <div v-else class="flex-1 overflow-y-auto">
            <div
                v-for="conversation in filteredConversations"
                :key="conversation.id"
                @click="$emit('select', conversation.id)"
                :class="[
                    'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
                    selectedId === conversation.id
                        ? 'bg-blue-50 border-l-4 border-l-blue-500'
                        : 'border-l-4 border-l-transparent',
                ]"
            >
                <div class="flex items-start gap-3">
                    <div class="relative">
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
                                {{
                                    getInitial(conversation.patientName)
                                }}
                            </template>
                        </a-avatar>
                        <span
                            v-if="conversation.unread > 0"
                            class="absolute -top-1 -right-1 bg-green-500 w-3 h-3 rounded-full border-2 border-white"
                        ></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1 gap-2">
                            <h4
                                class="font-semibold text-sm text-gray-900 flex-1 min-w-0"
                                style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                            >
                                {{ conversation.patientName }}
                            </h4>
                            <span
                                class="text-xs text-gray-500 whitespace-nowrap flex-shrink-0"
                                >{{ conversation.lastMessageTime }}</span
                            >
                        </div>
                        <p class="text-sm text-gray-600 truncate mb-1">
                            {{ conversation.lastMessage || $t("messages.no_messages") }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{
                                conversation.patientPhone
                            }}</span>
                            <a-badge
                                v-if="conversation.unread > 0"
                                :count="conversation.unread"
                                :number-style="{ backgroundColor: '#52c41a' }"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="filteredConversations.length === 0 && !loading"
                class="p-8 text-center text-gray-400"
            >
                <MessageOutlined class="text-4xl mb-2" />
                <p>{{ $t("messages.no_conversations") }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { MessageOutlined } from "@ant-design/icons-vue";
import { debounce } from "lodash-es";

const props = defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    selectedId: {
        type: [Number, String],
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["select", "filter-change", "search"]);

const searchQuery = ref("");
const filter = ref("all");

const filteredConversations = computed(() => {
    // Filtering is now done on the backend, so just return conversations
    // Local filtering is only for immediate UI feedback
    let result = props.conversations;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (c) =>
                c.patientName?.toLowerCase().includes(query) ||
                c.patientPhone?.includes(query) ||
                c.lastMessage?.toLowerCase().includes(query)
        );
    }

    return result;
});

const setFilter = (newFilter) => {
    filter.value = newFilter;
    emit("filter-change", newFilter);
};

const onSearch = (value) => {
    emit("search", value);
};

// Debounced search on input change
const onSearchChange = debounce(() => {
    emit("search", searchQuery.value);
}, 300);

const getInitial = (name) => {
    if (!name) return "?";
    return name.charAt(0).toUpperCase();
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
