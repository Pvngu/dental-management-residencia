<template>
    <div class="appointment-details">
        <a-card 
            :title="$t('calendar.appointment_details')"
            :headStyle="{ background: '#fafafa', borderBottom: '1px solid #f0f0f0' }"
        >
            <template #extra>
                <a-space>
                    <a-tag 
                        :color="getStatusColor(appointment.status)"
                        style="margin: 0;"
                    >
                        {{ $t(`calendar.${appointment.status}`) }}
                    </a-tag>
                    <a-dropdown>
                        <template #overlay>
                            <a-menu @click="onMenuClick">
                                <a-menu-item key="edit">
                                    <EditOutlined />
                                    {{ $t("common.edit") }}
                                </a-menu-item>
                                <a-menu-item key="cancel" :disabled="appointment.status === 'cancelled'">
                                    <CloseOutlined />
                                    {{ $t("calendar.cancel_appointment") }}
                                </a-menu-item>
                                <a-menu-item key="reschedule">
                                    <CalendarOutlined />
                                    {{ $t("calendar.reschedule") }}
                                </a-menu-item>
                                <a-menu-divider />
                                <a-menu-item key="delete" danger>
                                    <DeleteOutlined />
                                    {{ $t("common.delete") }}
                                </a-menu-item>
                            </a-menu>
                        </template>
                        <a-button type="text" size="small">
                            <MoreOutlined />
                        </a-button>
                    </a-dropdown>
                </a-space>
            </template>

            <div class="appointment-info">
                <a-row :gutter="[16, 16]">
                    <a-col :span="24">
                        <div class="patient-section">
                            <h3 class="section-title">
                                <UserOutlined />
                                {{ $t("calendar.patient_information") }}
                            </h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.patient_name") }}:</span>
                                    <span class="value">{{ appointment.patient_name }}</span>
                                </div>
                                <div class="info-item" v-if="appointment.patient_phone">
                                    <span class="label">{{ $t("calendar.patient_phone") }}:</span>
                                    <span class="value">{{ appointment.patient_phone }}</span>
                                </div>
                                <div class="info-item" v-if="appointment.patient_email">
                                    <span class="label">{{ $t("calendar.patient_email") }}:</span>
                                    <span class="value">{{ appointment.patient_email }}</span>
                                </div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :span="24">
                        <div class="appointment-section">
                            <h3 class="section-title">
                                <CalendarOutlined />
                                {{ $t("calendar.appointment_information") }}
                            </h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.date") }}:</span>
                                    <span class="value">{{ formatDate(appointment.date) }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.time") }}:</span>
                                    <span class="value">{{ appointment.start_time }} - {{ appointment.end_time }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.duration") }}:</span>
                                    <span class="value">{{ calculateDuration(appointment.start_time, appointment.end_time) }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.treatment_type") }}:</span>
                                    <span class="value">{{ $t(`calendar.${appointment.treatment_type}`) }}</span>
                                </div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :span="24">
                        <div class="dentist-section">
                            <h3 class="section-title">
                                <MedicineBoxOutlined />
                                {{ $t("calendar.dentist_information") }}
                            </h3>
                            <div class="dentist-card">
                                <a-avatar 
                                    :src="dentist?.image" 
                                    :size="48"
                                    class="dentist-avatar"
                                >
                                    {{ dentist?.name?.charAt(0) }}
                                </a-avatar>
                                <div class="dentist-info">
                                    <h4>{{ dentist?.name }}</h4>
                                    <p v-if="dentist?.specialization">{{ dentist.specialization }}</p>
                                    <p v-if="dentist?.phone">
                                        <PhoneOutlined />
                                        {{ dentist.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :span="24" v-if="appointment.notes">
                        <div class="notes-section">
                            <h3 class="section-title">
                                <FileTextOutlined />
                                {{ $t("calendar.notes") }}
                            </h3>
                            <div class="notes-content">
                                <p>{{ appointment.notes }}</p>
                            </div>
                        </div>
                    </a-col>

                    <a-col :span="24" v-if="appointment.payment_info">
                        <div class="payment-section">
                            <h3 class="section-title">
                                <CreditCardOutlined />
                                {{ $t("calendar.payment_information") }}
                            </h3>
                            <div class="payment-info">
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.total_amount") }}:</span>
                                    <span class="value amount">{{ formatCurrency(appointment.payment_info.amount) }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="label">{{ $t("calendar.payment_status") }}:</span>
                                    <a-tag :color="getPaymentStatusColor(appointment.payment_info.status)">
                                        {{ $t(`calendar.payment_${appointment.payment_info.status}`) }}
                                    </a-tag>
                                </div>
                                <div class="info-item" v-if="appointment.payment_info.method">
                                    <span class="label">{{ $t("calendar.payment_method") }}:</span>
                                    <span class="value">{{ $t(`calendar.payment_${appointment.payment_info.method}`) }}</span>
                                </div>
                            </div>
                        </div>
                    </a-col>
                </a-row>
            </div>

            <template #actions>
                <a-space style="width: 100%; justify-content: flex-end;">
                    <a-button @click="onClose">
                        {{ $t("common.close") }}
                    </a-button>
                    <a-button 
                        type="primary" 
                        @click="onEdit"
                        v-if="canEdit"
                    >
                        <EditOutlined />
                        {{ $t("common.edit") }}
                    </a-button>
                </a-space>
            </template>
        </a-card>
    </div>
</template>

<script>
import { defineComponent, computed } from "vue";
import {
    UserOutlined,
    CalendarOutlined,
    MedicineBoxOutlined,
    FileTextOutlined,
    CreditCardOutlined,
    EditOutlined,
    CloseOutlined,
    DeleteOutlined,
    MoreOutlined,
    PhoneOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import moment from "moment";

export default defineComponent({
    name: "AppointmentDetails",
    components: {
        UserOutlined,
        CalendarOutlined,
        MedicineBoxOutlined,
        FileTextOutlined,
        CreditCardOutlined,
        EditOutlined,
        CloseOutlined,
        DeleteOutlined,
        MoreOutlined,
        PhoneOutlined,
    },
    props: {
        appointment: {
            type: Object,
            required: true,
        },
        dentist: {
            type: Object,
            default: () => ({}),
        },
        canEdit: {
            type: Boolean,
            default: true,
        },
    },
    emits: ["edit", "close", "cancel", "reschedule", "delete"],
    setup(props, { emit }) {
        const { t } = useI18n();

        const getStatusColor = (status) => {
            const colors = {
                registered: "blue",
                finished: "green",
                waiting_payment: "orange",
                cancelled: "red",
                rescheduled: "purple",
            };
            return colors[status] || "default";
        };

        const getPaymentStatusColor = (status) => {
            const colors = {
                paid: "green",
                pending: "orange",
                overdue: "red",
                cancelled: "default",
            };
            return colors[status] || "default";
        };

        const formatDate = (date) => {
            return moment(date).format("dddd, MMMM Do YYYY");
        };

        const calculateDuration = (startTime, endTime) => {
            const start = moment(startTime, "HH:mm A");
            const end = moment(endTime, "HH:mm A");
            const duration = moment.duration(end.diff(start));
            
            const hours = duration.hours();
            const minutes = duration.minutes();
            
            if (hours > 0) {
                return `${hours}h ${minutes}min`;
            }
            return `${minutes}min`;
        };

        const formatCurrency = (amount) => {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
            }).format(amount);
        };

        const onMenuClick = ({ key }) => {
            switch (key) {
                case "edit":
                    onEdit();
                    break;
                case "cancel":
                    emit("cancel", props.appointment);
                    break;
                case "reschedule":
                    emit("reschedule", props.appointment);
                    break;
                case "delete":
                    emit("delete", props.appointment);
                    break;
            }
        };

        const onEdit = () => {
            emit("edit", props.appointment);
        };

        const onClose = () => {
            emit("close");
        };

        return {
            getStatusColor,
            getPaymentStatusColor,
            formatDate,
            calculateDuration,
            formatCurrency,
            onMenuClick,
            onEdit,
            onClose,
        };
    },
});
</script>

<style scoped>
.appointment-details {
    max-width: 800px;
    margin: 0 auto;
}

.appointment-info {
    padding: 8px 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: #262626;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item .label {
    font-size: 12px;
    color: #8c8c8c;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item .value {
    font-size: 14px;
    color: #262626;
    font-weight: 500;
}

.info-item .value.amount {
    color: #52c41a;
    font-weight: 600;
    font-size: 16px;
}

.dentist-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #fafafa;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
}

.dentist-avatar {
    flex-shrink: 0;
}

.dentist-info h4 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 600;
    color: #262626;
}

.dentist-info p {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #595959;
    display: flex;
    align-items: center;
    gap: 6px;
}

.notes-content {
    background: #fafafa;
    border-radius: 6px;
    padding: 16px;
    border-left: 4px solid #1890ff;
}

.notes-content p {
    margin: 0;
    color: #595959;
    line-height: 1.6;
}

.payment-info {
    background: #fff7e6;
    border-radius: 8px;
    padding: 16px;
    border: 1px solid #ffd591;
}

.patient-section,
.appointment-section,
.dentist-section,
.notes-section,
.payment-section {
    margin-bottom: 24px;
}

.patient-section:last-child,
.appointment-section:last-child,
.dentist-section:last-child,
.notes-section:last-child,
.payment-section:last-child {
    margin-bottom: 0;
}

/* Responsive design */
@media (max-width: 768px) {
    .appointment-details {
        margin: 0;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .dentist-card {
        flex-direction: column;
        text-align: center;
    }
    
    .section-title {
        font-size: 14px;
    }
    
    .info-item .value {
        font-size: 13px;
    }
}

/* Print styles */
@media print {
    .appointment-details {
        box-shadow: none;
    }
    
    :deep(.ant-card-actions) {
        display: none;
    }
    
    :deep(.ant-card-extra) {
        display: none;
    }
    
    .section-title {
        color: #000 !important;
    }
    
    .info-item .value {
        color: #000 !important;
    }
}
</style>
