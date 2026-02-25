<template>
    <div>
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
            :categories="categories"
            :brands="brands"
            :manufacturers="manufacturers"
            :suppliers="suppliers"
        />
        
        <ViewDetails
            :visible="isViewDrawerVisible"
            :data="viewDrawerData"
            @closed="hideViewDrawer"
        />
        
        <StockAdjustment
            :visible="stockAdjustmentVisible"
            :itemData="stockAdjustmentItem"
            :adjustmentReasons="adjustmentReasons"
            @closed="onCloseStockAdjustment"
            @adjustmentSuccess="onAdjustmentSuccess"
            @editItem="handleEditFromAdjustment"
        />
        
        <!-- Item Statistics Cards -->
        <a-row :gutter="[16,16]">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('items.total_items')"
                    :value="itemStats.totalItems || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('items.available_quantity')"
                    :value="itemStats.availableQuantity || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('items.low_stock_alerts')"
                    :value="itemStats.lowStockAlerts || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('items.out_of_stock')"
                    :value="itemStats.outOfStockItems || 0"
                    :loading="loadingStats"
                />
            </a-col>
        </a-row>

        <a-row class="mt-5">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="extraFilters.stock_status" 
                    @change="setUrlData" 
                >
                    <template #rightExtra>
                        <a-space>
                            <!-- Hidden input for barcode scanner -->
                            <input
                                v-model="scanInput"
                                @input="handleScanInput"
                                ref="scanInputRef"
                                style="position: absolute; left: -9999px; width: 1px; height: 1px;"
                                autocomplete="off"
                            />
                            <a-button
                                :type="scanActive ? 'primary' : 'default'"
                                @click="toggleScanMode"
                                :loading="scanLoading"
                            >
                                <template #icon>
                                    <ScanOutlined :class="{ 'scan-pulse': scanActive }" />
                                </template>
                                {{ scanActive ? $t("items.scanning") : $t("items.scan") }}
                            </a-button>
                        </a-space>
                    </template>
                    <a-tab-pane key="">
                        <template #tab>
                            <span>
                                <InboxOutlined />
                                {{ $t("common.all") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="low_stock">
                        <template #tab>
                            <span>
                                <WarningOutlined />
                                {{ $t("items.low_stock") }}
                                <a-badge 
                                    :count="itemStats.lowStockAlerts" 
                                    :number-style="{ backgroundColor: '#faad14', marginLeft: '8px' }"
                                />
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
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
                                <a-col 
                                    :xs="21"
                                    :sm="16"
                                    :md="16"
                                    :lg="12"
                                    :xl="8"
                                >
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
                                            <a-form-item :label="$t('items.item_category')">
                                                <a-select
                                                    v-model:value="filters.category_id"
                                                    :placeholder="
                                                        $t('common.select_default_text', [
                                                            $t('items.item_category'),
                                                        ])
                                                    "
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    optionFilterProp="title"
                                                    show-search
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="category in categories"
                                                        :key="category.xid"
                                                        :title="category.name"
                                                        :value="category.xid"
                                                    >
                                                        {{ category.name }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                        <a-col :span="24">
                                            <a-form-item :label="$t('items.item_brand')">
                                                <a-select
                                                    v-model:value="filters.brand_id"
                                                    :placeholder="
                                                        $t('common.select_default_text', [
                                                            $t('items.item_brand'),
                                                        ])
                                                    "
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    optionFilterProp="title"
                                                    show-search
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="brand in brands"
                                                        :key="brand.xid"
                                                        :title="brand.name"
                                                        :value="brand.xid"
                                                    >
                                                        {{ brand.name }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                        <a-col :span="24">
                                            <a-form-item :label="$t('items.item_manufacturer')">
                                                <a-select
                                                    v-model:value="filters.manufacturer_id"
                                                    :placeholder="
                                                        $t('common.select_default_text', [
                                                            $t('items.item_manufacturer'),
                                                        ])
                                                    "
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    optionFilterProp="title"
                                                    show-search
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option
                                                        v-for="manufacturer in manufacturers"
                                                        :key="manufacturer.xid"
                                                        :title="manufacturer.name"
                                                        :value="manufacturer.xid"
                                                    >
                                                        {{ manufacturer.name }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'name'">
                                <a @click="showViewDrawer(record)">
                                    {{ record.name }}
                                </a>
                            </template>
                            <template v-if="column.dataIndex === 'category'">
                                {{ record.category ? record.category.name : '-' }}
                            </template>
                            <template v-if="column.dataIndex === 'sale_price'">
                                {{ record.sale_price ? `$${Number(record.sale_price).toFixed(2)}` : '-' }}
                            </template>
                            <template v-if="column.dataIndex === 'cost_price'">
                                {{ record.cost_price ? `$${Number(record.cost_price).toFixed(2)}` : '-' }}
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('items_edit') ||
                                        permsArray.includes('admin')
                                    "
                                    type="primary"
                                    @click="editItem(record)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><EditOutlined /></template>
                                </a-button>
                                <a-button
                                    v-if="
                                        (permsArray.includes('items_delete') ||
                                            permsArray.includes('admin')) &&
                                        (!record.children ||
                                            record.children.length == 0)
                                    "
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon
                                        ><DeleteOutlined
                                    /></template>
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { useI18n } from "vue-i18n";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import ViewDetails from "./ViewDetails.vue";
import StockAdjustment from "./StockAdjustment.vue";
import StateWidget from "../../../../common/components/common/card/StateWidget.vue";
import Filters from "../../../../common/components/common/select/Filters.vue";
import { 
    PlusOutlined, 
    DeleteOutlined, 
    EditOutlined,
    InboxOutlined,
    WarningOutlined,
    BarcodeOutlined,
    ScanOutlined
} from "@ant-design/icons-vue";
import viewDrawer from "../../../../common/composable/viewDrawer";
import { message } from "ant-design-vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        InboxOutlined,
        WarningOutlined,
        BarcodeOutlined,
        ScanOutlined,
        AddEdit,
        ViewDetails,
        StockAdjustment,
        StateWidget,
        Filters
    },
    setup() {
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
            getPrefetchData,
            categories,
            brands,
            manufacturers,
            suppliers,
        } = fields();
        const { permsArray } = common();
        const crudVariables = crud();
        const { t } = useI18n();

        const filters = ref({
            category_id: undefined,
            brand_id: undefined,
            manufacturer_id: undefined,
        });

        const extraFilters = ref({
            stock_status: "",
        });

        const drawerView = viewDrawer();

        const itemStats = ref({
            totalItems: 0,
            availableQuantity: 0,
            lowStockAlerts: 0,
            outOfStockItems: 0,
        });

        const loadingStats = ref(true);
        const scanInput = ref("");
        const scanInputRef = ref(null);
        const scanLoading = ref(false);
        const scanActive = ref(false);
        const scanTimeout = ref(null);
        const stockAdjustmentVisible = ref(false);
        const stockAdjustmentItem = ref(null);
        const adjustmentReasons = ref([]);

        const fetchItemStats = () => {
            axiosAdmin
                .get("/items/stats")
                .then((response) => {
                    itemStats.value = response;
                })
                .catch((error) => {
                    console.error("Error fetching item statistics:", error);
                })
                .finally(() => {
                    loadingStats.value = false;
                });
        };

        const fetchAdjustmentReasons = async () => {
            try {
                const response = await axiosAdmin.get("adjustments-reason/all");
                adjustmentReasons.value = response.data;
            } catch (error) {
                console.error("Error fetching adjustment reasons:", error);
            }
        };

        const toggleScanMode = () => {
            scanActive.value = !scanActive.value;
            
            if (scanActive.value) {
                // Focus the hidden input to receive scanner input
                if (scanInputRef.value) {
                    scanInputRef.value.focus();
                }
                message.info(t('items.scan_mode_active'));
            } else {
                // Deactivate scan mode
                if (scanInputRef.value) {
                    scanInputRef.value.blur();
                }
                scanInput.value = "";
                if (scanTimeout.value) {
                    clearTimeout(scanTimeout.value);
                }
            }
        };

        const handleScanInput = () => {
            // Clear existing timeout
            if (scanTimeout.value) {
                clearTimeout(scanTimeout.value);
            }

            // Set a timeout to process the scan after input stops (barcode scanners are fast)
            scanTimeout.value = setTimeout(() => {
                if (scanInput.value && scanInput.value.trim() !== "") {
                    processBarcodeScan();
                }
            }, 100); // 100ms delay to ensure full barcode is captured
        };

        const processBarcodeScan = async () => {
            if (!scanActive.value) return;
            
            const scannedSku = scanInput.value.trim();
            if (!scannedSku) return;

            scanLoading.value = true;
            // scanActive.value = false; // DELETED THIS LINE to keep scan mode active
            
            try {
                // Search for item by SKU
                const response = await axiosAdmin.get(`items/search-by-sku/${scannedSku}`);
                
                if (response.found && response.data) {
                    // Item found, open stock adjustment drawer
                    stockAdjustmentItem.value = response.data;
                    stockAdjustmentVisible.value = true;
                } else {
                    // Item not found, open create drawer with pre-filled SKU
                    message.info(response.message || "Item not found. Opening create form...");
                    // Call addItem first to properly initialize the crud state
                    crudVariables.addItem();
                    // Then update the formData with the scanned SKU
                    crudVariables.formData.value = { ...initData, sku: scannedSku };
                }
            } catch (error) {
                // Handle unexpected errors
                message.error("Error searching for item");
                console.error("Error searching item:", error);
            } finally {
                scanInput.value = "";
                scanLoading.value = false;
                
                // Refocus input if scan is still active and no modal opened
                if (scanActive.value && !stockAdjustmentVisible.value && !crudVariables.addEditVisible.value) {
                    if (scanInputRef.value) {
                        scanInputRef.value.focus();
                    }
                }
            }
        };

        // Re-focus scanner when modals close if scan mode is active
        watch([stockAdjustmentVisible, crudVariables.addEditVisible], ([newStockVal, newAddEditVal], [oldStockVal, oldAddEditVal]) => {
            if (scanActive.value && !newStockVal && !newAddEditVal && (oldStockVal || oldAddEditVal)) {
                setTimeout(() => {
                    if (scanInputRef.value) {
                        scanInputRef.value.focus();
                        // message.info("Ready to scan");
                    }
                }, 300); // Small delay to allow modal animation to finish
            }
        });

        const onCloseStockAdjustment = () => {
            stockAdjustmentVisible.value = false;
            stockAdjustmentItem.value = null;
        };

        const onAdjustmentSuccess = () => {
            // Refresh the table and stats
            crudVariables.fetch({
                page: 1,
            });
            fetchItemStats();
        };

        const handleEditFromAdjustment = (item) => {
            // Close adjustment drawer
            stockAdjustmentVisible.value = false;
            // Open edit drawer
            crudVariables.editItem(item);
        };

        onMounted(() => {
            getPrefetchData();
            setUrlData();
            fetchItemStats();
            fetchAdjustmentReasons();
            
            // Listen for add item event from parent
            window.addEventListener('add-item', crudVariables.addItem);
        });

        const setUrlData = () => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "items";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

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

        const resetFilters = () => {
            filters.value = {
                category_id: undefined,
                brand_id: undefined,
                manufacturer_id: undefined,
            };
            extraFilters.value = {
                stock_status: "",
            };
            setUrlData();
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            resetFilters,
            filters,
            extraFilters,
            categories,
            brands,
            manufacturers,
            suppliers,
            itemStats,
            loadingStats,
            scanInput,
            scanInputRef,
            scanLoading,
            scanActive,
            toggleScanMode,
            handleScanInput,
            stockAdjustmentVisible,
            stockAdjustmentItem,
            adjustmentReasons,
            onCloseStockAdjustment,
            onAdjustmentSuccess,
            handleEditFromAdjustment,
            ...drawerView
        };
    },
};
</script>

<style scoped>
@keyframes scan-pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.1);
    }
}

.scan-pulse {
    animation: scan-pulse 1.5s ease-in-out infinite;
}
</style>
