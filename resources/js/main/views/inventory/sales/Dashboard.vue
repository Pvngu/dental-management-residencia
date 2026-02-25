<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.sales_dashboard`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <DateRangePicker
                    @dateTimeChanged="
                        (changedDateTime) => {
                            dateRange = changedDateTime;
                            fetchDashboardData();
                        }
                    "
                />
                <a-button type="primary" @click="fetchDashboardData" :loading="loading">
                    <template #icon><ReloadOutlined /></template>
                    {{ $t("common.refresh") }}
                </a-button>
            </a-space>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.sales.index' }">
                        {{ $t(`menu.sales`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.sales_dashboard`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <!-- Summary Cards -->
        <a-row :gutter="[16, 16]" class="mb-5 mt-5">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <a-card :loading="loading" class="stat-card">
                    <a-statistic
                        :title="$t('sales.total_sales')"
                        :value="dashboardData.summary?.total_sales || 0"
                        :prefix="h(ShoppingCartOutlined)"
                        valueStyle="color: #3f8600"
                    />
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <a-card :loading="loading" class="stat-card">
                    <a-statistic
                        :title="$t('sales.total_revenue')"
                        :value="dashboardData.summary?.total_revenue || 0"
                        prefix="$"
                        :precision="2"
                        valueStyle="color: #1890ff"
                    />
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <a-card :loading="loading" class="stat-card">
                    <a-statistic
                        :title="$t('sales.average_sale_value')"
                        :value="dashboardData.summary?.average_sale_value || 0"
                        prefix="$"
                        :precision="2"
                        valueStyle="color: #cf1322"
                    />
                </a-card>
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <a-card :loading="loading" class="stat-card">
                    <a-statistic
                        :title="$t('sales.monthly_growth')"
                        :value="dashboardData.summary?.monthly_growth || 0"
                        suffix="%"
                        :precision="2"
                        :valueStyle="{ color: (dashboardData.summary?.monthly_growth || 0) >= 0 ? '#3f8600' : '#cf1322' }"
                    >
                        <template #prefix>
                            <ArrowUpOutlined v-if="(dashboardData.summary?.monthly_growth || 0) >= 0" />
                            <ArrowDownOutlined v-else />
                        </template>
                    </a-statistic>
                </a-card>
            </a-col>
        </a-row>

        <!-- Charts Row 1: Sales Trend & Status Distribution -->
        <a-row :gutter="[16, 16]" class="mb-4">
            <a-col :xs="24" :lg="16">
                <a-card :title="$t('sales.sales_trend')" :loading="loading">
                    <LineChart
                        v-if="!loading && dailySalesChartData"
                        :chartData="dailySalesChartData"
                        :options="lineChartOptions"
                        :height="300"
                    />
                    <a-empty v-else-if="!loading" :description="$t('common.no_data')" />
                </a-card>
            </a-col>
            <a-col :xs="24" :lg="8">
                <a-card :title="$t('sales.sales_by_status')" :loading="loading">
                    <DoughnutChart
                        v-if="!loading && salesByStatusChartData"
                        :chartData="salesByStatusChartData"
                        :options="doughnutChartOptions"
                        :height="300"
                    />
                    <a-empty v-else-if="!loading" :description="$t('common.no_data')" />
                </a-card>
            </a-col>
        </a-row>

        <!-- Charts Row 2: Top Products & Sales by Hour -->
        <a-row :gutter="[16, 16]" class="mb-4">
            <a-col :xs="24" :lg="12">
                <a-card :title="$t('sales.top_selling_products')" :loading="loading">
                    <BarChart
                        v-if="!loading && topProductsChartData"
                        :chartData="topProductsChartData"
                        :options="barChartOptions"
                        :height="300"
                    />
                    <a-empty v-else-if="!loading" :description="$t('common.no_data')" />
                </a-card>
            </a-col>
            <a-col :xs="24" :lg="12">
                <a-card :title="$t('sales.sales_by_hour')" :loading="loading">
                    <BarChart
                        v-if="!loading && salesByHourChartData"
                        :chartData="salesByHourChartData"
                        :options="hourChartOptions"
                        :height="300"
                    />
                    <a-empty v-else-if="!loading" :description="$t('common.no_data')" />
                </a-card>
            </a-col>
        </a-row>

        <!-- Recent Sales Table -->
        <a-row :gutter="[16, 16]">
            <a-col :span="24">
                <a-card :title="$t('sales.recent_sales')" :loading="loading">
                    <a-table
                        :columns="recentSalesColumns"
                        :data-source="dashboardData.recent_sales || []"
                        :pagination="false"
                        :loading="loading"
                        bordered
                        size="small"
                        :scroll="{ x: 800 }"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'sale_number'">
                                <router-link :to="{ name: 'admin.sales.index' }">
                                    {{ record.sale_number }}
                                </router-link>
                            </template>
                            <template v-if="column.dataIndex === 'total'">
                                ${{ formatCurrency(record.total) }}
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ record.status }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'sold_at'">
                                {{ formatDate(record.sold_at) }}
                            </template>
                        </template>
                    </a-table>
                </a-card>
            </a-col>
        </a-row>
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted, h } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import {
    ShoppingCartOutlined,
    ReloadOutlined,
    ArrowUpOutlined,
    ArrowDownOutlined,
} from "@ant-design/icons-vue";
import { LineChart, BarChart, DoughnutChart } from "vue-chart-3";
import { Chart, registerables } from "chart.js";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import common from "../../../../common/composable/common";
import { useI18n } from "vue-i18n";
import dayjs from "dayjs";

Chart.register(...registerables);

export default {
    components: {
        AdminPageHeader,
        ShoppingCartOutlined,
        ReloadOutlined,
        ArrowUpOutlined,
        ArrowDownOutlined,
        LineChart,
        BarChart,
        DoughnutChart,
        DateRangePicker,
    },
    setup() {
        const { t } = useI18n();
        const { permsArray } = common();
        const loading = ref(false);
        const dateRange = ref([]);
        const dashboardData = ref({
            summary: {},
            sales_by_status: [],
            daily_sales: [],
            top_products: [],
            recent_sales: [],
            sales_by_hour: [],
        });

        // Chart data
        const dailySalesChartData = ref(null);
        const salesByStatusChartData = ref(null);
        const topProductsChartData = ref(null);
        const salesByHourChartData = ref(null);

        // Chart options
        const lineChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        };

        const barChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.x.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(0);
                        }
                    }
                }
            }
        };

        const hourChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' sales';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        callback: function(value) {
                            return value + ':00';
                        }
                    }
                }
            }
        };

        const doughnutChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                },
            }
        };

        // Recent sales table columns
        const recentSalesColumns = [
            {
                title: t('sales.sale_number'),
                dataIndex: 'sale_number',
                key: 'sale_number',
                width: 150,
            },
            {
                title: t('sales.customer'),
                dataIndex: 'patient_name',
                key: 'patient_name',
            },
            {
                title: t('sales.total'),
                dataIndex: 'total',
                key: 'total',
                width: 120,
            },
            {
                title: t('sales.status'),
                dataIndex: 'status',
                key: 'status',
                width: 100,
            },
            {
                title: t('sales.date'),
                dataIndex: 'sold_at',
                key: 'sold_at',
                width: 180,
            },
        ];

        // Fetch dashboard data
        const fetchDashboardData = async () => {
            loading.value = true;
            try {
                const params = {};
                if (dateRange.value && dateRange.value.length === 2) {
                    params.start_date = dateRange.value[0];
                    params.end_date = dateRange.value[1];
                }

                const response = await axiosAdmin.get('sales/dashboard/statistics', { params });
                dashboardData.value = response;

                // Prepare chart data
                prepareDailySalesChart();
                prepareSalesByStatusChart();
                prepareTopProductsChart();
                prepareSalesByHourChart();
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                loading.value = false;
            }
        };

        // Prepare daily sales chart
        const prepareDailySalesChart = () => {
            const data = dashboardData.value.daily_sales || [];
            if (data.length === 0) {
                dailySalesChartData.value = null;
                return;
            }

            dailySalesChartData.value = {
                labels: data.map(item => dayjs(item.date).format('MMM DD')),
                datasets: [
                    {
                        label: t('sales.revenue'),
                        data: data.map(item => parseFloat(item.revenue || 0)),
                        borderColor: '#1890ff',
                        backgroundColor: 'rgba(24, 144, 255, 0.1)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: t('sales.sales_count'),
                        data: data.map(item => parseInt(item.count || 0)),
                        borderColor: '#52c41a',
                        backgroundColor: 'rgba(82, 196, 26, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                    }
                ]
            };

            // Update options to include second y-axis
            lineChartOptions.scales.y1 = {
                position: 'right',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false,
                },
            };
        };

        // Prepare sales by status chart
        const prepareSalesByStatusChart = () => {
            const data = dashboardData.value.sales_by_status || [];
            if (data.length === 0) {
                salesByStatusChartData.value = null;
                return;
            }

            const statusColors = {
                paid: '#52c41a',
                pending: '#faad14',
                draft: '#d9d9d9',
                cancelled: '#ff4d4f'
            };

            salesByStatusChartData.value = {
                labels: data.map(item => t('sales.' + item.status)),
                datasets: [
                    {
                        data: data.map(item => parseFloat(item.total || 0)),
                        backgroundColor: data.map(item => statusColors[item.status] || '#1890ff'),
                    }
                ]
            };
        };

        // Prepare top products chart
        const prepareTopProductsChart = () => {
            const data = dashboardData.value.top_products || [];
            if (data.length === 0) {
                topProductsChartData.value = null;
                return;
            }

            // Take top 10
            const top10 = data.slice(0, 10);

            topProductsChartData.value = {
                labels: top10.map(item => item.product_name),
                datasets: [
                    {
                        label: t('sales.revenue'),
                        data: top10.map(item => parseFloat(item.total_revenue || 0)),
                        backgroundColor: '#1890ff',
                    }
                ]
            };
        };

        // Prepare sales by hour chart
        const prepareSalesByHourChart = () => {
            const data = dashboardData.value.sales_by_hour || [];
            if (data.length === 0) {
                salesByHourChartData.value = null;
                return;
            }

            // Create array for all 24 hours
            const hourlyData = Array(24).fill(0);
            data.forEach(item => {
                hourlyData[item.hour] = parseInt(item.count || 0);
            });

            salesByHourChartData.value = {
                labels: Array.from({ length: 24 }, (_, i) => i),
                datasets: [
                    {
                        label: t('sales.sales_count'),
                        data: hourlyData,
                        backgroundColor: '#52c41a',
                    }
                ]
            };
        };

        // Helper functions
        const formatCurrency = (amount) => {
            return parseFloat(amount || 0).toFixed(2);
        };

        const formatDate = (date) => {
            if (!date) return '';
            return dayjs(date).format('YYYY-MM-DD HH:mm');
        };

        const getStatusColor = (status) => {
            switch(status) {
                case 'paid':
                    return 'success';
                case 'pending':
                    return 'warning';
                case 'draft':
                    return 'default';
                case 'cancelled':
                    return 'error';
                default:
                    return 'default';
            }
        };

        onMounted(() => {
            fetchDashboardData();
        });

        return {
            h,
            loading,
            dateRange,
            dashboardData,
            dailySalesChartData,
            salesByStatusChartData,
            topProductsChartData,
            salesByHourChartData,
            lineChartOptions,
            barChartOptions,
            hourChartOptions,
            doughnutChartOptions,
            recentSalesColumns,
            fetchDashboardData,
            formatCurrency,
            formatDate,
            getStatusColor,
            permsArray,
        };
    },
};
</script>

<style scoped>
.stat-card {
    height: 100%;
}

.stat-card :deep(.ant-statistic-title) {
    font-size: 14px;
    margin-bottom: 8px;
}

.stat-card :deep(.ant-statistic-content) {
    font-size: 24px;
}

.table-responsive {
    overflow-x: auto;
}
</style>
