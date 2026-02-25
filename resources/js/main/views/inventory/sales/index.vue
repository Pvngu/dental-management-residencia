<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.sales`)" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <router-link :to="{ name: 'admin.sales.dashboard' }">
                    <a-button type="default">
                        <BarChartOutlined />
                        {{ $t("menu.sales_dashboard") }}
                    </a-button>
                </router-link>
                <template
                    v-if="
                        permsArray.includes('sales_create') ||
                        permsArray.includes('admin')
                    "
                >
                    <a-button type="primary" @click="addItem" style="margin-right: 8px">
                        <PlusOutlined />
                        {{ $t("sales.new_sale") }}
                    </a-button>
                    <a-button type="primary" @click="$router.push({ name: 'admin.sales.pos' })">
                        <ShoppingCartOutlined />
                        {{ $t("sales.pos_mode") }}
                    </a-button>
                </template>
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
                    {{ $t(`menu.inventory`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.sales`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />

        <!-- View Sale Modal -->
        <a-modal
            v-model:open="viewSaleVisible"
            :title="$t('sales.view_sale')"
            width="800px"
            :footer="null"
            @cancel="closeViewSale"
        >
            <div v-if="selectedSale">
                <!-- Sale Information -->
                <a-descriptions :column="2" bordered class="mb-4">
                    <a-descriptions-item :label="$t('sales.sale_number')">
                        {{ selectedSale.sale_number }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.status')">
                        <a-tag :color="getStatusColor(selectedSale.status)">
                            {{ selectedSale.status }}
                        </a-tag>
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.date')">
                        {{ formatDate(selectedSale.sold_at) }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.customer')">
                        {{ selectedSale.patient_name || 'N/A' }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.subtotal')">
                        ${{ formatCurrency(selectedSale.subtotal) }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.tax')">
                        ${{ formatCurrency(selectedSale.tax) }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.discount')">
                        ${{ formatCurrency(selectedSale.discount) }}
                    </a-descriptions-item>
                    <a-descriptions-item :label="$t('sales.total')">
                        <strong>${{ formatCurrency(selectedSale.total) }}</strong>
                    </a-descriptions-item>
                </a-descriptions>

                <!-- Sale Details -->
                <a-divider>{{ $t('sales.sale_details') }}</a-divider>
                
                <a-table
                    :columns="saleDetailsColumns"
                    :data-source="saleDetails"
                    :loading="loadingSaleDetails"
                    :pagination="false"
                    bordered
                    size="small"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'price_at_time'">
                            ${{ formatCurrency(record.price_at_time) }}
                        </template>
                        <template v-if="column.dataIndex === 'subtotal'">
                            ${{ formatCurrency(record.subtotal) }}
                        </template>
                        <template v-if="column.dataIndex === 'total'">
                            ${{ formatCurrency(record.total) }}
                        </template>
                    </template>
                </a-table>
            </div>
        </a-modal>

                <a-row class="mt-5">
                    <a-col :span="24">
                        <a-tabs
                            v-model:activeKey="filters.status"
                            @change="setUrlData"
                            centered
                            type="card"
                            class="table-tab-filters"
                        >
                            <a-tab-pane key="">
                                <template #tab>
                                    <span>
                                        <FileOutlined />
                                        {{ $t("common.all") }}
                                    </span>
                                </template>
                            </a-tab-pane>
                            <a-tab-pane key="pending">
                                <template #tab>
                                    <span>
                                        <ClockCircleOutlined />
                                        {{ $t("sales.pending") }}
                                    </span>
                                </template>
                            </a-tab-pane>
                            <a-tab-pane key="paid">
                                <template #tab>
                                    <span>
                                        <CheckCircleOutlined />
                                        {{ $t("sales.paid") }}
                                    </span>
                                </template>
                            </a-tab-pane>
                        </a-tabs>
                    </a-col>
                </a-row>

                <a-row class="mt-5">
                    <a-col :span="24">
                        <div class="table-responsive">
                            <a-table
                                :columns="columns"
                                :row-key="(record) => record.xid"
                                :data-source="table.data"
                                :pagination="table.pagination"
                                :loading="table.loading"
                                @change="handleTableChange"
                                bordered
                                size="middle"
                            >
                                <template #title>
                                    <a-row justify="end" align="middle" class="table-header">
                                        <a-col :xs="21" :sm="16" :md="16" :lg="12" :xl="8">
                                            <a-input-search
                                                style="width: 100%"
                                                v-model:value="table.searchString"
                                                :placeholder="$t('common.search')"
                                                show-search
                                                @search="onTableSearch"
                                                @change="onTableSearch"
                                                :loading="table.loading"
                                            />
                                        </a-col>
                                        <a-col class="ml-2">
                                            <Filters 
                                                @onReset="resetFilters" 
                                                :filters="filters"
                                            >
                                                <a-col :span="24">
                                                    <a-form-item :label="$t('common.date')">
                                                        <DateRangePicker
                                                            @dateTimeChanged="
                                                                (changedDateTime) => {
                                                                    extraFilters.dates = changedDateTime;
                                                                    setUrlData();
                                                                }
                                                            "
                                                        />
                                                    </a-form-item>
                                                </a-col>
                                            </Filters>
                                        </a-col>
                                    </a-row>
                                </template>

                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.dataIndex === 'sale_number'">
                                        <a href="#" @click.prevent="viewSale(record)">
                                            {{ record.sale_number }}
                                        </a>
                                    </template>
                                    <template v-if="column.dataIndex === 'sold_at'">
                                        {{ formatDate(record.sold_at) }}
                                    </template>
                                    <template v-if="column.dataIndex === 'status'">
                                        <a-tag :color="getStatusColor(record.status)">
                                            {{ record.status }}
                                        </a-tag>
                                    </template>
                                    <template v-if="column.dataIndex === 'subtotal'">
                                        ${{ formatCurrency(record.subtotal) }}
                                    </template>
                                    <template v-if="column.dataIndex === 'tax'">
                                        ${{ formatCurrency(record.tax) }}
                                    </template>
                                    <template v-if="column.dataIndex === 'discount'">
                                        ${{ formatCurrency(record.discount) }}
                                    </template>
                                    <template v-if="column.dataIndex === 'total'">
                                        <strong>${{ formatCurrency(record.total) }}</strong>
                                    </template>
                                    <template v-if="column.dataIndex === 'action'">
                                        <a-button
                                            v-if="permsArray.includes('sales_view') || permsArray.includes('admin')"
                                            type="primary"
                                            @click="viewSale(record)"
                                        >
                                            <template #icon><EyeOutlined /></template>
                                        </a-button>
                                        <a-button
                                            v-if="(permsArray.includes('sales_delete') || permsArray.includes('admin'))"
                                            type="primary"
                                            @click="showDeleteConfirm(record.xid)"
                                            style="margin-left: 4px"
                                            danger
                                        >
                                            <template #icon><DeleteOutlined /></template>
                                        </a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>
                    </a-col>
                </a-row>
    </admin-page-table-content>
</template>

<script>
import { ref, onMounted } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { 
    PlusOutlined, 
    DeleteOutlined, 
    FileOutlined,
    ClockCircleOutlined,
    CheckCircleOutlined,
    ShoppingCartOutlined,
    EditOutlined,
    EyeOutlined,
    BarChartOutlined
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import apiAdmin from "../../../../common/composable/apiAdmin";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        FileOutlined,
        ClockCircleOutlined,
        CheckCircleOutlined,
        ShoppingCartOutlined,
        EditOutlined,
        BarChartOutlined,
        EyeOutlined,
        AdminPageHeader,
        AddEdit,
        Filters,
        DateRangePicker
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            getPrefetchData,
            hashableColumns,
        } = fields();
        
        const { permsArray } = common();
        const { loading } = apiAdmin();
        const crudVariables = crud();
        const extraFilters = ref({
            dates: [],
        });
        const filters = ref({
            status: "",
        });

        // View sale modal state
        const viewSaleVisible = ref(false);
        const selectedSale = ref(null);
        const saleDetails = ref([]);
        const loadingSaleDetails = ref(false);

        // Reset filters
        const resetFilters = () => {
            filters.value = {
                status: "",
            };
            extraFilters.value = {
                dates: [],
            };
            setUrlData();
        };

        // Sale details columns
        const saleDetailsColumns = [
            {
                title: 'Product',
                dataIndex: 'product_name',
                key: 'product_name',
            },
            {
                title: 'Quantity',
                dataIndex: 'quantity',
                key: 'quantity',
                width: 100,
            },
            {
                title: 'Unit Price',
                dataIndex: 'price_at_time',
                key: 'price_at_time',
                width: 120,
            },
            {
                title: 'Subtotal',
                dataIndex: 'subtotal',
                key: 'subtotal',
                width: 120,
            },
            {
                title: 'Total',
                dataIndex: 'total',
                key: 'total',
                width: 120,
            },
        ];

        // Get status color
        function getStatusColor(status) {
            switch(status) {
                case 'paid':
                    return 'success';
                case 'pending':
                    return 'warning';
                default:
                    return 'default';
            }
        }

        // Format currency
        function formatCurrency(amount) {
            return parseFloat(amount || 0).toFixed(2);
        }

        // Format date
        function formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleString();
        }

        // View sale - show sale details in read-only modal
        async function viewSale(record) {
            selectedSale.value = record;
            viewSaleVisible.value = true;
            loadingSaleDetails.value = true;
            
            try {
                // Fetch sale details
                const response = await window.axiosAdmin.get(`sale_details?sale_id=${record.xid}&fields=id,xid,item_id,product_name,quantity,price_at_time,subtotal,total`);
                saleDetails.value = response.data || [];
            } catch (error) {
                console.error('Error fetching sale details:', error);
                saleDetails.value = [];
            } finally {
                loadingSaleDetails.value = false;
            }
        }

        // Close view sale modal
        function closeViewSale() {
            viewSaleVisible.value = false;
            selectedSale.value = null;
            saleDetails.value = [];
        }

        onMounted(() => {
            try {
                getPrefetchData().then(() => {
                    if (filterableColumns && filterableColumns.value) {
                        crudVariables.table.filterableColumns = filterableColumns.value;
                    }

                    crudVariables.crudUrl.value = addEditUrl;
                    crudVariables.langKey.value = "sales";
                    crudVariables.initData.value = { ...initData };
                    crudVariables.formData.value = { ...initData };

                    setUrlData();
                }).catch(error => {
                    console.error("Error in getPrefetchData:", error);
                    // Continue with basic initialization even if prefetch fails
                    crudVariables.crudUrl.value = addEditUrl;
                    crudVariables.langKey.value = "sales";
                    crudVariables.initData.value = { ...initData };
                    crudVariables.formData.value = { ...initData };
                    setUrlData();
                });
            } catch (error) {
                console.error("Error in onMounted:", error);
            }
        });

        const setUrlData = () => {
            crudVariables.tableUrl.value = {
                url,
                filters,
                extraFilters,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            filters,
            extraFilters,
            resetFilters,
            loading,
            getStatusColor,
            formatCurrency,
            formatDate,
            viewSale,
            closeViewSale,
            viewSaleVisible,
            selectedSale,
            saleDetails,
            loadingSaleDetails,
            saleDetailsColumns
        };
    },
};
</script>

<style scoped>
/* Sales Index Specific Styles */
.table-header {
    margin-bottom: 16px;
}

.table-responsive {
    overflow-x: auto;
}
</style>