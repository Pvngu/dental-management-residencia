<template>
    <div class="history-container">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">{{ $t('patients.patient_history') }}</h3>
            <a-space>
                <a-select 
                    v-model:value="filters.event_type" 
                    :placeholder="$t('common.select_default_text', [$t('patients.event_type')])"
                    :allowClear="true"
                    style="width: 200px"
                    @change="setUrlData"
                >
                    <a-select-option value="appointment">{{ $t('patients.appointments') }}</a-select-option>
                    <a-select-option value="treatment">{{ $t('patients.treatments') }}</a-select-option>
                    <a-select-option value="payment">{{ $t('patients.payments') }}</a-select-option>
                    <a-select-option value="note">{{ $t('patients.notes') }}</a-select-option>
                    <a-select-option value="document">{{ $t('patients.documents') }}</a-select-option>
                </a-select>
                <a-button @click="refreshHistory" :loading="loading">
                    <template #icon><ReloadOutlined /></template>
                    {{ $t('common.refresh') }}
                </a-button>
            </a-space>
        </div>

        <a-tabs v-model:activeKey="activeTab" class="mb-2">
            <a-tab-pane key="timeline" :tab="$t('patients.history')" />
            <a-tab-pane key="activity" tab="Activity Log" />
        </a-tabs>

        <div class="bg-gray-50 rounded-lg p-4" v-if="activeTab === 'timeline'">
            <a-timeline v-if="historyData.length > 0">
                <a-timeline-item 
                    v-for="(item, index) in historyData" 
                    :key="index"
                    :color="getEventColor(item.event_type)"
                >
                    <template #dot>
                        <component :is="getEventIcon(item.event_type)" />
                    </template>
                    
                    <div class="history-item bg-white p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center gap-2">
                                <a-tag :color="getEventColor(item.event_type)">
                                    {{ $t('patients.' + baseType(item.event_type)) }}
                                </a-tag>
                                <span class="font-medium">{{ getEventTitle(item) }}</span>
                            </div>
                            <div class="text-gray-500 text-sm">
                                {{ formatDate(item.created_at) }}
                            </div>
                        </div>
                        
                        <div class="text-gray-600 mb-2" v-if="item.data && item.data.description">
                            {{ item.data.description }}
                        </div>
                        
                        <div class="text-sm text-gray-500" v-if="item.user">
                            {{ $t('patients.performed_by') }}: {{ getUserName(item.user) }}
                        </div>
                        
                        <!-- Event specific data -->
                        <div class="mt-3" v-if="item.data">
                            <!-- Notes UI -->
                            <div v-if="baseType(item.event_type) === 'note'" class="bg-gray-50 border rounded-md p-3 note-block">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <a-avatar :size="36" :src="item.user?.profile_image_url">
                                            <UserOutlined />
                                        </a-avatar>
                                        <div>
                                            <div class="font-medium leading-none">{{ getUserName(item.user) }}</div>
                                            <div class="text-xs text-gray-500">{{ formatDateTime(item.created_at) }}</div>
                                        </div>
                                    </div>
                                    <a-tooltip :title="$t('common.copy_url')">
                                        <a-button type="text" shape="circle" size="small" @click="copyNote(item)">
                                            <template #icon><CopyOutlined /></template>
                                        </a-button>
                                    </a-tooltip>
                                </div>
                                <div class="mt-3">
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ getNoteContent(item) }}</p>
                                </div>
                            </div>
                            <div v-if="baseType(item.event_type) === 'appointment'" class="grid grid-cols-2 gap-4 text-sm">
                                <div v-if="item.data.appointment_date">
                                    <span class="font-medium">{{ $t('appointments.date') }}:</span>
                                    {{ formatDate(item.data.appointment_date) }}
                                </div>
                                <div v-if="item.data.duration">
                                    <span class="font-medium">{{ $t('appointments.duration') }}:</span>
                                    {{ item.data.duration }} {{ $t('common.minutes') }}
                                </div>
                                <div v-if="item.data.treatment_type">
                                    <span class="font-medium">{{ $t('appointments.treatment_type') }}:</span>
                                    {{ item.data.treatment_type }}
                                </div>
                                <div v-if="item.data.status">
                                    <span class="font-medium">{{ $t('common.status') }}:</span>
                                    <a-tag :color="getStatusColor(item.data.status)" size="small">
                                        {{ item.data.status }}
                                    </a-tag>
                                </div>
                            </div>
                            
                            <div v-if="baseType(item.event_type) === 'payment'" class="grid grid-cols-2 gap-4 text-sm">
                                <div v-if="item.data.amount">
                                    <span class="font-medium">{{ $t('payment.amount') }}:</span>
                                    ${{ item.data.amount }}
                                </div>
                                <div v-if="item.data.payment_method">
                                    <span class="font-medium">{{ $t('payment.method') }}:</span>
                                    {{ item.data.payment_method }}
                                </div>
                            </div>
                            
                            <div v-if="baseType(item.event_type) === 'treatment'" class="text-sm">
                                <div v-if="item.data.treatment_details" class="whitespace-pre-wrap">
                                    {{ item.data.treatment_details }}
                                </div>
                            </div>
                        </div>
                    </div>
                </a-timeline-item>
            </a-timeline>
            
            <div v-else-if="!loading" class="text-center py-8 text-gray-500">
                <HistoryOutlined class="text-4xl mb-4" />
                <p>{{ $t('patients.no_history_found') }}</p>
            </div>
            
            <div v-if="loading" class="text-center py-8">
                <a-spin size="large" />
            </div>
        </div>

        <!-- Activity Log Tab -->
        <div v-else class="bg-gray-50 rounded-lg p-4">
            <ActivityLogTable showTableSearch :patientId="patientId" />
        </div>

        <!-- Load More Button -->
        <div v-if="activeTab === 'timeline' && hasMoreData && !loading" class="text-center mt-4">
            <a-button @click="loadMore" :loading="crudVariables.table.loading">
                {{ $t('common.load_more') }}
            </a-button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { 
    CalendarOutlined, 
    MedicineBoxOutlined, 
    DollarOutlined, 
    FileTextOutlined, 
    FileOutlined,
    HistoryOutlined,
    ReloadOutlined,
    UserOutlined,
    CopyOutlined
} from '@ant-design/icons-vue';
import common from '../../../../../common/composable/common';
import crud from '../../../../../common/composable/crud';
import { message } from 'ant-design-vue';
import ActivityLogTable from '../../../../components/activity-log/index.vue';

const props = defineProps({
    patientId: {
        type: String,
        required: true
    }
});

const { t } = useI18n();
const { formatDate, formatDateTime } = common();
const crudVariables = crud();
// Activity log table is handled by ActivityLogTable component

const filters = ref({
    event_type: ''
});

const historyData = computed(() => {
    return crudVariables.table.data || [];
});

const loading = computed(() => {
    return crudVariables.table.loading;
});

const hasMoreData = computed(() => {
    const pagination = crudVariables.table.pagination;
    return pagination.current < pagination.total / pagination.pageSize;
});

// Tabs
const activeTab = ref('timeline');
watch(activeTab, (val) => {
    // No-op; ActivityLogTable handles its own fetching
});

const setUrlData = () => {
    crudVariables.tableUrl.value = {
        url: `patient-histories?fields=xid,id,patient_id,user_id,event_type,referenceable_type,referenceable_id,data,created_at,updated_at,user{xid,name,last_name,profile_image_url}&patient_id=${props.patientId}`,
        filters,
    };

    crudVariables.fetch({
        page: 1,
    });
};

// Activity log is encapsulated in ActivityLogTable

const loadMore = () => {
    const pagination = crudVariables.table.pagination;
    crudVariables.fetch({
        page: pagination.current + 1,
    });
};

const refreshHistory = () => {
    setUrlData();
};

const baseType = (eventType = '') => {
    const et = (eventType || '').toLowerCase();
    if (et.includes('appointment')) return 'appointment';
    if (et.includes('treatment')) return 'treatment';
    if (et.includes('payment')) return 'payment';
    if (et.includes('note')) return 'note';
    if (et.includes('document')) return 'document';
    return eventType || 'activity';
};

const getEventIcon = (eventType) => {
    switch (baseType(eventType)) {
        case 'appointment':
            return CalendarOutlined;
        case 'treatment':
            return MedicineBoxOutlined;
        case 'payment':
            return DollarOutlined;
        case 'note':
            return FileTextOutlined;
        case 'document':
            return FileOutlined;
        default:
            return HistoryOutlined;
    }
};

const getEventColor = (eventType) => {
    switch (baseType(eventType)) {
        case 'appointment':
            return 'blue';
        case 'treatment':
            return 'green';
        case 'payment':
            return 'orange';
        case 'note':
            return 'purple';
        case 'document':
            return 'cyan';
        default:
            return 'gray';
    }
};

const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'completed':
        case 'paid':
            return 'green';
        case 'scheduled':
        case 'pending':
            return 'blue';
        case 'cancelled':
        case 'failed':
            return 'red';
        default:
            return 'gray';
    }
};

const getEventTitle = (item) => {
    if (item.data?.title) return item.data.title;
    const et = (item.event_type || '').toLowerCase();

    if (et.includes('appointment')) return item.data?.treatment_type || t('patients.appointment_created');
    if (et.includes('treatment')) return item.data?.treatment_name || t('patients.treatment_performed');
    if (et.includes('payment')) return `${t('payment.payment')} - $${item.data?.amount || '0.00'}`;
    if (et.includes('note')) {
        if (et.includes('updated')) return t('patient_notes.updated');
        if (et.includes('deleted')) return t('patient_notes.deleted');
        return t('patients.note_added');
    }
    if (et.includes('document')) return item.data?.document_name || t('patients.document_uploaded');
    return t('patients.activity');
};

const getUserName = (user) => {
    if (!user) return '';
    return [user.name, user.last_name].filter(Boolean).join(' ');
};

const getNoteContent = (item) => {
    const et = (item?.event_type || '').toLowerCase();
    const data = item?.data || {};
    if (et.includes('updated')) {
        if (data?.changes?.content?.new !== undefined && data?.changes?.content?.new !== null) {
            return data.changes.content.new;
        }
        return data.current_content || '';
    }
    return data?.content || data?.current_content || '';
};

const copyNote = async (item) => {
    const text = getNoteContent(item) || '';
    try {
        await navigator.clipboard.writeText(text);
        message.success(t('common.success'));
    } catch (e) {
        message.error(t('common.error'));
    }
};

onMounted(() => {
    setUrlData();
});
</script>

<style scoped>
.history-container {
    padding: 16px 0;
}

.history-item {
    margin-bottom: 16px;
    border-left: 3px solid #f0f0f0;
    transition: all 0.3s ease;
}

.history-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

:deep(.ant-timeline-item-content) {
    margin-left: 20px;
}

:deep(.ant-timeline-item-tail) {
    border-left: 2px solid #e8e8e8;
}

.note-block {
    border-color: #f0f0f0;
}
</style>
