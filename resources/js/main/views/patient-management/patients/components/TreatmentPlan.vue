<template>
    <div class="treatment-plan-container">
        <!-- Header with Actions -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ $t('patients.treatment_plan') }}
            </h3>
            <a-button 
                type="primary" 
                @click="showAddTreatmentModal"
                v-if="permsArray.includes('patients_edit') || permsArray.includes('admin')"
            >
                <template #icon>
                    <PlusOutlined />
                </template>
                {{ $t('treatment_plan.add_treatment') }}
            </a-button>
        </div>

        <!-- Filter Tabs -->
        <a-tabs v-model:activeKey="activeFilter" @change="filterTreatments" class="mb-4">
            <a-tab-pane key="all" :tab="$t('common.all')" />
            <a-tab-pane key="active" :tab="$t('treatment_plan.active')" />
            <a-tab-pane key="completed" :tab="$t('treatment_plan.resolved')" />
            <a-tab-pane key="cancelled" :tab="$t('treatment_plan.deleted')" />
        </a-tabs>

        <!-- Treatments List -->
        <a-spin :spinning="loading">
            <div class="space-y-4" v-if="!loading">
                <div 
                    v-for="treatment in filteredTreatments" 
                    :key="treatment.xid"
                    class="treatment-item"
                    :class="{
                        'treatment-active': treatment.status === 'pending',
                        'treatment-completed': treatment.status === 'resolved',
                        'treatment-cancelled': treatment.status === 'deleted',
                        'treatment-urgent': treatment.type === 'high',
                        'treatment-high': treatment.type === 'medium'
                    }"
                >
                <!-- Status Indicator -->
                <div class="status-indicator">
                    <div class="status-dot" :class="`status-${treatment.status}`"></div>
                </div>

                <!-- Treatment Content -->
                <div class="treatment-content">
                    <div class="treatment-header">
                        <h4 class="treatment-title">{{ treatment.content }}</h4>
                        <div class="treatment-meta">
                            <span class="tooth-info" v-if="treatment.tooth_number">
                                {{ $t('dental_chart.tooth') }} {{ treatment.tooth_number }}
                            </span>
                            <span class="priority-badge" :class="`priority-${treatment.type}`">
                                {{ $t(`dental_chart.${treatment.type}`) }}
                            </span>
                            <span class="status-badge" :class="`status-${treatment.status}`">
                                {{ $t(`dental_chart.${treatment.status}`) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="treatment-description" v-if="treatment.comment">
                        <strong>{{ $t('dental_chart.comment') }}:</strong> {{ treatment.comment }}
                    </div>
                    
                    <div class="treatment-details">
                        <div class="detail-row" v-if="treatment.creator">
                            <span class="detail-label">{{ $t('dental_chart.created_by') }}:</span>
                            <span class="detail-value">{{ treatment.creator.name }}</span>
                        </div>
                    </div>
                    
                    <div class="treatment-footer">
                        <span class="created-info">
                            {{ $t('treatment_plan.created_on') }}: {{ formatDate(treatment.created_at) }}
                        </span>
                        <span class="updated-info" v-if="treatment.updated_at !== treatment.created_at">
                            {{ $t('treatment_plan.updated_on') }}: {{ formatDate(treatment.updated_at) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="treatment-actions">
                    <a-dropdown placement="bottomRight">
                        <a-button size="small" type="text">
                            <template #icon>
                                <MoreOutlined />
                            </template>
                        </a-button>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item 
                                    v-if="treatment.status === 'active'"
                                    @click="markAsCompleted(treatment)"
                                >
                                    <template #icon>
                                        <CheckOutlined />
                                    </template>
                                    {{ $t('dental_chart.mark_resolved') }}
                                </a-menu-item>
                                <a-menu-item 
                                    v-if="treatment.status === 'resolved'"
                                    @click="markAsActive(treatment)"
                                >
                                    <template #icon>
                                        <ReloadOutlined />
                                    </template>
                                    {{ $t('dental_chart.reactivate') }}
                                </a-menu-item>
                                <a-menu-divider />
                                <a-menu-item 
                                    @click="deleteTreatment(treatment)" 
                                    class="text-red-600"
                                    v-if="permsArray.includes('patients_delete') || permsArray.includes('admin')"
                                >
                                    <template #icon>
                                        <DeleteOutlined />
                                    </template>
                                    {{ $t('common.delete') }}
                                </a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredTreatments.length === 0 && !loading" class="empty-state">
            <div class="empty-icon">
                <CalendarOutlined />
            </div>
            <p class="empty-text">{{ $t('treatment_plan.no_treatments') }}</p>
            <p class="empty-help">{{ $t('treatment_plan.add_first_treatment') }}</p>
        </div>
        </a-spin>
    </div>

    <!-- Add/Edit Treatment Modal -->
    <TreatmentAddEdit
        :visible="treatmentModalVisible"
        :formData="treatmentFormData"
        :editMode="editMode"
        :patientId="patientId"
        @closed="treatmentModalVisible = false"
        @success="handleTreatmentSuccess"
    />
</template>

<script setup>
import { ref, computed, onMounted, defineProps } from 'vue';
import {
    PlusOutlined,
    MoreOutlined,
    DeleteOutlined,
    CheckOutlined,
    ReloadOutlined,
    CalendarOutlined
} from '@ant-design/icons-vue';
import { Modal } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import common from '../../../../../common/composable/common';
import apiAdmin from '../../../../../common/composable/apiAdmin';
import TreatmentAddEdit from './treatment-plan/TreatmentAddEdit.vue';

const props = defineProps({
    patientId: {
        type: String,
        required: true
    }
});

const { t } = useI18n();
const { permsArray, formatDate } = common();
const { addEditRequestAdmin, deleteRequestAdmin } = apiAdmin();

const treatments = ref([]);
const activeFilter = ref('all');
const loading = ref(false);
const treatmentModalVisible = ref(false);
const editMode = ref(false);
const treatmentFormData = ref({
    content: '',
    comment: '',
    tooth_number: null,
    type: 'normal',
    status: 'active'
});

const filteredTreatments = computed(() => {
    if (activeFilter.value === 'all') return treatments.value;
    if (activeFilter.value === 'active') return treatments.value.filter(t => t.status === 'active');
    if (activeFilter.value === 'completed') return treatments.value.filter(t => t.status === 'resolved');
    if (activeFilter.value === 'cancelled') return treatments.value.filter(t => t.status === 'deleted');
    return treatments.value;
});

const fetchTreatments = async () => {
    loading.value = true;
    try {
        // axiosAdmin is used globally in this project
        const response = await axiosAdmin.get(`patients/${props.patientId}/dental-chart`);
        treatments.value = response.data.treat_monitor_items || [];
    } catch (error) {
        console.error('Error fetching treatments:', error);
        treatments.value = [];
    } finally {
        loading.value = false;
    }
};

const showAddTreatmentModal = () => {
    editMode.value = false;
    treatmentFormData.value = {
        content: '',
        comment: '',
        tooth_number: null,
        type: 'normal',
        status: 'active'
    };
    treatmentModalVisible.value = true;
};

const editTreatment = (treatment) => {
    editMode.value = true;
    treatmentFormData.value = { ...treatment };
    treatmentModalVisible.value = true;
};

const markAsCompleted = async (treatment) => {
    try {
        await addEditRequestAdmin({
            url: `patients/${props.patientId}/treat-monitor/${treatment.xid}/resolve`,
            data: {},
            successMessage: t('dental_treat_monitor.resolved')
        });
        await fetchTreatments();
    } catch (error) {
        console.error('Error marking treatment as completed:', error);
    }
};

const markAsActive = async (treatment) => {
    try {
        await addEditRequestAdmin({
            url: `patients/${props.patientId}/treat-monitor/${treatment.xid}/reactivate`,
            data: {},
            successMessage: t('dental_treat_monitor.reactivated')
        });
        await fetchTreatments();
    } catch (error) {
        console.error('Error marking treatment as active:', error);
    }
};

const deleteTreatment = (treatment) => {
    Modal.confirm({
        title: t('dental_treat_monitor.delete_confirm_title'),
        content: t('dental_treat_monitor.delete_confirm_message'),
        okText: t('common.yes'),
        cancelText: t('common.no'),
        onOk: async () => {
            try {
                await deleteRequestAdmin({
                    url: `patients/${props.patientId}/treat-monitor/${treatment.xid}`,
                    successMessage: t('dental_treat_monitor.deleted')
                });
                await fetchTreatments();
            } catch (error) {
                console.error('Error deleting treatment:', error);
            }
        }
    });
};

const handleTreatmentSuccess = () => {
    treatmentModalVisible.value = false;
    fetchTreatments();
};

const filterTreatments = () => {
    // no-op: computed handles filtering
};

onMounted(() => {
    fetchTreatments();
});
</script>

<style scoped>
.treatment-plan-container {
    padding: 1rem;
}

.treatment-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
    border-left-width: 4px;
    background: white;
}

.treatment-active {
    border-left-color: #10b981;
    background-color: #f0fdf4;
}

.treatment-completed {
    border-left-color: #6b7280;
    background-color: #f9fafb;
    opacity: 0.8;
}

.treatment-cancelled {
    border-left-color: #ef4444;
    background-color: #fef2f2;
    opacity: 0.8;
}

.treatment-urgent {
    border-color: #fecaca;
    box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.2);
}

.treatment-high {
    border-color: #fed7aa;
    box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.2);
}

.status-indicator {
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.status-dot {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
}

.status-active {
    background-color: #10b981;
}

.status-completed {
    background-color: #6b7280;
}

.status-cancelled {
    background-color: #ef4444;
}

.treatment-content {
    flex: 1;
    min-width: 0;
}

.treatment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.treatment-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.treatment-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tooth-info {
    background-color: #e0e7ff;
    color: #3730a3;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.priority-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.priority-urgent {
    background-color: #fee2e2;
    color: #991b1b;
}

.priority-high {
    background-color: #fef3c7;
    color: #92400e;
}

.priority-normal {
    background-color: #dbeafe;
    color: #1e40af;
}

.priority-low {
    background-color: #f3f4f6;
    color: #374151;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.status-active {
    background-color: #dcfce7;
    color: #166534;
}

.status-badge.status-completed {
    background-color: #f3f4f6;
    color: #374151;
}

.status-badge.status-cancelled {
    background-color: #fee2e2;
    color: #991b1b;
}

.treatment-description {
    color: #4b5563;
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.treatment-details {
    margin-bottom: 0.75rem;
}

.detail-row {
    display: flex;
    margin-bottom: 0.25rem;
}

.detail-label {
    font-weight: 500;
    color: #6b7280;
    min-width: 150px;
}

.detail-value {
    color: #111827;
}

.treatment-footer {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #6b7280;
}

.treatment-actions {
    flex-shrink: 0;
}

.empty-state {
    text-align: center;
    padding: 4rem 0;
}

.empty-icon {
    font-size: 3rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.empty-text {
    color: #6b7280;
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
}

.empty-help {
    color: #9ca3af;
    font-size: 0.875rem;
}
</style>
