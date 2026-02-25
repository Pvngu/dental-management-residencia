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
                                :label="$t('purchase_medicine.reference_no')"
                                name="reference_no"
                                :help="
                                    rules.reference_no
                                        ? rules.reference_no.message
                                        : null
                                "
                                :validateStatus="
                                    rules.reference_no ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.reference_no"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('purchase_medicine.reference_no'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('purchase_medicine.delivery_date')"
                                name="delivery_date"
                                :help="
                                    rules.delivery_date
                                        ? rules.delivery_date.message
                                        : null
                                "
                                :validateStatus="
                                    rules.delivery_date ? 'error' : null
                                "
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.delivery_date"
                                    :isFutureDateDisabled="false"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.delivery_date = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('purchase_medicine.payment_type')"
                                name="payment_type"
                                :help="
                                    rules.payment_type
                                        ? rules.payment_type.message
                                        : null
                                "
                                :validateStatus="
                                    rules.payment_type ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.payment_type"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('purchase_medicine.payment_type'),
                                        ])
                                    "
                                    style="width: 100%"
                                >
                                    <a-select-option
                                        v-for="paymentType in paymentTypes"
                                        :key="paymentType.key"
                                        :value="paymentType.key"
                                    >
                                        {{ paymentType.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('purchase_medicine.payment_status')"
                                name="payment_status"
                                :help="
                                    rules.payment_status
                                        ? rules.payment_status.message
                                        : null
                                "
                                :validateStatus="
                                    rules.payment_status ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.payment_status"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('purchase_medicine.payment_status'),
                                        ])
                                    "
                                    style="width: 100%"
                                >
                                    <a-select-option
                                        v-for="status in paymentStatuses"
                                        :key="status.key"
                                        :value="status.key"
                                    >
                                        {{ status.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <div class="medicine-items-table">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h3 style="margin: 0;">{{ $t('purchase_medicine.medicine_details') }}</h3>
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

                        <a-row :gutter="16" v-if="rules.order_medicines" class="mb-15">
                            <a-col :span="24">
                                <a-alert type="error" :message="rules.order_medicines.message" />
                            </a-col>
                        </a-row>

                        <div class="table-responsive">
                            <a-table
                                :columns="medicineColumns"
                                :dataSource="formData.order_medicines"
                                :pagination="false"
                                bordered
                                size="small"
                            >
                                <template #bodyCell="{ column, record, index }">
                                    <template v-if="column.dataIndex === 'medicine_id'">
                                        <ItemSelect
                                            :value="record.x_medicine_id"
                                            :placeholder="
                                                $t('common.select_default_text', [
                                                    $t('purchase_medicine.medicine'),
                                                ])
                                            "
                                            apiUrl="medicines"
                                            apiParams="fields=id,xid,salt_composition,side_effects,item{xid,name,sku,cost_price}&limit=1000"
                                            @onChange="(value, selectedMedicine) => handleMedicineChange(value, selectedMedicine, index)"
                                        />
                                    </template>
                                    <template v-if="column.dataIndex === 'lot_no'">
                                        <a-input v-model:value="record.lot_no" />
                                    </template>
                                    <template v-if="column.dataIndex === 'expiry_date'">
                                        <DateTimePicker
                                            :dateTime="record.expiry_date"
                                            :isFutureDateDisabled="false"
                                            :showTime="false"
                                            :onlyDate="true"
                                            @dateTimeChanged="(changedDateTime) => { record.expiry_date = changedDateTime; }"
                                        />
                                    </template>
                                    <template v-if="column.dataIndex === 'quantity'">
                                        <a-input-number
                                            v-model:value="record.quantity"
                                            :min="1"
                                            style="width: 100%"
                                            @change="() => calculateAmount(index)"
                                        />
                                    </template>
                                    <template v-if="column.dataIndex === 'rate'">
                                        <a-input-number
                                            v-model:value="record.rate"
                                            :min="0"
                                            :step="0.01"
                                            style="width: 100%"
                                            @change="() => calculateAmount(index)"
                                        />
                                    </template>
                                    <template v-if="column.dataIndex === 'amount'">
                                        <a-input-number
                                            v-model:value="record.amount"
                                            :min="0"
                                            :step="0.01"
                                            style="width: 100%"
                                            :disabled="true"
                                        />
                                    </template>
                                    <template v-if="column.dataIndex === 'action'">
                                        <a-button
                                            type="primary"
                                            danger
                                            @click="removeMedicineRow(index)"
                                        >
                                            <DeleteOutlined />
                                        </a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>

                        <div class="add-item-btn" style="margin-top:10px;">
                            <a-button type="dashed" block @click="addMedicineRow">
                                <template #icon>
                                    <PlusOutlined />
                                </template>
                                {{ $t('purchase_medicine.add_medicine') }}
                            </a-button>
                        </div>
                    </div>

                    <a-divider orientation="left">
                        {{ $t('purchase_medicine.payment_details') }}
                    </a-divider>

                    <a-row :gutter="16">
                        <!-- Left column: Note -->
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('purchase_medicine.note')"
                                name="note"
                                :help="rules.note ? rules.note.message : null"
                                :validateStatus="rules.note ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.note"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('purchase_medicine.note'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 3, maxRows: 5 }"
                                />
                            </a-form-item>
                        </a-col>
                        <!-- Right column: Subtotal, Discount, Tax, Adjustments, Total -->
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-card>
                                <a-form-item
                                    :label="$t('purchase_medicine.subtotal')"
                                    name="subtotal"
                                >
                                    <a-input-number
                                        v-model:value="formData.subtotal"
                                        :min="0"
                                        :step="0.01"
                                        :disabled="true"
                                        style="width: 100%"
                                    />
                                </a-form-item>
                                <a-form-item
                                    :label="$t('purchase_medicine.discount')"
                                    name="discount"
                                >
                                    <a-input-number
                                        v-model:value="formData.discount"
                                        :min="0"
                                        :step="0.01"
                                        style="width: 100%"
                                        @change="calculateTotal"
                                    >
                                        <template #addonAfter>
                                            <a-select
                                                v-model:value="formData.discount_type"
                                                :placeholder="
                                                    $t('common.select_default_text', [
                                                        $t('purchase_medicine.discount_type'),
                                                    ])
                                                "
                                                @change="calculateTotal"
                                                defaultValue="percentage"
                                            >
                                                <a-select-option value="percentage">
                                                    %
                                                </a-select-option>
                                                <a-select-option value="fixed">
                                                    $
                                                </a-select-option>
                                            </a-select>
                                        </template>
                                    </a-input-number>
                                </a-form-item>
                                <a-form-item
                                    :label="$t('purchase_medicine.tax')"
                                    name="tax"
                                >
                                    <a-input-number
                                        v-model:value="formData.tax"
                                        :min="0"
                                        :step="0.01"
                                        style="width: 100%"
                                        @change="calculateTotal"
                                    >
                                        <template #addonAfter>
                                            <a-select
                                                v-model:value="formData.tax_type"
                                                :placeholder="
                                                    $t('common.select_default_text', [
                                                        $t('purchase_medicine.tax_type'),
                                                    ])
                                                "
                                                @change="calculateTotal"
                                                defaultValue="percentage"
                                            >
                                                <a-select-option value="percentage">
                                                    %
                                                </a-select-option>
                                                <a-select-option value="fixed">
                                                    $
                                                </a-select-option>
                                            </a-select>
                                        </template>
                                    </a-input-number>
                                </a-form-item>
                                <a-form-item
                                    :label="$t('purchase_medicine.adjustments')"
                                    name="adjustments"
                                >
                                    <a-input-number
                                        v-model:value="formData.adjustments"
                                        :min="0"
                                        :step="0.01"
                                        style="width: 100%"
                                        @change="calculateTotal"
                                    />
                                </a-form-item>
                                <a-divider />
                                <a-row justify="space-between" align="middle">
                                    <a-col>
                                        <a-typography-title :level="5">
                                            {{ $t('purchase_medicine.total') }}
                                        </a-typography-title>
                                    </a-col>
                                    <a-col>
                                        <a-typography-title :level="5">
                                            {{ formatAmountCurrency(formData.total) }}
                                        </a-typography-title>
                                    </a-col>
                                </a-row>
                            </a-card>
                        </a-col>
                    </a-row>
                </a-col>
            </a-row>
        </a-form>
        
        <template #footer>
            <a-button
                type="primary"
                @click="onSubmit"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{
                    addEditType == "add" ? $t("common.create") : $t("common.update")
                }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, watch, onMounted, ref } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    DeleteOutlined,
    ScanOutlined,
} from "@ant-design/icons-vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import ItemSelect from "../../../../common/components/common/select/ItemSelect.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import fields from "./fields";
import { useI18n } from "vue-i18n";
import common from "../../../../common/composable/common";
import { message } from "ant-design-vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        DeleteOutlined,
        ScanOutlined,
        DateTimePicker,
        ItemSelect,
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { formatAmountCurrency } = common();
        const {
            paymentTypes,
            paymentStatuses,
            medicines,
            getPrefetchData,
            medicineColumns,
        } = fields();
        
        const scanInput = ref("");
        const scanInputRef = ref(null);
        const scanLoading = ref(false);
        const scanActive = ref(false);
        const scanTimeout = ref(null);

        onMounted(() => {
            getPrefetchData();
        });

        watch(
            () => props.visible,
            (newVal) => {
                if (newVal && props.addEditType === "edit") {
                    if (props.data.order_medicines && props.data.order_medicines.length > 0) {
                        calculateSubtotal();
                        calculateTotal();
                    }
                }
            }
        );

        const addMedicineRow = () => {
            if (!props.formData.order_medicines) {
                props.formData.order_medicines = [];
            }
            
            props.formData.order_medicines.push({
               x_medicine_id: undefined,
                lot_no: "",
                expiry_date: "",
                quantity: 1,
                rate: 0,
                amount: 0,
            });
        };

        const removeMedicineRow = (index) => {
            props.formData.order_medicines.splice(index, 1);
            calculateSubtotal();
            calculateTotal();
            
            // Refocus scanner input if scan mode is active
            if (scanActive.value) {
                setTimeout(() => {
                    if (scanInputRef.value) {
                        scanInputRef.value.focus();
                    }
                }, 100);
            }
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
                // Search for medicine directly by SKU
                const response = await axiosAdmin.get(`medicines/search-by-sku/${scannedSku}`);
                
                if (response.found && response.data) {
                    const medicine = response.data;
                    
                    // Check if medicine is already in the table
                    const existingMedicine = props.formData.order_medicines?.find(
                        item => item.x_medicine_id === medicine.xid
                    );
                    
                    if (existingMedicine) {
                        message.warning(t('purchase_medicine.medicine_already_added'));
                    } else {
                        // Add medicine to order_medicines
                        if (!props.formData.order_medicines) {
                            props.formData.order_medicines = [];
                        }
                        
                        props.formData.order_medicines.push({
                            x_medicine_id: medicine.xid,
                            lot_no: "",
                            expiry_date: "",
                            quantity: 1,
                            rate: parseFloat(medicine.item?.cost_price || 0),
                            amount: parseFloat(medicine.item?.cost_price || 0),
                        });
                        
                        calculateSubtotal();
                        calculateTotal();
                        message.success(`${medicine.item?.name || 'Medicine'} ${t('purchase_medicine.added_successfully')}`);
                    }
                } else {
                    message.error(t('purchase_medicine.medicine_not_found'));
                }
            } catch (error) {
                message.error(t('purchase_medicine.error_scanning'));
                console.error("Error scanning medicine:", error);
            } finally {
                scanInput.value = "";
                scanLoading.value = false;
                
                if (scanActive.value && scanInputRef.value) {
                    scanInputRef.value.focus();
                }
            }
        };

        const handleMedicineChange = (value, selectedMedicine, index) => {
            // Set the medicine ID
            props.formData.order_medicines[index].x_medicine_id = value;
            
            if (selectedMedicine) {
                props.formData.order_medicines[index].rate = parseFloat(selectedMedicine.item?.cost_price || selectedMedicine.buying_price || 0);
                calculateAmount(index);
            }
        };

        const calculateAmount = (index) => {
            const medicine = props.formData.order_medicines[index];
            if (medicine.quantity && medicine.rate) {
                medicine.amount = parseFloat((medicine.quantity * medicine.rate).toFixed(2));
                calculateSubtotal();
                calculateTotal();
            }
        };

        const calculateSubtotal = () => {
            let subtotal = 0;
            if (props.formData.order_medicines && props.formData.order_medicines.length > 0) {
                props.formData.order_medicines.forEach(medicine => {
                    subtotal += parseFloat(medicine.amount || 0);
                });
            }
            props.formData.subtotal = parseFloat(subtotal.toFixed(2));
        };

        const calculateTotal = () => {
            let total = parseFloat(props.formData.subtotal || 0);
            
            // Subtract discount
            if(props.formData.discount_type === "percentage") {
                total -= (total * (parseFloat(props.formData.discount || 0) / 100));
            } else {
                total -= parseFloat(props.formData.discount || 0);
            }
            
            // Add tax
            if(props.formData.tax_type === "percentage") {
                total += (total * (parseFloat(props.formData.tax || 0) / 100));
            } else {
                total += parseFloat(props.formData.tax || 0);
            }
            
            // Add adjustments
            total += parseFloat(props.formData.adjustments || 0);
            
            props.formData.total = parseFloat(total.toFixed(2));
        };

        const onSubmit = () => {
            if (!props.formData.order_medicines || props.formData.order_medicines.length === 0) {
                rules.value = {
                    order_medicines: {
                        message: t('purchase_medicine.please_add_at_least_one_medicine'),
                    },
                };
                return;
            }

            // Validate each medicine row
            for (let i = 0; i < props.formData.order_medicines.length; i++) {
                const medicine = props.formData.order_medicines[i];
                
                if (!medicine.x_medicine_id || medicine.x_medicine_id === undefined || medicine.x_medicine_id === null || medicine.x_medicine_id === '') {
                    rules.value = {
                        order_medicines: {
                            message: t('purchase_medicine.please_select_medicine_for_all_rows') + ` (Row ${i + 1})`,
                        },
                    };
                    return;
                }
                if (!medicine.expiry_date || medicine.expiry_date === undefined || medicine.expiry_date === null || medicine.expiry_date === '') {
                    rules.value = {
                        order_medicines: {
                            message: t('purchase_medicine.please_provide_expiry_date_for_all_medicines') + ` (Row ${i + 1})`,
                        },
                    };
                    return;
                }
                if (!medicine.lot_no || medicine.lot_no.trim() === '') {
                    rules.value = {
                        order_medicines: {
                            message: t('purchase_medicine.please_provide_lot_number_for_all_medicines') + ` (Row ${i + 1})`,
                        },
                    };
                    return;
                }
            }

            // All validation passed, submit the form
            addEditRequestAdmin({
                url: props.url + (props.addEditType === "edit" ? "/update" : "/store"),
                data: {
                    ...props.formData,
                    _method: props.addEditType === "edit" ? "PUT" : "POST",
                },
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "95%" : "70%",
            medicineColumns,
            paymentTypes,
            paymentStatuses,
            medicines,
            addMedicineRow,
            removeMedicineRow,
            handleMedicineChange,
            calculateAmount,
            calculateSubtotal,
            calculateTotal,
            formatAmountCurrency,
            scanInput,
            scanInputRef,
            scanLoading,
            scanActive,
            toggleScanMode,
            handleScanInput,
        };
    },
});
</script>

<style scoped>
.mb-15 {
    margin-bottom: 15px;
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

.medicine-items-table {
    margin-top: 20px;
}

.add-item-btn {
    margin-top: 10px;
}
</style>