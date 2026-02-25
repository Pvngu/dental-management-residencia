<template>
    <a-modal
        :open="visible"
        :title="`${$t('open_cases.active_cases')} - ${patientName}`"
        width="800px"
        :footer="null"
        @cancel="$emit('update:visible', false)"
        class="active-cases-modal"
    >
        <div v-if="loading" class="flex justify-center py-8">
            <a-spin size="large" />
        </div>
        
        <div v-else-if="activeCases.length === 0" class="text-center py-8">
            <div class="text-gray-500">
                <AlertOutlined class="text-4xl mb-4" />
                <p>{{ $t('open_cases.no_active_cases') || 'No active cases found' }}</p>
            </div>
        </div>

        <div v-else class="space-y-4 max-h-96 overflow-y-auto">
            <div
                v-for="openCase in activeCases"
                :key="openCase.xid"
                class="border rounded-lg p-4 hover:bg-gray-50 transition-colors"
                :class="{
                    'border-red-300 bg-red-50': openCase.priority === 'critical',
                    'border-orange-300 bg-orange-50': openCase.priority === 'high',
                    'border-yellow-300 bg-yellow-50': openCase.priority === 'medium',
                    'border-gray-300 bg-gray-50': openCase.priority === 'low'
                }"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <a-tag
                                :color="getPriorityColor(openCase.priority)"
                                class="uppercase text-xs font-bold"
                            >
                                {{ openCase.priority }}
                            </a-tag>
                            <a-tag
                                :color="getStatusColor(openCase.status)"
                                class="capitalize"
                            >
                                {{ openCase.status.replace('_', ' ') }}
                            </a-tag>
                        </div>
                        
                        <h4 class="font-semibold text-gray-800 mb-2">
                            {{ openCase.title }}
                        </h4>
                        
                        <p class="text-gray-600 text-sm mb-3 leading-relaxed">
                            {{ openCase.description }}
                        </p>
                        
                        <div class="grid grid-cols-2 gap-4 text-xs text-gray-500">
                            <div>
                                <span class="font-medium">{{ $t('common.created') }}:</span>
                                {{ formatDate(openCase.created_at) }}
                            </div>
                            <div v-if="openCase.tooth">
                                <span class="font-medium">{{ $t('open_cases.tooth') }}:</span>
                                {{ openCase.tooth }}
                            </div>
                            <div v-if="openCase.doctor">
                                <span class="font-medium">{{ $t('common.doctor') }}:</span>
                                {{ openCase.doctor.name }}
                            </div>
                            <div v-if="openCase.updated_at !== openCase.created_at">
                                <span class="font-medium">{{ $t('common.updated') }}:</span>
                                {{ formatDate(openCase.updated_at) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="ml-4 flex flex-col gap-2">
                        <a-button
                            type="primary"
                            size="small"
                            @click="$emit('viewCase', openCase)"
                        >
                            {{ $t('common.view') }}
                        </a-button>
                        <a-button
                            v-if="canEdit"
                            type="default"
                            size="small"
                            @click="$emit('editCase', openCase)"
                        >
                            {{ $t('common.edit') }}
                        </a-button>
                    </div>
                </div>
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import { AlertOutlined } from '@ant-design/icons-vue';
import common from "../../../../../common/composable/common";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    activeCases: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    patientName: {
        type: String,
        default: ''
    },
    canEdit: {
        type: Boolean,
        default: true
    }
});

defineEmits(['update:visible', 'viewCase', 'editCase']);

const { t } = useI18n();

const { formatDate } = common();

const getPriorityColor = (priority) => {
    switch (priority) {
        case 'critical':
            return 'red';
        case 'high':
            return 'orange';
        case 'medium':
            return 'yellow';
        case 'low':
            return 'blue';
        default:
            return 'gray';
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'open':
            return 'red';
        case 'in_progress':
            return 'orange';
        case 'resolved':
            return 'green';
        case 'closed':
            return 'gray';
        default:
            return 'blue';
    }
};
</script>

<style scoped>
.active-cases-modal :deep(.ant-modal-body) {
    max-height: 70vh;
    padding: 16px;
}

.active-cases-modal :deep(.ant-modal-header) {
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 0;
}
</style>