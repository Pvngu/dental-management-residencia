<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.dashboard`)" style="padding: 0px" />
        </template>
    </AdminPageHeader>

    <div class="dashboard-page-content-container">
        <!-- Statistics Cards -->
        <a-row :gutter="[16, 16]" class="mb-4 mt-4">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('dashboard.visitors_today')"
                    :value="dashboardStats.visitorsToday + '+'"
                    icon="UsersOutlined"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('dashboard.total_patients')"
                    :value="dashboardStats.totalPatients + '+'"
                    icon="UserOutlined"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('dashboard.patient_requests')"
                    :value="dashboardStats.patientRequests + '+'"
                    icon="CalendarOutlined"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('dashboard.overdue_payments')"
                    :value="'$' + dashboardStats.overduePayments.toFixed(2)"
                    icon="DollarOutlined"
                />
            </a-col>
        </a-row>

        <!-- Charts Row -->
        <a-row :gutter="[16, 16]" class="mb-4">
            <!-- Patient Statistics Chart -->
            <a-col :xs="24" :sm="24" :md="12" :lg="8">
                <a-card :title="$t('dashboard.patient_statistics')" class="chart-card">
                    <template #extra>
                        <a-select v-model:value="patientStatsFilter" style="width: 100px" size="small">
                            <a-select-option value="yearly">{{ $t('dashboard.yearly') }}</a-select-option>
                            <a-select-option value="monthly">{{ $t('dashboard.monthly') }}</a-select-option>
                        </a-select>
                    </template>
                    <BarChart
                        :chartData="patientStatsChartData"
                        :options="barChartOptions"
                        :height="250"
                    />
                </a-card>
            </a-col>

            <!-- Revenue Chart -->
            <a-col :xs="24" :sm="24" :md="12" :lg="8">
                <a-card :title="$t('dashboard.revenue')" class="chart-card">
                    <template #extra>
                        <div class="revenue-info">
                            <div class="revenue-percentage">{{ dashboardStats.revenueGrowth }}%</div>
                            <div class="revenue-amount">${{ dashboardStats.totalRevenue.toLocaleString() }}</div>
                        </div>
                    </template>
                    <LineChart
                        :chartData="revenueChartData"
                        :options="lineChartOptions"
                        :height="250"
                    />
                </a-card>
            </a-col>

            <!-- Treatment Summary -->
            <a-col :xs="24" :sm="24" :md="24" :lg="8">
                <a-card :title="$t('dashboard.treatment_summary')" class="chart-card">
                    <template #extra>
                        <a-select v-model:value="treatmentSummaryFilter" style="width: 100px" size="small">
                            <a-select-option value="monthly">{{ $t('dashboard.monthly') }}</a-select-option>
                            <a-select-option value="weekly">{{ $t('dashboard.weekly') }}</a-select-option>
                        </a-select>
                    </template>
                    <div class="treatment-summary-content">
                        <div class="treatment-total">
                            <span class="total-number">{{ dashboardStats.totalTreatments }}</span>
                            <span class="growth-indicator">{{ dashboardStats.treatmentGrowth }}% ↗</span>
                        </div>
                        <div class="treatment-progress-bar">
                            <div class="progress-segments">
                                <div class="segment implant" :style="{ width: treatmentPercentages.implant + '%' }"></div>
                                <div class="segment dentures" :style="{ width: treatmentPercentages.dentures + '%' }"></div>
                                <div class="segment canal" :style="{ width: treatmentPercentages.canal + '%' }"></div>
                            </div>
                        </div>
                        <div class="treatment-list">
                            <div class="treatment-item" v-for="treatment in treatmentSummary" :key="treatment.type">
                                <div class="treatment-icon" :class="treatment.type">
                                    <component :is="treatment.icon" />
                                </div>
                                <div class="treatment-details">
                                    <div class="treatment-name">{{ treatment.name }}</div>
                                    <div class="treatment-count">{{ treatment.count }} {{ $t('dashboard.treatments') }}</div>
                                </div>
                                <div class="treatment-amount">${{ treatment.amount.toFixed(2) }}</div>
                            </div>
                        </div>
                    </div>
                </a-card>
            </a-col>
        </a-row>

        <!-- Tables Row -->
        <a-row :gutter="[16, 16]">
            <!-- Appointment Activity -->
            <a-col :xs="24" :sm="24" :md="12" :lg="8">
                <a-card :title="$t('dashboard.appointment_activity')" class="table-card">
                    <template #extra>
                        <a-space>
                            <a-select v-model:value="appointmentFilter" style="width: 120px" size="small">
                                <a-select-option value="last_7_days">{{ $t('dashboard.last_7_days') }}</a-select-option>
                                <a-select-option value="last_30_days">{{ $t('dashboard.last_30_days') }}</a-select-option>
                            </a-select>
                            <a-button size="small" type="link" @click="$router.push({ name:'admin.appointments.today'})">{{ $t('dashboard.view_all') }} →</a-button>
                        </a-space>
                    </template>
                    <a-table
                        :columns="appointmentColumns"
                        :dataSource="appointmentData"
                        :pagination="false"
                        size="small"
                        :scroll="{ y: 300 }"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ record.status }}
                                </a-tag>
                            </template>
                        </template>
                    </a-table>
                </a-card>
            </a-col>

            <!-- Payment Summary -->
            <a-col :xs="24" :sm="24" :md="12" :lg="8">
                <a-card :title="$t('dashboard.payment_summary')" class="table-card">
                    <template #extra>
                        <a-tag color="green">{{ currentDate }}</a-tag>
                    </template>
                    <div class="payment-summary">
                        <div class="payment-total">
                            <span class="payment-amount">${{ dashboardStats.todayEarnings.toFixed(2) }}</span>
                            <div class="payment-subtitle">{{ $t('dashboard.amount_earned_today') }}</div>
                        </div>
                        <a-table
                            :columns="paymentColumns"
                            :dataSource="paymentData"
                            :pagination="false"
                            size="small"
                            :scroll="{ y: 250 }"
                        />
                    </div>
                </a-card>
            </a-col>

            <!-- Latest Schedule -->
            <a-col :xs="24" :sm="24" :md="24" :lg="8">
                <a-card :title="$t('dashboard.latest_schedule')" class="table-card">
                    <template #extra>
                        <a-tag color="blue">{{ currentMonth }}</a-tag>
                    </template>
                    <div class="schedule-list">
                        <div class="schedule-item" v-for="schedule in scheduleData" :key="schedule.id">
                            <div class="schedule-date">
                                <div class="month">{{ schedule.month }}</div>
                                <div class="day">{{ schedule.day }}</div>
                            </div>
                            <div class="schedule-details">
                                <div class="patient-name">{{ schedule.patientName }}</div>
                                <div class="appointment-time">
                                    <ClockCircleOutlined /> {{ schedule.time }}
                                </div>
                            </div>
                            <div class="patient-avatar">
                                <a-avatar :src="schedule.avatar" :alt="schedule.patientName">
                                    {{ schedule.patientName.charAt(0) }}
                                </a-avatar>
                            </div>
                        </div>
                    </div>
                </a-card>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { onMounted, reactive, ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import common from "../../common/composable/common";
import AdminPageHeader from "../../common/layouts/AdminPageHeader.vue";
import StateWidget from "../../common/components/common/card/StateWidget.vue";
import DateRangePicker from "../../common/components/common/calendar/DateRangePicker.vue";
import { BarChart, LineChart } from "vue-chart-3";
import { Chart, registerables } from "chart.js";
import {
    ClockCircleOutlined,
    MedicineBoxOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../common/composable/apiAdmin";

Chart.register(...registerables);

export default {
    components: {
        AdminPageHeader,
        StateWidget,
        DateRangePicker,
        BarChart,
        LineChart,
        ClockCircleOutlined,
        MedicineBoxOutlined,
    },
    setup() {
        const { formatTimeDuration } = common();
        const { t } = useI18n();
        const responseData = ref([]);
        const filters = reactive({
            dates: [],
        });

        // Filter states
        const patientStatsFilter = ref("yearly");
        const treatmentSummaryFilter = ref("monthly");
        const appointmentFilter = ref("last_7_days");

        // Dashboard statistics (dummy data - replace with API data)
        const dashboardStats = ref({
            visitorsToday: 20,
            totalPatients: 90,
            patientRequests: 25,
            overduePayments: 250.00,
            revenueGrowth: 12.34,
            totalRevenue: 329000,
            totalTreatments: 2302,
            treatmentGrowth: 432,
            todayEarnings: 1450.00,
        });

        // Treatment summary data
        const treatmentSummary = ref([
            {
                type: 'implant',
                name: 'Implant',
                count: 1500,
                amount: 234.00,
                icon: 'ToothOutlined'
            },
            {
                type: 'dentures',
                name: 'Dentures',
                count: 302,
                amount: 85.00,
                icon: 'MedicineBoxOutlined'
            },
            {
                type: 'canal',
                name: 'Root Canal',
                count: 500,
                amount: 112.00,
                icon: 'HeartOutlined'
            }
        ]);

        // Treatment percentages for progress bar
        const treatmentPercentages = computed(() => {
            const total = treatmentSummary.value.reduce((sum, item) => sum + item.count, 0);
            return {
                implant: (treatmentSummary.value[0].count / total) * 100,
                dentures: (treatmentSummary.value[1].count / total) * 100,
                canal: (treatmentSummary.value[2].count / total) * 100,
            };
        });

        // Chart data for patient statistics
        const patientStatsChartData = ref({
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [
                {
                    label: 'Treatment',
                    data: [20, 35, 40, 45, 80],
                    backgroundColor: '#4ECDC4',
                    borderColor: '#4ECDC4',
                    borderWidth: 1,
                },
                {
                    label: 'Check up',
                    data: [25, 30, 20, 35, 15],
                    backgroundColor: '#FFD93D',
                    borderColor: '#FFD93D',
                    borderWidth: 1,
                }
            ]
        });

        // Chart data for revenue
        const revenueChartData = ref({
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [
                {
                    label: 'Revenue',
                    data: [45000, 52000, 48000, 61000, 55000, 67000],
                    borderColor: '#4ECDC4',
                    backgroundColor: 'rgba(78, 205, 196, 0.1)',
                    tension: 0.4,
                    fill: true,
                }
            ]
        });

        // Chart options
        const barChartOptions = ref({
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: false,
                    }
                },
                x: {
                    grid: {
                        display: false,
                    }
                }
            }
        });

        const lineChartOptions = ref({
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    display: false,
                },
                x: {
                    display: false,
                }
            },
            elements: {
                point: {
                    radius: 4,
                    backgroundColor: '#4ECDC4',
                }
            }
        });

        // Appointment data
        const appointmentColumns = [
            {
                title: 'Name',
                dataIndex: 'name',
                key: 'name',
            },
            {
                title: 'Date',
                dataIndex: 'date',
                key: 'date',
            },
            {
                title: 'Service',
                dataIndex: 'service',
                key: 'service',
            },
            {
                title: 'Status',
                dataIndex: 'status',
                key: 'status',
            },
        ];

        const appointmentData = ref([
            {
                key: '1',
                name: 'Jenny Wilson',
                date: '05-05-24',
                service: 'Tooth Removing',
                status: 'Pending',
            },
            {
                key: '2',
                name: 'Jenny Wilson',
                date: '05-05-24',
                service: 'Gum Disease',
                status: 'Approved',
            },
            {
                key: '3',
                name: 'Jenny Wilson',
                date: '05-05-24',
                service: 'Root Canal',
                status: 'Pending',
            },
            {
                key: '4',
                name: 'Jenny Wilson',
                date: '05-05-24',
                service: 'Tooth Implant',
                status: 'Cancelled',
            },
            {
                key: '5',
                name: 'Jenny Wilson',
                date: '05-05-24',
                service: 'Root Canal',
                status: 'Pending',
            },
        ]);

        // Payment data
        const paymentColumns = [
            {
                title: 'Name',
                dataIndex: 'name',
                key: 'name',
            },
            {
                title: 'Service',
                dataIndex: 'service',
                key: 'service',
            },
            {
                title: 'Payment',
                dataIndex: 'payment',
                key: 'payment',
            },
        ];

        const paymentData = ref([
            {
                key: '1',
                name: 'Wade Warren',
                service: 'Root Canal',
                payment: '$500.00',
            },
            {
                key: '2',
                name: 'Esther Howard',
                service: 'Tooth Removing',
                payment: '$250.00',
            },
            {
                key: '3',
                name: 'Robert Fox',
                service: 'Tooth Removing',
                payment: '$250.00',
            },
            {
                key: '4',
                name: 'Jenny Wilson',
                service: 'Tooth Implant',
                payment: '$450.00',
            },
        ]);

        // Schedule data
        const scheduleData = ref([
            {
                id: '1',
                month: 'May',
                day: '07',
                patientName: 'Guy Hawkins',
                time: '02:00-03:00PM',
                avatar: null,
            },
            {
                id: '2',
                month: 'May',
                day: '08',
                patientName: 'Esther Howard',
                time: '02:00-03:00PM',
                avatar: null,
            },
            {
                id: '3',
                month: 'May',
                day: '09',
                patientName: 'Jenny Wilson',
                time: '02:00-03:00PM',
                avatar: null,
            },
            {
                id: '4',
                month: 'May',
                day: '09',
                patientName: 'Robert Fox',
                time: '02:00-03:00PM',
                avatar: null,
            },
            {
                id: '5',
                month: 'May',
                day: '10',
                patientName: 'Cody Fisher',
                time: '02:00-03:00PM',
                avatar: null,
            },
        ]);

        // Date formatting
        const currentDate = computed(() => {
            const now = new Date();
            return now.toLocaleDateString('en-US', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric' 
            });
        });

        const currentMonth = computed(() => {
            const now = new Date();
            return now.toLocaleDateString('en-US', { 
                month: 'short', 
                year: 'numeric' 
            });
        });

        // Status color helper
        const getStatusColor = (status) => {
            const colors = {
                'Pending': 'orange',
                'Approved': 'green',
                'Cancelled': 'red',
                'Completed': 'blue',
            };
            return colors[status] || 'default';
        };

        onMounted(() => {
            getInitData();
        });

        const getInitData = () => {
            // API endpoint for dashboard data - replace dummy data when ready
            const dashboardPromise = axiosAdmin.post("dashboard", filters);
            
            Promise.all([dashboardPromise]).then(([dashboardResponse]) => {
                const data = dashboardResponse.data;
                
                // Update dashboard stats
                if(data) {
                    dashboardStats.value = {
                        visitorsToday: data.visitors_today || 0,
                        totalPatients: data.total_patients || 0,
                        patientRequests: data.patient_requests || 0,
                        overduePayments: data.overdue_payments || 0,
                        revenueGrowth: data.revenue_growth || 0,
                        totalRevenue: data.total_revenue || 0,
                        totalTreatments: data.total_treatments || 0,
                        treatmentGrowth: data.treatment_growth || 0,
                        todayEarnings: data.today_earnings || 0,
                    };
                
                    // Update chart data
                    if (data.patient_stats) {
                        patientStatsChartData.value = data.patient_stats;
                    }
                    if (data.revenue_chart) {
                        revenueChartData.value = data.revenue_chart;
                    }
                    
                    // Update table data
                    if (data.appointments) {
                        appointmentData.value = data.appointments;
                    }
                    if (data.payments) {
                        paymentData.value = data.payments;
                    }
                    if (data.schedule) {
                        scheduleData.value = data.schedule;
                    }
                    if (data.treatment_summary) {
                        treatmentSummary.value = data.treatment_summary;
                    }
                }
            });

            console.log('Dashboard data loaded with dummy data. Replace with API call when backend is ready.');
        };

        watch([filters], (newVal, oldVal) => {
            getInitData();
        });

        return {
            formatTimeDuration,
            filters,
            responseData,
            dashboardStats,
            patientStatsFilter,
            treatmentSummaryFilter,
            appointmentFilter,
            patientStatsChartData,
            revenueChartData,
            barChartOptions,
            lineChartOptions,
            appointmentColumns,
            appointmentData,
            paymentColumns,
            paymentData,
            scheduleData,
            treatmentSummary,
            treatmentPercentages,
            currentDate,
            currentMonth,
            getStatusColor,
        };
    },
};
</script>

<style lang="less">
.ant-card-extra,
.ant-card-head-title {
    padding: 0px;
}

.ant-card-head-title {
    margin-top: 10px;
}

.dashboard-page-content-container {
    padding: 20px;
    
    .chart-card {
        height: 350px;
        
        .ant-card-body {
            padding-top: 0;
        }
    }
    
    .table-card {
        height: 400px;
        
        .ant-card-body {
            padding-top: 10px;
            overflow: hidden;
        }
    }
}

// Revenue card styling
.revenue-info {
    text-align: right;
    
    .revenue-percentage {
        color: #52c41a;
        font-size: 12px;
        font-weight: bold;
    }
    
    .revenue-amount {
        font-size: 18px;
        font-weight: bold;
        color: #262626;
    }
}

// Treatment summary styling
.treatment-summary-content {
    .treatment-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        
        .total-number {
            font-size: 32px;
            font-weight: bold;
            color: #262626;
        }
        
        .growth-indicator {
            color: #52c41a;
            font-size: 14px;
            font-weight: 500;
        }
    }
    
    .treatment-progress-bar {
        margin-bottom: 20px;
        
        .progress-segments {
            display: flex;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #f0f0f0;
            
            .segment {
                height: 100%;
                
                &.implant {
                    background: linear-gradient(90deg, #1890ff, #40a9ff);
                }
                
                &.dentures {
                    background: linear-gradient(90deg, #fa8c16, #ffa940);
                }
                
                &.canal {
                    background: linear-gradient(90deg, #52c41a, #73d13d);
                }
            }
        }
    }
    
    .treatment-list {
        .treatment-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            
            &:last-child {
                border-bottom: none;
            }
            
            .treatment-icon {
                width: 40px;
                height: 40px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 12px;
                
                &.implant {
                    background-color: #e6f7ff;
                    color: #1890ff;
                }
                
                &.dentures {
                    background-color: #fff7e6;
                    color: #fa8c16;
                }
                
                &.canal {
                    background-color: #f6ffed;
                    color: #52c41a;
                }
            }
            
            .treatment-details {
                flex: 1;
                
                .treatment-name {
                    font-weight: 500;
                    color: #262626;
                }
                
                .treatment-count {
                    font-size: 12px;
                    color: #8c8c8c;
                }
            }
            
            .treatment-amount {
                font-weight: bold;
                color: #262626;
            }
        }
    }
}

// Payment summary styling
.payment-summary {
    .payment-total {
        text-align: center;
        margin-bottom: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #f0f0f0;
        
        .payment-amount {
            font-size: 28px;
            font-weight: bold;
            color: #52c41a;
            display: block;
        }
        
        .payment-subtitle {
            font-size: 12px;
            color: #8c8c8c;
            margin-top: 4px;
        }
    }
}

// Schedule styling
.schedule-list {
    max-height: 300px;
    overflow-y: auto;
    padding-right: 8px; // give space for scrollbar

    .schedule-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        
        &:last-child {
            border-bottom: none;
        }
        
        .schedule-date {
            width: 50px;
            text-align: center;
            margin-right: 16px;
            
            .month {
                font-size: 12px;
                color: #8c8c8c;
                text-transform: uppercase;
            }
            
            .day {
                font-size: 18px;
                font-weight: bold;
                color: #262626;
            }
        }
        
        .schedule-details {
            flex: 1;
            
            .patient-name {
                font-weight: 500;
                color: #262626;
                margin-bottom: 4px;
            }
            
            .appointment-time {
                font-size: 12px;
                color: #8c8c8c;
                display: flex;
                align-items: center;
                gap: 4px;
            }
        }
        
        .patient-avatar {
            .ant-avatar {
                background-color: #f0f0f0;
                color: #8c8c8c;
            }
        }
    }
}

// Table customizations
.ant-table-small {
    .ant-table-tbody > tr > td {
        padding: 8px;
    }
    
    .ant-table-thead > tr > th {
        padding: 8px;
        font-weight: 600;
        color: #262626;
        background-color: #fafafa;
    }
}

// Status tag colors
.ant-tag {
    border-radius: 4px;
    font-size: 11px;
    padding: 2px 8px;
}

// Responsive adjustments
@media (max-width: 768px) {
    .dashboard-page-content-container {
        padding: 10px;
        
        .chart-card,
        .table-card {
            height: auto;
            min-height: 300px;
        }
    }
    
    .treatment-summary-content {
        .treatment-total {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }
    }
    
    .schedule-item {
        .schedule-date {
            width: 40px;
            margin-right: 12px;
        }
    }
}

// Chart container adjustments
.ant-card-body {
    position: relative;
    
    canvas {
        max-height: 250px !important;
    }
}
</style>
