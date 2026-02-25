<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <a-row>
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('inventory_adjustment.reference_number')"
                                name="reference_number"
                                :help="
                                    rules.reference_number
                                        ? rules.reference_number.message
                                        : null
                                "
                                :validateStatus="
                                    rules.reference_number ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.reference_number"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('inventory_adjustment.reference_number'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('inventory_adjustment.date')"
                                name="date"
                                :help="
                                    rules.date
                                        ? rules.date.message
                                        : null
                                "
                                :validateStatus="
                                    rules.date ? 'error' : null
                                "
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.date"
                                    :isFutureDateDisabled="false"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.date = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('inventory_adjustment.reason')"
                                name="adjustment_reason"
                                :help="
                                    rules.adjustments_reason_id
                                        ? rules.adjustments_reason_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.adjustments_reason_id ? 'error' : null
                                "
                                class="required"
                            >
                                <SelectInput
                                    :value="formData.adjustments_reason_id"
                                    simple-form
                                    url="adjustments-reason"
                                    :placeholder="$t('inventory_adjustment.reason')"
                                    :options="adjustmentReasons"
                                    @onChange="(value) => formData.adjustments_reason_id = value"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('common.description')"
                                name="description"
                                :help="
                                    rules.description
                                        ? rules.description.message
                                        : null
                                "
                                :validateStatus="
                                    rules.description ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.description"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('common.description'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 2, maxRows: 4 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- Adjustment Items Table -->
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <div class="adjustment-items-table">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                    <h3 style="margin: 0;">{{ $t('inventory_adjustment.adjustment_items') }}</h3>
                                    <!-- Scanner Button -->
                                    <div>
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
                                            size="small"
                                        >
                                            <template #icon>
                                                <ScanOutlined :class="{ 'scan-pulse': scanActive }" />
                                            </template>
                                            {{ scanActive ? $t("items.scanning") : $t("items.scan") }}
                                        </a-button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <a-table 
                                        :columns="itemColumns" 
                                        :dataSource="formData.adjustment_items"
                                        :pagination="false"
                                        bordered
                                        size="small"
                                    >
                                        <template #bodyCell="{ column, record, index }">
                                            <template v-if="column.dataIndex === 'action'">
                                                <a-button type="primary" danger @click="removeItem(index)" size="small">
                                                    <template #icon><DeleteOutlined /></template>
                                                </a-button>
                                            </template>
                                            <template v-if="column.dataIndex === 'item_id'">
                                                <ItemSelect
                                                    :value="record.item_id"
                                                    :placeholder="$t('common.select_default_text', [$t('inventory_adjustment.item')])"
                                                    apiUrl="items"
                                                    apiParams="fields=id,xid,name,available_quantity,sku,unit&limit=1000"
                                                    :dropdownMatchSelectWidth="false"
                                                    optionLabelProp="label"
                                                    @onChange="(value, selectedItem) => onItemSelected(value, selectedItem, index)"
                                                />
                                            </template>
                                            <template v-if="column.dataIndex === 'current_quantity'">
                                                <span>{{ record.current_quantity || 0 }}</span>
                                            </template>
                                            <template v-if="column.dataIndex === 'new_quantity'">
                                                <a-input-number
                                                    v-model:value="record.new_quantity"
                                                    style="width: 100%"
                                                    @change="(value) => calculateAdjustmentQuantity(index, value)"
                                                    :disabled="!record.item_id"
                                                />
                                            </template>
                                            <template v-if="column.dataIndex === 'adjustment_quantity'">
                                                <a-input-number
                                                    v-model:value="record.adjustment_quantity"
                                                    style="width: 100%"
                                                    @change="(value) => calculateNewQuantity(index, value)"
                                                    :disabled="!record.item_id"
                                                />
                                            </template>
                                        </template>
                                    </a-table>
                                </div>
                                <div class="add-item-btn">
                                    <a-button type="dashed" block @click="addNewItem"
                                        :disabled="formData.adjustment_items.some(item => !item.item_id)">
                                        <PlusOutlined /> {{ $t('inventory_adjustment.add_item') }}
                                    </a-button>
                                </div>
                            </div>
                        </a-col>
                    </a-row>
                </a-col>
            </a-row>
        </a-form>

        <template #footer>
            <a-button
                type="primary"
                @click="onSubmit"
                :loading="loading"
                :disabled="formData.adjustment_items.length === 0"
            >
                <template #icon> <SaveOutlined /> </template>
                {{
                    addEditType == "add" ? $t("common.create") : $t("common.update")
                }}
            </a-button>
            <a-button style="margin-left: 8px" @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import {
    PlusOutlined,
    DeleteOutlined,
    SaveOutlined,
    ScanOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import ItemSelect from "../../../../common/components/common/select/ItemSelect.vue";
import SelectInput from "../../../../common/components/common/select/SelectInput.vue";
import { message } from "ant-design-vue";
import { useI18n } from "vue-i18n";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "adjustmentReasons",
        "itemColumns",
    ],
    components: {
        PlusOutlined,
        DeleteOutlined,
        SaveOutlined,
        ScanOutlined,
        DateTimePicker,
        ItemSelect,
        SelectInput,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { t } = useI18n();
        const scanInput = ref("");
        const scanInputRef = ref(null);
        const scanLoading = ref(false);
        const scanActive = ref(false);
        const scanTimeout = ref(null);

        watch(() => props.visible, (newVal) => {
            if (newVal) {
                // If it's edit mode, make sure to properly set the items
                if (props.addEditType === 'edit' && props.data && props.data.adjustment_items) {
                    props.formData.adjustment_items = [...props.data.adjustment_items];
                }
            }
        });

        const addNewItem = () => {
            props.formData.adjustment_items.push({
                item_id: undefined,
                current_quantity: 0,
                adjustment_quantity: 0,
                new_quantity: 0
            });
        };

        const removeItem = (index) => {
            props.formData.adjustment_items.splice(index, 1);
            
            // Refocus scanner input if scan mode is active
            if (scanActive.value) {
                setTimeout(() => {
                    if (scanInputRef.value) {
                        scanInputRef.value.focus();
                    }
                }, 100);
            }
        };

        const onItemSelected = (value, selectedItem, index) => {
            props.formData.adjustment_items[index].item_id = value;
            if (selectedItem) {
                props.formData.adjustment_items[index].current_quantity = selectedItem.available_quantity;
                props.formData.adjustment_items[index].adjustment_quantity = 0;
                props.formData.adjustment_items[index].new_quantity = selectedItem.available_quantity;
            } else {
                props.formData.adjustment_items[index].current_quantity = 0;
                props.formData.adjustment_items[index].adjustment_quantity = 0;
                props.formData.adjustment_items[index].new_quantity = 0;
            }
        };

        const calculateNewQuantity = (index, value) => {
            const currentItem = props.formData.adjustment_items[index];
            const adjustmentQuantity = value !== undefined ? value : currentItem.adjustment_quantity;
            currentItem.new_quantity = parseInt(currentItem.current_quantity || 0) + parseInt(adjustmentQuantity || 0);
        };

        const calculateAdjustmentQuantity = (index, value) => {
            const currentItem = props.formData.adjustment_items[index];
            currentItem.adjustment_quantity = parseInt(value || 0) - parseInt(currentItem.current_quantity || 0);
        };

        const toggleScanMode = () => {
            scanActive.value = !scanActive.value;
            
            if (scanActive.value) {
                if (scanInputRef.value) {
                    scanInputRef.value.focus();
                }
                message.info(t('items.scan_mode_active'));
            } else {
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
            if (scanTimeout.value) {
                clearTimeout(scanTimeout.value);
            }

            scanTimeout.value = setTimeout(() => {
                if (scanInput.value && scanInput.value.trim() !== "") {
                    processBarcodeScan();
                }
            }, 100);
        };

        const processBarcodeScan = async () => {
            if (!scanActive.value) return;
            
            const scannedSku = scanInput.value.trim();
            if (!scannedSku) return;

            scanLoading.value = true;
            
            try {
                const response = await axiosAdmin.get(`items/search-by-sku/${scannedSku}`);
                
                if (response.found && response.data) {
                    // Check if item is already in the table
                    const existingItem = props.formData.adjustment_items.find(
                        item => item.item_id === response.data.xid
                    );
                    
                    if (existingItem) {
                        message.warning(t('inventory_adjustment.item_already_added'));
                    } else {
                        // Add item to adjustment_items
                        props.formData.adjustment_items.push({
                            item_id: response.data.xid,
                            current_quantity: response.data.available_quantity,
                            adjustment_quantity: 0,
                            new_quantity: response.data.available_quantity
                        });
                        message.success(`${response.data.name} ${t('inventory_adjustment.added_successfully')}`);
                    }
                } else {
                    message.error(t('inventory_adjustment.item_not_found'));
                }
            } catch (error) {
                message.error(t('inventory_adjustment.error_scanning'));
                console.error("Error scanning item:", error);
            } finally {
                scanInput.value = "";
                scanLoading.value = false;
                
                if (scanActive.value && scanInputRef.value) {
                    scanInputRef.value.focus();
                }
            }
        };

        const validateItems = () => {
            if (props.formData.adjustment_items.length === 0) {
                rules.value = {
                    adjustment_items: {
                        message: "Please add at least one item"
                    }
                };
                return false;
            }

            for (const item of props.formData.adjustment_items) {
                if (!item.item_id) {
                    rules.value = {
                        adjustment_items: {
                            message: "Please select an item for all rows"
                        }
                    };
                    return false;
                }
            }

            return true;
        };

        const onSubmit = () => {
            if (!validateItems()) {
                return;
            }

            // Prepare the data for submission
            // Convert the items array for the backend
            const submissionData = {
                ...props.formData,
                adjustment_items: props.formData.adjustment_items.map(item => ({
                    item_id: item.item_id,
                    quantity: item.adjustment_quantity,
                    new_quantity: item.new_quantity,
                }))
            };

            addEditRequestAdmin({
                url: props.url + (props.addEditType === "add" ? "/store-adjustment" : `/update-adjustment`),
                data: {
                    ...submissionData,
                    _method: props.addEditType === "add" ? "POST" : "PUT",
                },
                successMessage: props.successMessage,
                success: (res) => {
                    if(res.success) {
                        emit("addEditSuccess", res.xid);
                    }
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        watch(
            () => props.visible,
            (newVal) => {
                if(newVal && props.addEditType == "edit" && props.formData) {
                    props.formData.adjustment_items = props.formData.adjustment_items.map((e, index) => {
                        const item = {
                            item_id: e.x_item_id,
                            adjustment_quantity: e.quantity_adjusted,
                            current_quantity: e.item.available_quantity - e.quantity_adjusted,
                        };
                        item.new_quantity = parseInt(item.current_quantity || 0) + parseInt(item.adjustment_quantity || 0);
                        return item;
                    });
                }
            }
        )

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            addNewItem,
            removeItem,
            onItemSelected,
            calculateNewQuantity,
            calculateAdjustmentQuantity,
            scanInput,
            scanInputRef,
            scanLoading,
            scanActive,
            toggleScanMode,
            handleScanInput,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "70%",
        };
    },
});
</script>

<style scoped>
.adjustment-items-table {
    margin-top: 20px;
}

.add-item-btn {
    margin-top: 10px;
}

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