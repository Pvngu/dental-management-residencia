<template>
    <a-drawer
        :title="$t('items.stock_adjustment')"
        :width="drawerWidth"
        :open="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <div v-if="itemData">
            <!-- Item Info Header -->
            <div style="background: #f5f5f5; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 8px 0;">{{ itemData.name }}</h3>
                <p style="margin: 0; color: #666;">
                    {{ itemData.sku ? `(${itemData.sku})` : '' }}
                </p>
            </div>

            <!-- Current Stock Display -->
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="color: #999; font-size: 14px; margin-bottom: 8px;">
                    {{ $t('items.current_stock') }}
                </div>
                <div style="font-size: 48px; font-weight: bold;">
                    {{ itemData.available_quantity }} 
                    <span style="font-size: 24px; color: #999;">{{ itemData.unit || 'pcs' }}</span>
                </div>
            </div>

            <!-- Quantity Input -->
            <a-form layout="vertical">
                <a-form-item 
                    :label="$t('items.quantity')"
                    :help="rules.quantity ? rules.quantity.message : null"
                    :validateStatus="rules.quantity ? 'error' : null"
                >
                    <a-input-number
                        v-model:value="formData.quantity"
                        :min="1"
                        style="width: 100%"
                        :placeholder="$t('common.placeholder_default_text', [$t('items.quantity')])"
                        size="large"
                    />
                </a-form-item>

                <a-form-item 
                    :label="$t('items.adjustment_reason')"
                    :help="rules.adjustments_reason_id ? rules.adjustments_reason_id.message : null"
                    :validateStatus="rules.adjustments_reason_id ? 'error' : null"
                >
                    <a-select
                        v-model:value="formData.adjustments_reason_id"
                        :placeholder="$t('common.select_default_text', [$t('items.adjustment_reason')])"
                        style="width: 100%"
                        size="large"
                        show-search
                        optionFilterProp="title"
                    >
                        <a-select-option
                            v-for="reason in adjustmentReasons"
                            :key="reason.xid"
                            :value="reason.xid"
                            :title="reason.name"
                        >
                            {{ reason.name }}
                        </a-select-option>
                    </a-select>
                </a-form-item>

                <a-form-item 
                    :label="$t('items.description')"
                    :help="rules.description ? rules.description.message : null"
                    :validateStatus="rules.description ? 'error' : null"
                >
                    <a-textarea
                        v-model:value="formData.description"
                        :placeholder="$t('common.placeholder_default_text', [$t('items.description')])"
                        :auto-size="{ minRows: 3, maxRows: 5 }"
                    />
                </a-form-item>
            </a-form>

            <!-- Action Buttons -->
            <a-row :gutter="16">
                <a-col :span="12">
                    <a-button
                        type="primary"
                        size="large"
                        block
                        @click="handleCheckIn"
                        :loading="loading"
                        style="height: 60px; background-color: #059669; border-color: #059669;"
                    >
                        <template #icon><PlusOutlined /></template>
                        <div>{{ $t("items.check_in") }}</div>
                    </a-button>
                </a-col>
                <a-col :span="12">
                    <a-button
                        type="primary"
                        size="large"
                        block
                        @click="handleCheckOut"
                        :loading="loading"
                        danger
                        style="height: 60px;"
                    >
                        <template #icon><MinusOutlined /></template>
                        <div>{{ $t("items.check_out") }}</div>
                    </a-button>
                </a-col>
            </a-row>

            <!-- Edit Item Button -->
            <a-divider />
            <a-button
                block
                @click="handleEditItem"
                v-if="permsArray.includes('items_edit') || permsArray.includes('admin')"
            >
                <template #icon><EditOutlined /></template>
                {{ $t("items.edit_item_details") }}
            </a-button>
        </div>
        
        <template #footer>
            <a-button @click="onClose">
                {{ $t("common.close") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, watch } from "vue";
import { PlusOutlined, MinusOutlined, EditOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: {
        visible: {
            type: Boolean,
            default: false,
        },
        itemData: {
            type: Object,
            default: null,
        },
        adjustmentReasons: {
            type: Array,
            default: () => [],
        },
    },
    components: {
        PlusOutlined,
        MinusOutlined,
        EditOutlined,
    },
    emits: ["closed", "adjustmentSuccess", "editItem"],
    setup(props, { emit }) {
        const { t } = useI18n();
        const { permsArray } = common();
        const loading = ref(false);
        const rules = ref({});
        
        const formData = ref({
            quantity: 1,
            adjustments_reason_id: undefined,
            description: "",
        });

        watch(() => props.visible, (newVal) => {
            if (newVal) {
                // Reset form when drawer opens
                formData.value = {
                    quantity: 1,
                    adjustments_reason_id: undefined,
                    description: "",
                };
                rules.value = {};
            }
        });

        const validateForm = () => {
            rules.value = {};
            let isValid = true;

            if (!formData.value.quantity || formData.value.quantity < 1) {
                rules.value.quantity = {
                    message: t("common.field_required", [t("items.quantity")]),
                };
                isValid = false;
            }

            return isValid;
        };

        const handleCheckIn = async () => {
            if (!validateForm()) {
                return;
            }

            loading.value = true;
            try {
                const response = await axiosAdmin.post("inventory-adjustments/quick-adjust", {
                    item_id: props.itemData.xid,
                    quantity: formData.value.quantity,
                    type: "check_in",
                    adjustments_reason_id: formData.value.adjustments_reason_id,
                    description: formData.value.description,
                });

                message.success(t("items.check_in_success"));
                emit("adjustmentSuccess", response);
                onClose();
            } catch (error) {
                if (error.response && error.response.data && error.response.data.errors) {
                    rules.value = error.response.data.errors;
                } else {
                    message.error(t("common.error_something_went_wrong"));
                }
            } finally {
                loading.value = false;
            }
        };

        const handleCheckOut = async () => {
            if (!validateForm()) {
                return;
            }

            // Check if we have enough stock
            if (formData.value.quantity > props.itemData.available_quantity) {
                message.error(t("items.insufficient_stock"));
                return;
            }

            loading.value = true;
            try {
                const response = await axiosAdmin.post("inventory-adjustments/quick-adjust", {
                    item_id: props.itemData.xid,
                    quantity: formData.value.quantity,
                    type: "check_out",
                    adjustments_reason_id: formData.value.adjustments_reason_id,
                    description: formData.value.description,
                });

                message.success(t("items.check_out_success"));
                emit("adjustmentSuccess", response);
                onClose();
            } catch (error) {
                if (error.response && error.response.data && error.response.data.errors) {
                    rules.value = error.response.data.errors;
                } else {
                    message.error(t("common.error_something_went_wrong"));
                }
            } finally {
                loading.value = false;
            }
        };

        const handleEditItem = () => {
            emit("editItem", props.itemData);
        };

        const onClose = () => {
            rules.value = {};
            formData.value = {
                quantity: 1,
                adjustments_reason_id: undefined,
                description: "",
            };
            emit("closed");
        };

        return {
            formData,
            loading,
            rules,
            handleCheckIn,
            handleCheckOut,
            handleEditItem,
            onClose,
            permsArray,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "40%",
        };
    },
});
</script>
