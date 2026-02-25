<template>
    <div>
        <a-skeleton v-if="loading" active :paragraph="{ rows: 8 }" />
        <div v-else>
            <!-- Empty State -->
            <a-empty 
                v-if="!appointments || appointments.length === 0"
                :description="$t('appointments.no_appointments')"
            />
            
            <!-- Appointments Cards Grid -->
            <div v-else class="appointments-grid">
                <a-card
                    v-for="appointment in appointments"
                    :key="appointment.xid"
                    class="appointment-card"
                    :hoverable="true"
                >
                    <!-- Card Header -->
                    <template #title>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <CalendarOutlined class="text-lg" />
                                <span class="font-semibold">
                                    {{ formatDate(appointment.appointment_date) }}
                                </span>
                            </div>
                            <a-tag :color="getStatusColor(appointment.status)">
                                {{ getStatusLabel(appointment.status) }}
                            </a-tag>
                        </div>
                    </template>

                    <!-- Card Content -->
                    <div class="appointment-details">
                        <!-- Time -->
                        <div class="detail-row">
                            <ClockCircleOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.time') }}:</span>
                            <span class="detail-value">{{ formatTime(appointment.appointment_date) }}</span>
                        </div>

                        <!-- Doctor -->
                        <div class="detail-row">
                            <UserOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.doctor') }}:</span>
                            <span class="detail-value">{{ appointment.doctor?.user?.name || '-' }}</span>
                        </div>

                        <!-- Room -->
                        <div class="detail-row">
                            <EnvironmentOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.room') }}:</span>
                            <span class="detail-value">{{ appointment.room?.name || '-' }}</span>
                        </div>

                        <!-- Treatment Type -->
                        <div class="detail-row">
                            <MedicineBoxOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.treatment_type') }}:</span>
                            <span class="detail-value">{{ appointment.treatment_type?.name || '-' }}</span>
                        </div>

                        <!-- Duration -->
                        <div class="detail-row">
                            <FieldTimeOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.duration') }}:</span>
                            <span class="detail-value">{{ appointment.duration }} {{ $t('common.minutes') }}</span>
                        </div>

                        <!-- Treatment Details -->
                        <div v-if="appointment.treatment_details" class="detail-row treatment-details">
                            <FileTextOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.details') }}:</span>
                            <div class="detail-value">
                                <a-typography-paragraph
                                    :ellipsis="{
                                        rows: 2,
                                        expandable: true,
                                        symbol: $t('common.more'),
                                    }"
                                    :content="appointment.treatment_details"
                                    class="mb-0"
                                />
                            </div>
                        </div>

                        <!-- Created At -->
                        <div class="detail-row text-gray-500 text-sm mt-2">
                            <HistoryOutlined class="detail-icon" />
                            <span class="detail-label">{{ $t('common.created_at') }}:</span>
                            <span class="detail-value">{{ formatDate(appointment.created_at) }}</span>
                        </div>
                    </div>
                </a-card>
            </div>

            <!-- Pagination -->
            <div v-if="appointments && appointments.length > 0" class="mt-4 flex justify-end">
                <a-pagination
                    v-model:current="pagination.current"
                    v-model:pageSize="pagination.pageSize"
                    :total="pagination.total"
                    :show-size-changer="true"
                    :show-quick-jumper="true"
                    :show-total="pagination.showTotal"
                    @change="handlePaginationChange"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import {
    CalendarOutlined,
    ClockCircleOutlined,
    UserOutlined,
    EnvironmentOutlined,
    MedicineBoxOutlined,
    FieldTimeOutlined,
    FileTextOutlined,
    HistoryOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../../common/composable/common";

const props = defineProps({
    patientId: {
        type: String,
        required: true
    }
});

const { t } = useI18n();
const { formatDate } = common();

const loading = ref(false);
const appointments = ref([]);
const pagination = ref({
    current: 1,
    pageSize: 9,
    total: 0,
    showSizeChanger: true,
    showQuickJumper: true,
    showTotal: (total) => `${t('common.total')} ${total} ${t('common.items')}`,
});

const fetchAppointments = (page = 1) => {
    loading.value = true;
    const params = {
        page: page,
        patient_id: props.patientId,
        fields: "id,xid,patient_id,doctor_id,appointment_date,duration,treatment_details,status,doctor,doctor:user,room_id,treatment_type_id,room,treatmentType,created_at"
    };

    axiosAdmin.get("appointments", { params })
        .then((response) => {
            appointments.value = response.data.data || response.data;
            
            if (response.data.meta) {
                pagination.value.current = response.data.meta.current_page;
                pagination.value.total = response.data.meta.total;
                pagination.value.pageSize = response.data.meta.per_page;
            }
        })
        .catch((error) => {
            console.error('Error fetching appointments:', error);
        })
        .finally(() => {
            loading.value = false;
        });
};

const handlePaginationChange = (page, pageSize) => {
    fetchAppointments(page);
};

const formatTime = (dateTime) => {
    if (!dateTime) return '-';
    return new Date(dateTime).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};

const getStatusColor = (status) => {
    const statusColors = {
        pending: 'orange',
        confirmed: 'blue',
        completed: 'green',
        cancelled: 'red',
        delayed: 'purple'
    };
    return statusColors[status] || 'default';
};

const getStatusLabel = (status) => {
    const statusLabels = {
        pending: t("appointments.pending"),
        confirmed: t("appointments.confirmed"),
        completed: t("appointments.completed"),
        cancelled: t("appointments.cancelled"),
        delayed: t("appointments.delayed")
    };
    return statusLabels[status] || status;
};

onMounted(() => {
    fetchAppointments();
});
</script>

<style scoped>
.appointments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.appointment-card {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.appointment-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.appointment-card :deep(.ant-card-head) {
    border-bottom: 2px solid #f0f0f0;
    padding: 12px 16px;
}

.appointment-card :deep(.ant-card-body) {
    padding: 16px;
}

.appointment-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 14px;
}

.detail-row.treatment-details {
    flex-direction: column;
    gap: 4px;
}

.detail-icon {
    color: #1890ff;
    font-size: 16px;
    min-width: 16px;
    margin-top: 2px;
}

.detail-label {
    font-weight: 500;
    color: #666;
    min-width: 120px;
}

.detail-value {
    color: #333;
    flex: 1;
}

.treatment-details .detail-value {
    width: 100%;
    margin-left: 24px;
}

@media (max-width: 768px) {
    .appointments-grid {
        grid-template-columns: 1fr;
    }
}
</style>
