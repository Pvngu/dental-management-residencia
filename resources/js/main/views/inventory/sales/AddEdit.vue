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
                                :label="$t('sales.patient')"
                                name="patient_id"
                                :help="
                                    rules.patient_id
                                        ? rules.patient_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.patient_id ? 'error' : null
                                "
                                class="required"
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.patient_id"
                                    show-search
                                    optionFilterProp="title"
                                    :placeholder="$t('common.select_default_text', [$t('sales.patient')])"
                                >
                                    <a-select-option
                                        v-for="patient in patients"
                                        :key="patient.xid"
                                        :title="patient.user ? patient.user.name : ''"
                                        :value="patient.xid"
                                    >
                                        {{ patient.user ? patient.user.name : '' }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('sales.sale_number')"
                                name="sale_number"
                                :help="
                                    rules.sale_number
                                        ? rules.sale_number.message
                                        : null
                                "
                                :validateStatus="
                                    rules.sale_number ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.sale_number"
                                    :placeholder="$t('common.placeholder_default_text', [$t('sales.sale_number')])"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('sales.sold_at')"
                                name="sold_at"
                                :help="
                                    rules.sold_at
                                        ? rules.sold_at.message
                                        : null
                                "
                                :validateStatus="
                                    rules.sold_at ? 'error' : null
                                "
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.sold_at"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.sold_at = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('sales.status')"
                                name="status"
                                :help="
                                    rules.status
                                        ? rules.status.message
                                        : null
                                "
                                :validateStatus="
                                    rules.status ? 'error' : null
                                "
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.status"
                                    :placeholder="$t('common.select_default_text', [$t('sales.status')])"
                                >
                                    <a-select-option value="pending">
                                        {{ $t('sales.pending') }}
                                    </a-select-option>
                                    <a-select-option value="paid">
                                        {{ $t('sales.paid') }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('sales.sale_details')"
                                name="sale_details"
                            >
                                <div class="invoice-details-wrapper">
                                    <a-table
                                        :columns="columns"
                                        :data-source="formData.sale_details || []"
                                        bordered
                                        :pagination="false"
                                    >
                                        <template #bodyCell="{ column, record, index }">
                                            <template v-if="column.dataIndex === 'action'">
                                                <a-button
                                                    type="primary"
                                                    danger
                                                    @click="removeItem(index)"
                                                >
                                                    <DeleteOutlined />
                                                </a-button>
                                            </template>
                                        </template>
                                    </a-table>
                                    <div class="invoice-summary mt-5">
                                        <div class="invoice-summary-item">
                                            <span class="label">{{ $t('sales.subtotal') }}:</span>
                                            <span class="value">${{ (formData.subtotal || 0).toFixed(2) }}</span>
                                        </div>
                                        <div class="invoice-summary-item">
                                            <span class="label">{{ $t('sales.tax') }} (5%):</span>
                                            <span class="value">${{ (formData.tax || 0).toFixed(2) }}</span>
                                        </div>
                                        <div class="invoice-summary-item">
                                            <span class="label">{{ $t('sales.discount') }}:</span>
                                            <a-input-number 
                                                v-model:value="formData.discount" 
                                                @change="calculateTotals"
                                                :min="0"
                                                style="width: 100px"
                                            />
                                        </div>
                                        <div class="invoice-summary-item total">
                                            <span class="label">{{ $t('sales.total') }}:</span>
                                            <span class="value">${{ (formData.total || 0).toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </a-form-item>
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
                    addEditType == "add" ? $t("sales.create") : $t("sales.update")
                }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, watch } from "vue";
import {
    SaveOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import fields from "./fields";

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
        SaveOutlined,
        DeleteOutlined,
        DateTimePicker,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { patients } = fields();

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const removeItem = (index) => {
            if (props.formData.sale_details && Array.isArray(props.formData.sale_details)) {
                props.formData.sale_details.splice(index, 1);
                calculateTotals();
            }
        };

        const calculateTotals = () => {
            let subtotal = 0;
            
            if (props.formData.sale_details && Array.isArray(props.formData.sale_details)) {
                props.formData.sale_details.forEach(item => {
                    subtotal += parseFloat(item.subtotal || 0);
                });
            }
            
            props.formData.subtotal = subtotal;
            props.formData.tax = subtotal * 0.05;
            props.formData.total = subtotal + props.formData.tax - (parseFloat(props.formData.discount) || 0);
        };

        watch(
            () => props.formData.sale_details,
            () => {
                calculateTotals();
            },
            { deep: true }
        );

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            patients,
            loading,
            rules,
            onClose,
            onSubmit,
            removeItem,
            calculateTotals,
            columns: [
                { title: "Product Name", dataIndex: "product_name" },
                { title: "Price", dataIndex: "price_at_time" },
                { title: "Quantity", dataIndex: "quantity" },
                { title: "Subtotal", dataIndex: "subtotal" },
                { title: "Action", dataIndex: "action" },
            ],
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
        };
    },
});
</script>

<style scoped>
.invoice-details-wrapper {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
}

.invoice-summary {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.invoice-summary-item {
    display: flex;
    justify-content: space-between;
    width: 300px;
    margin-bottom: 8px;
}

.invoice-summary-item.total {
    font-weight: bold;
    font-size: 16px;
    border-top: 1px solid #d9d9d9;
    padding-top: 8px;
    margin-top: 8px;
}
</style>
