<template>
    <div>
        <a-skeleton v-if="loading" active :paragraph="{ rows: 8 }" />
        <div v-else>
            <div class="table-responsive">
                <a-table
                    :columns="columns"
                    :row-key="(record) => record.xid"
                    :data-source="sales"
                    :pagination="pagination"
                    :loading="loading"
                    @change="handleTableChange"
                    bordered
                    size="middle"
                >
                    <template #bodyCell="{ column, record }">
                        <template v-if="column.dataIndex === 'sale_number'">
                            {{ record.sale_number }}
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
                                type="primary"
                                size="small"
                                @click="viewSale(record)"
                                :icon="h(EyeOutlined)"
                            >
                                {{ $t('common.view') }}
                            </a-button>
                        </template>
                    </template>
                </a-table>
            </div>
        </div>

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
    </div>
</template>

<script setup>
import { ref, onMounted, h } from "vue";
import { useI18n } from "vue-i18n";
import { EyeOutlined } from "@ant-design/icons-vue";
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
const sales = ref([]);
const pagination = ref({
    current: 1,
    pageSize: 10,
    total: 0,
    showSizeChanger: true,
    showQuickJumper: true,
    showTotal: (total) => `${t('common.total')} ${total} ${t('common.items')}`,
});

// View sale modal state
const viewSaleVisible = ref(false);
const selectedSale = ref(null);
const saleDetails = ref([]);
const loadingSaleDetails = ref(false);

const columns = [
    {
        title: t("sales.sale_number"),
        dataIndex: "sale_number",
    },
    {
        title: t("sales.date"),
        dataIndex: "sold_at",
        sorter: true,
    },
    {
        title: t("sales.status"),
        dataIndex: "status",
    },
    {
        title: t("sales.subtotal"),
        dataIndex: "subtotal",
        align: "right",
    },
    {
        title: t("sales.tax"),
        dataIndex: "tax",
        align: "right",
    },
    {
        title: t("sales.discount"),
        dataIndex: "discount",
        align: "right",
    },
    {
        title: t("sales.total"),
        dataIndex: "total",
        align: "right",
    },
    {
        title: t("common.action"),
        dataIndex: "action",
        align: "center",
    },
];

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

const fetchSales = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axiosAdmin.get(`sales?patient_id=${props.patientId}&fields=id,xid,sale_number,sold_at,status,subtotal,tax,discount,total&page=${page}&limit=${pagination.value.pageSize}`);
        sales.value = response.data || [];
        
        if (response.meta) {
            pagination.value.total = response.meta.total;
            pagination.value.current = response.meta.current_page;
        }
    } catch (error) {
        console.error('Error fetching sales:', error);
    } finally {
        loading.value = false;
    }
};

const handleTableChange = (pag, filters, sorter) => {
    pagination.value.current = pag.current;
    pagination.value.pageSize = pag.pageSize;
    fetchSales(pag.current);
};

const getStatusColor = (status) => {
    switch(status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        default:
            return 'default';
    }
};

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

// View sale - show sale details in read-only modal
const viewSale = async (record) => {
    selectedSale.value = record;
    viewSaleVisible.value = true;
    loadingSaleDetails.value = true;
    
    try {
        // Fetch sale details
        const response = await axiosAdmin.get(`sale_details?sale_id=${record.xid}&fields=id,xid,item_id,product_name,quantity,price_at_time,subtotal,total`);
        saleDetails.value = response.data || [];
    } catch (error) {
        console.error('Error fetching sale details:', error);
        saleDetails.value = [];
    } finally {
        loadingSaleDetails.value = false;
    }
};

// Close view sale modal
const closeViewSale = () => {
    viewSaleVisible.value = false;
    selectedSale.value = null;
    saleDetails.value = [];
};

onMounted(() => {
    fetchSales();
});
</script>

<style scoped>
.table-responsive {
    width: 100%;
}
</style>
