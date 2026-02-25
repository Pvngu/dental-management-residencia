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
        />
        <!-- Medicine Statistics Cards -->
        <a-row :gutter="[16,16]" class="mb-4">
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('medicine.total_available')"
                    :value="medicineStats.totalAvailableQuantity || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('medicine.out_of_stock')"
                    :value="medicineStats.outOfStockMedicines || 0"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('medicine.most_sold')"
                    :value="medicineStats.mostSoldMedicine || '-'"
                    :loading="loadingStats"
                />
            </a-col>
            <a-col :xs="24" :sm="12" :md="6" :lg="6">
                <StateWidget
                    :title="$t('medicine.expiry_alerts')"
                    :value="medicineStats.expiryAlerts || 0"
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
                                {{ scanActive ? $t("medicine.scanning") : $t("medicine.scan") }}
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
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="expired">
                        <template #tab>
                            <span>
                                <CloseCircleOutlined />
                                {{ $t("medicine.expired") }}
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
                        @expand="onExpand"
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
                                            <a-form-item :label="$t('medicine.category')">
                                                <a-select
                                                    v-model:value="filters.category_id"
                                                    :placeholder="$t('common.select_default_text', [$t('medicine.category')])"
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
                                            <a-form-item :label="$t('medicine.brand')">
                                                <a-select
                                                    v-model:value="filters.brand_id"
                                                    :placeholder="$t('common.select_default_text', [$t('medicine.brand')])"
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
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>
                        <template #expandedRowRender="{ record }">
                            <div style="margin: 0">
                                <a-spin :spinning="batchesLoading[record.xid]">
                                    <div v-if="batchesData[record.xid]">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                            <p style="font-weight: 600; margin: 0;">
                                                {{ $t('medicine.batch_details') }} 
                                                ({{ batchesData[record.xid].active_batches }} {{ $t('medicine.active_batches') }})
                                            </p>
                                            <a-tag color="blue" style="margin: 0;">
                                                <InfoCircleOutlined /> {{ $t('medicine.fefo_enabled') }}
                                            </a-tag>
                                        </div>
                                        <a-table
                                            :columns="batchColumns"
                                            :data-source="batchesData[record.xid].batches"
                                            :row-key="(batch) => batch.xid"
                                            :pagination="false"
                                            size="small"
                                            bordered
                                        >
                                            <template #bodyCell="{ column, record: batch, index }">
                                                <template v-if="column.dataIndex === 'priority'">
                                                    <a-tag :color="index === 0 ? 'blue' : 'default'">
                                                        {{ index + 1 }}
                                                    </a-tag>
                                                </template>
                                                <template v-if="column.dataIndex === 'status'">
                                                    <a-tag v-if="batch.status === 'expired'" color="error">
                                                        <WarningOutlined /> {{ $t('medicine.expired') }}
                                                    </a-tag>
                                                    <a-tag v-else-if="batch.status === 'expiring_soon'" color="warning">
                                                        <ClockCircleOutlined /> {{ $t('medicine.expiring_soon') }}
                                                    </a-tag>
                                                    <a-tag v-else color="success">
                                                        <CheckCircleOutlined /> {{ $t('medicine.healthy') }}
                                                    </a-tag>
                                                </template>
                                            </template>
                                        </a-table>
                                    </div>
                                    <a-empty v-else-if="!batchesLoading[record.xid]" :description="$t('medicine.no_batches')" />
                                </a-spin>
                            </div>
                        </template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'action'">
                                <a-button
                                    v-if="
                                        permsArray.includes('medicines_edit') ||
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
                                        (permsArray.includes('medicines_delete') ||
                                            permsArray.includes('admin')) &&
                                        (!record.children || record.children.length == 0)
                                    "
                                    type="primary"
                                    @click="showDeleteConfirm(record.xid)"
                                    style="margin-left: 4px"
                                >
                                    <template #icon><DeleteOutlined /></template>
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
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import {
    PlusOutlined,
    DeleteOutlined,
    EditOutlined,
    WarningOutlined,
    CheckCircleOutlined,
    ClockCircleOutlined,
    InfoCircleOutlined,
    InboxOutlined,
    CloseCircleOutlined,
    ScanOutlined,
} from "@ant-design/icons-vue";
import crud from "../../../../common/composable/crud";
import common from "../../../../common/composable/common";
import fields from "./fields";
import AddEdit from "./AddEdit.vue";
import Filters from "../../../../common/components/common/select/Filters.vue";
import StateWidget from "../../../../common/components/common/card/StateWidget.vue";
import { message } from "ant-design-vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        EditOutlined,
        WarningOutlined,
        CheckCircleOutlined,
        ClockCircleOutlined,
        InfoCircleOutlined,
        InboxOutlined,
        CloseCircleOutlined,
        ScanOutlined,
        AddEdit,
        Filters,
        StateWidget,
    },
    setup() {
        const { t } = useI18n();
        const {
            url,
            addEditUrl,
            initData,
            columns,
            filterableColumns,
            hashableColumns,
            categories,
            brands,
            getPrefetchData,
        } = fields();
        const { permsArray, formatAmountCurrency } = common();
        const crudVariables = crud();

        // Batch columns for expanded row
        const batchColumns = computed(() => [
            {
                title: t('medicine.priority'),
                dataIndex: 'priority',
                key: 'priority',
                width: 80,
            },
            {
                title: t('medicine.batch_number'),
                dataIndex: 'lot_no',
                key: 'lot_no',
            },
            {
                title: t('medicine.quantity'),
                dataIndex: 'quantity',
                key: 'quantity',
                width: 100,
            },
            {
                title: t('medicine.expiration_date'),
                dataIndex: 'formatted_expiry_date',
                key: 'formatted_expiry_date',
                width: 150,
            },
            {
                title: t('common.status'),
                dataIndex: 'status',
                key: 'status',
                width: 150,
            },
            {
                title: t('medicine.received'),
                dataIndex: 'received_date',
                key: 'received_date',
                width: 130,
            },
        ]);

        // Batches data storage
        const batchesData = ref({});
        const batchesLoading = ref({});

        // Fetch batches for a medicine when row is expanded
        const fetchBatches = (medicineXid) => {
            if (batchesData.value[medicineXid]) {
                return; // Already fetched
            }
            
            batchesLoading.value[medicineXid] = true;
            
            axiosAdmin
                .get(`/medicines/${medicineXid}/batches`)
                .then((response) => {
                    batchesData.value[medicineXid] = response;
                })
                .catch((error) => {
                    console.error("Error fetching batches:", error);
                    batchesData.value[medicineXid] = { batches: [], active_batches: 0, total_batches: 0 };
                })
                .finally(() => {
                    batchesLoading.value[medicineXid] = false;
                });
        };

        const onExpand = (expanded, record) => {
            if (expanded) {
                fetchBatches(record.xid);
            }
        };

        // Medicine statistics
        const medicineStats = ref({
            totalAvailableQuantity: 0,
            outOfStockMedicines: 0,
            mostSoldMedicine: '-',
            expiryAlerts: 0
        });

        const loadingStats = ref(true);    

        const fetchMedicineStats = () => {
            axiosAdmin
            .get("/medicines/stats")
            .then((response) => {
                medicineStats.value = response;
            })
            .catch((error) => {
                console.error("Error fetching medicine statistics:", error);
            })
            .finally(() => {
                loadingStats.value = false;
            });
        };

        // Add filters for category_id and brand_id
        const filters = ref({
            category_id: undefined,
            brand_id: undefined,
        });

        const extraFilters = ref({
            stock_status: "",
        });

        const scanInput = ref("");
        const scanInputRef = ref(null);
        const scanLoading = ref(false);
        const scanActive = ref(false);
        const scanTimeout = ref(null);

        const resetFilters = () => {
            filters.value = {
                category_id: undefined,
                brand_id: undefined,
            };
            extraFilters.value = {
                stock_status: "",
            };
            setUrlData();
        };

        const toggleScanMode = () => {
            scanActive.value = !scanActive.value;
            
            if (scanActive.value) {
                // Focus the hidden input to receive scanner input
                if (scanInputRef.value) {
                    scanInputRef.value.focus();
                }
                message.info(t('medicine.scan_mode_active'));
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
            
            try {
                // Search for medicine by SKU
                const response = await axiosAdmin.get(`medicines/search-by-sku/${scannedSku}`);
                
                if (response.found && response.data) {
                    // Medicine found, open edit drawer
                    message.success(response.message || "Medicine found");
                    crudVariables.editItem(response.data);
                } else {
                    // Medicine not found, open create drawer with pre-filled SKU
                    message.info(response.message || "Medicine not found. Opening create form...");
                    // Call addItem first to properly initialize the crud state
                    crudVariables.addItem();
                    // Then update the formData with the scanned SKU
                    crudVariables.formData.value = { ...initData, sku: scannedSku };
                }
            } catch (error) {
                // Handle unexpected errors
                message.error("Error searching for medicine");
                console.error("Error searching medicine:", error);
            } finally {
                scanInput.value = "";
                scanLoading.value = false;
                
                // Refocus input if scan is still active and no modal opened
                if (scanActive.value && !crudVariables.addEditVisible.value) {
                    setTimeout(() => {
                        if (scanInputRef.value) {
                            scanInputRef.value.focus();
                        }
                    }, 300);
                }
            }
        };

        // Re-focus scanner when modal closes if scan mode is active
        watch(crudVariables.addEditVisible, (newVal, oldVal) => {
            if (scanActive.value && !newVal && oldVal) {
                setTimeout(() => {
                    if (scanInputRef.value) {
                        scanInputRef.value.focus();
                    }
                }, 300); // Small delay to allow modal animation to finish
            }
        });

        const editItem = (record) => {
            crudVariables.editItem(record);
        };

        onMounted(() => {
            getPrefetchData();
            setUrlData();
            fetchMedicineStats();
            
            // Listen for add event from parent
            window.addEventListener('add-medicine', crudVariables.addItem);
        });
        
        onBeforeUnmount(() => {
            window.removeEventListener('add-medicine', crudVariables.addItem);
        });

        const setUrlData = () => {
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.crudUrl.value = addEditUrl;
            crudVariables.langKey.value = "medicine";
            crudVariables.initData.value = { ...initData };
            crudVariables.formData.value = { ...initData };

            crudVariables.tableUrl.value = {
            editItem,
                url,
                filters,
                extraFilters,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const addEditSuccess = (xid) => {
            crudVariables.addEditSuccess(xid);
            // Refresh stats when a medicine is added or updated
            fetchMedicineStats();
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            filterableColumns,
            setUrlData,
            formatAmountCurrency,
            filters,
            extraFilters,
            resetFilters,
            medicineStats,
            fetchMedicineStats,
            addEditSuccess,
            loadingStats,
            categories,
            brands,
            batchColumns,
            batchesData,
            batchesLoading,
            onExpand,
            scanInput,
            scanInputRef,
            scanLoading,
            scanActive,
            toggleScanMode,
            handleScanInput,
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