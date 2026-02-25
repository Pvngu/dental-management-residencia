<template>
    <div class="treat-monitor-container">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ $t('dental_chart.treat_and_monitor') }}
            </h3>
        </div>

        <!-- Items List -->
        <a-spin :spinning="loading">
            <div class="space-y-3">
                <div 
                    v-for="item in treatMonitorItems" 
                    :key="item.xid"
                    class="treat-monitor-item"
                    :class="{
                        'treat-monitor-urgent': item.type === 'urgent',
                        'treat-monitor-important': item.type === 'important',
                        'treat-monitor-normal': item.type === 'normal',
                        'treat-monitor-resolved': item.status === 'resolved'
                    }"
                >
                <!-- Status Indicator -->
                <div class="status-indicator">
                    <div class="status-dot" :class="`status-${item.type}`"></div>
                </div>

                <!-- Content -->
                <div class="item-content">
                    <div class="item-header">
                        <span class="tooth-number">{{ $t('dental_chart.tooth') }} {{ item.tooth_number }}</span>
                        <span class="item-type" :class="`type-${item.type}`">
                            {{ $t(`dental_chart.${item.type}`) }}
                        </span>
                        <span class="item-status" :class="`status-${item.status}`">
                            {{ $t(`dental_chart.${item.status}`) }}
                        </span>
                    </div>
                    <div class="item-description">
                        {{ item.content }}
                    </div>
                    <div v-if="item.comment" class="item-comment">
                        <strong>{{ $t('dental_chart.comment') }}:</strong> {{ item.comment }}
                    </div>
                    <div class="item-footer">
                        <span class="created-info">
                            {{ $t('dental_chart.created_by') }}: {{ item.creator?.name || 'Unknown' }}
                        </span>
                        <span class="created-date">
                            {{ formatDate(item.created_at) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="item-actions">
                    <a-dropdown placement="bottomRight">
                        <a-button size="small" type="text">
                            <template #icon>
                                <MoreOutlined />
                            </template>
                        </a-button>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item 
                                    v-if="item.status === 'active'"
                                    @click="resolveItem(item)"
                                >
                                    <template #icon>
                                        <CheckOutlined />
                                    </template>
                                    {{ $t('dental_chart.mark_resolved') }}
                                </a-menu-item>
                                <a-menu-item 
                                    v-if="item.status === 'resolved'"
                                    @click="reactivateItem(item)"
                                >
                                    <template #icon>
                                        <ReloadOutlined />
                                    </template>
                                    {{ $t('dental_chart.reactivate') }}
                                </a-menu-item>
                                <a-menu-divider />
                                <a-menu-item @click="deleteItem(item)" class="text-red-600">
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
            <div v-if="!loading && treatMonitorItems.length === 0" class="empty-state">
                <div class="empty-icon">
                    <FileSearchOutlined />
                </div>
                <p class="empty-text">{{ $t('dental_chart.no_treat_monitor_items') }}</p>
                <p class="empty-help">{{ $t('dental_chart.create_items_from_pathology_restoration') }}</p>
            </div>
        </a-spin>
    </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { 
    MoreOutlined, 
    DeleteOutlined, 
    CheckOutlined, 
    ReloadOutlined,
    FileSearchOutlined
} from '@ant-design/icons-vue';
import { message, Modal } from 'ant-design-vue';
import { useI18n } from 'vue-i18n';
import apiAdmin from '../../../../../common/composable/apiAdmin';

export default {
    name: 'TreatAndMonitorComponent',
    components: {
        MoreOutlined,
        DeleteOutlined,
        CheckOutlined,
        ReloadOutlined,
        FileSearchOutlined
    },
    props: {
        patientId: {
            type: String,
            required: true
        },
        treatMonitorItems: {
            type: Array,
            default: () => []
        }
        ,
        loading: {
            type: Boolean,
            default: true
        }
    },
    emits: ['refresh-dental-chart'],
    setup(props, { emit }) {
        const { t } = useI18n();
        const { addEditRequestAdmin, deleteRequestAdmin } = apiAdmin();

        const deleteItem = (item) => {
            Modal.confirm({
                title: t('dental_treat_monitor.delete_confirm_title'),
                content: t('dental_treat_monitor.delete_confirm_message'),
                okText: t('common.yes'),
                cancelText: t('common.no'),
                onOk: async () => {
                    try {
                        await deleteRequestAdmin({
                            url: `patients/${props.patientId}/treat-monitor/${item.xid}`,
                            successMessage: t('dental_treat_monitor.deleted')
                        });
                        emit('refresh-dental-chart');
                    } catch (error) {
                        console.error('Error deleting treat monitor item:', error);
                    }
                }
            });
        };

        const resolveItem = async (item) => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/treat-monitor/${item.xid}/resolve`,
                    data: {},
                    successMessage: t('dental_treat_monitor.resolved'),
                    method: 'POST'
                });
                emit('refresh-dental-chart');
            } catch (error) {
                console.error('Error resolving treat monitor item:', error);
            }
        };

        const reactivateItem = async (item) => {
            try {
                await addEditRequestAdmin({
                    url: `patients/${props.patientId}/treat-monitor/${item.xid}/reactivate`,
                    data: {},
                    successMessage: t('dental_treat_monitor.reactivated'),
                    method: 'POST'
                });
                emit('refresh-dental-chart');
            } catch (error) {
                console.error('Error reactivating treat monitor item:', error);
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString();
        };

        return {
            deleteItem,
            resolveItem,
            reactivateItem,
            formatDate
        };
    }
};
</script>

<style scoped>
.treat-monitor-container {
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
}

.treat-monitor-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
    border-left-width: 4px;
}

.treat-monitor-urgent {
    border-color: #fecaca;
    background-color: #fef2f2;
    border-left-color: #ef4444;
}

.treat-monitor-important {
    border-color: #fde68a;
    background-color: #fffbeb;
    border-left-color: #f59e0b;
}

.treat-monitor-normal {
    border-color: #bfdbfe;
    background-color: #eff6ff;
    border-left-color: #3b82f6;
}

.treat-monitor-resolved {
    border-color: #e5e7eb;
    background-color: #f9fafb;
    opacity: 0.7;
    border-left-color: #6b7280;
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

.status-urgent {
    background-color: #ef4444;
}

.status-important {
    background-color: #f59e0b;
}

.status-normal {
    background-color: #3b82f6;
}

.item-content {
    flex: 1;
    min-width: 0;
}

.item-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.tooth-number {
    font-weight: 600;
    color: #111827;
    font-size: 0.875rem;
}

.item-type {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.type-urgent {
    background-color: #fee2e2;
    color: #991b1b;
}

.type-important {
    background-color: #fef3c7;
    color: #92400e;
}

.type-normal {
    background-color: #dbeafe;
    color: #1e40af;
}

.item-status {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-active {
    background-color: #dcfce7;
    color: #166534;
}

.status-resolved {
    background-color: #f3f4f6;
    color: #374151;
}

.item-description {
    color: #374151;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.item-comment {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    font-style: italic;
}

.item-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #6b7280;
}

.item-actions {
    flex-shrink: 0;
}

.empty-state {
    text-align: center;
    padding: 3rem 0;
}

.empty-icon {
    font-size: 2.25rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.empty-text {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.empty-help {
    color: #9ca3af;
    font-size: 0.875rem;
}
</style>
