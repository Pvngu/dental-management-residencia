<template>
    <a-drawer
        :title="$t('items.item_details')"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-descriptions :column="{ xxl: 2, xl: 2, lg: 2, md: 1, sm: 1, xs: 1 }" bordered>
            <a-descriptions-item :label="$t('items.name')">{{ data.name }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.item_code')">{{ data.item_code }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.category')">{{ data.category ? data.category.name : '-' }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.brand')">{{ data.brand ? data.brand.name : '-' }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.manufacturer')">{{ data.manufacturer ? data.manufacturer.name : '-' }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.available_quantity')">{{ data.available_quantity }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.unit')">{{ data.unit }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.cost_price')">{{ data.cost_price }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.sell_price')">{{ data.sell_price }}</a-descriptions-item>
            <a-descriptions-item :label="$t('items.description')" :span="2">
                <div v-html="data.description"></div>
            </a-descriptions-item>
        </a-descriptions>

        <!-- Item Adjustments History -->
        <div class="mt-5">
            <h2>{{ $t('items.adjustment_history') }}</h2>
            <a-table
                :columns="columns"
                :row-key="(record) => record.xid"
                :data-source="adjustments"
                :pagination="{ pageSize: 5 }"
                :loading="loading"
                bordered
                size="middle"
            >
                <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'date'">
                        {{ formatDate(record.date) }}
                    </template>
                    <template v-if="column.dataIndex === 'adjustment_type'">
                        <a-tag :color="record.adjustment_type === 'addition' ? 'green' : 'red'">
                            {{  record.adjustment_type }}
                        </a-tag>
                    </template>
                </template>
            </a-table>
        </div>

        <template #footer>
            <a-button @click="onClose">
                {{ $t("common.close") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import common from "../../../../common/composable/common";

const props = defineProps({
    visible: Boolean,
    data: Object,
});
const emit = defineEmits(["closed"]);

const { t } = useI18n();
const loading = ref(false);
const adjustments = ref([]);
const { formatDate } = common();

const columns = [
    {
        title: t("common.date"),
        dataIndex: "date",
    },
    {
        title: t("inventory_adjustment.reference_number"),
        dataIndex: "reference_number",
    },
    {
        title: t("inventory_adjustment.reason"),
        dataIndex: "reason",
    },
    {
        title: t("inventory_adjustment.type"),
        dataIndex: "adjustment_type",
    },
    {
        title: t("inventory_adjustment.quantity"),
        dataIndex: "quantity",
    }
];

const fetchAdjustmentHistory = async () => {
    if (props.data && props.data.xid) {
        loading.value = true;
        try {
            const response = await axiosAdmin.get(`inventory-adjustments/item/${props.data.xid}`);
            adjustments.value = response.data;
        } catch (error) {
            console.error("Error fetching adjustment history:", error);
        } finally {
            loading.value = false;
        }
    }
};

onMounted(() => {
    if (props.visible && props.data && props.data.xid) {
        fetchAdjustmentHistory();
    }
});

watch(
    () => props.visible,
    (newVal) => {
        if (newVal && props.data && props.data.xid) {
            fetchAdjustmentHistory();
        }
    }
);

const onClose = () => {
    emit("closed");
};

const drawerWidth = window.innerWidth <= 991 ? "90%" : "60%";
</script>
