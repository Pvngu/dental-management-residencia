<template>
    <a-modal
        :open="visible"
        :title="modalTitle"
        @cancel="onClose"
        :footer="null"
        width="500px"
    >
        <div v-if="holiday" class="holiday-details">
            <a-descriptions :column="1" bordered size="small">
                <a-descriptions-item :label="$t('doctor_holidays.dates')">
                    <a-space>
                        <CalendarOutlined style="color: #1890ff" />
                        <span>{{ formattedDateRange }}</span>
                    </a-space>
                </a-descriptions-item>
                
                <a-descriptions-item :label="$t('doctor_holidays.duration')">
                    <a-space>
                        <ClockCircleOutlined style="color: #1890ff" />
                        <span>{{ daysDuration }}</span>
                    </a-space>
                </a-descriptions-item>

                <a-descriptions-item :label="$t('doctor_holidays.type')">
                    <a-tag :color="getHolidayTypeColor(holiday.holiday_type)">
                        {{ getHolidayTypeLabel(holiday.holiday_type) }}
                    </a-tag>
                </a-descriptions-item>

                <a-descriptions-item :label="$t('doctor_holidays.status')">
                    <a-tag :color="getStatusColor(holiday.status)">
                        {{ getStatusLabel(holiday.status) }}
                    </a-tag>
                </a-descriptions-item>

                <a-descriptions-item
                    v-if="holiday.reason"
                    :label="$t('doctor_holidays.reason')"
                >
                    <p style="margin: 0; white-space: pre-wrap">{{ holiday.reason }}</p>
                </a-descriptions-item>
            </a-descriptions>

            <div style="margin-top: 24px; text-align: right">
                <a-space>
                    <a-button @click="onClose">
                        {{ $t('common.close') }}
                    </a-button>
                    <a-button
                        v-if="canEdit"
                        type="primary"
                        @click="onEdit"
                    >
                        <template #icon><EditOutlined /></template>
                        {{ $t('common.edit') }}
                    </a-button>
                </a-space>
            </div>
        </div>
    </a-modal>
</template>

<script>
import { defineComponent, computed } from 'vue';
import {
    CalendarOutlined,
    ClockCircleOutlined,
    EditOutlined,
} from '@ant-design/icons-vue';
import common from '../../../../common/composable/common';

export default defineComponent({
    name: 'HolidayEventModal',
    components: {
        CalendarOutlined,
        ClockCircleOutlined,
        EditOutlined,
    },
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        holiday: {
            type: Object,
            default: null,
        },
        startDate: {
            type: [String, Date],
            default: null,
        },
        endDate: {
            type: [String, Date],
            default: null,
        },
        canEdit: {
            type: Boolean,
            default: true,
        },
        holidayTypes: {
            type: Array,
            default: () => [
                { value: 'vacation', label: 'Vacation' },
                { value: 'sick_leave', label: 'Sick Leave' },
                { value: 'personal', label: 'Personal' },
                { value: 'other', label: 'Other' },
            ],
        },
        statusOptions: {
            type: Array,
            default: () => [
                { value: 'pending', label: 'Pending' },
                { value: 'approved', label: 'Approved' },
                { value: 'rejected', label: 'Rejected' },
            ],
        },
    },
    emits: ['close', 'edit'],
    setup(props, { emit }) {
        const { formatDate, dayjs } = common();

        const modalTitle = computed(() => {
            if (!props.holiday) return '';
            return props.holiday.doctor_name || 'Holiday Details';
        });

        const formattedDateRange = computed(() => {
            if (!props.startDate || !props.endDate) return '';
            
            const start = formatDate(props.startDate);
            const end = formatDate(props.endDate);
            
            if (start === end) {
                return start;
            }
            return `${start} - ${end}`;
        });

        const daysDuration = computed(() => {
            if (!props.startDate || !props.endDate) return '';
            
            const start = dayjs(props.startDate);
            const end = dayjs(props.endDate);
            const days = end.diff(start, 'day') + 1;

            return days === 1 ? '1 day' : `${days} days`;
        });

        const getHolidayTypeLabel = (type) => {
            const typeObj = props.holidayTypes.find((t) => t.value === type);
            return typeObj ? typeObj.label : type;
        };

        const getHolidayTypeColor = (type) => {
            const colors = {
                vacation: 'blue',
                sick_leave: 'orange',
                personal: 'green',
                other: 'purple',
            };
            return colors[type] || 'default';
        };

        const getStatusLabel = (status) => {
            const statusObj = props.statusOptions.find((s) => s.value === status);
            return statusObj ? statusObj.label : status;
        };

        const getStatusColor = (status) => {
            const colors = {
                pending: 'orange',
                approved: 'green',
                rejected: 'red',
            };
            return colors[status] || 'default';
        };

        const onClose = () => {
            emit('close');
        };

        const onEdit = () => {
            emit('edit');
        };

        return {
            modalTitle,
            formattedDateRange,
            daysDuration,
            getHolidayTypeLabel,
            getHolidayTypeColor,
            getStatusLabel,
            getStatusColor,
            onClose,
            onEdit,
        };
    },
});
</script>

<style scoped>
.holiday-details :deep(.ant-descriptions-item-label) {
    font-weight: 500;
}
</style>
