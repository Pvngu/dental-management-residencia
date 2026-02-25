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
                    <!-- Invoice Information Section -->
                    <a-divider orientation="left">
                        {{ $t('invoices.invoice_information') }}
                    </a-divider>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.invoice_number')"
                                name="invoice_number"
                                :help="rules.invoice_number ? rules.invoice_number.message : null"
                                :validateStatus="rules.invoice_number ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.invoice_number"
                                    :placeholder="$t('common.placeholder_default_text', [$t('invoices.invoice_number')])"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.status')"
                                name="status"
                                :help="rules.status ? rules.status.message : null"
                                :validateStatus="rules.status ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.status"
                                    show-search
                                    optionFilterProp="label"
                                    :placeholder="$t('common.select_default_text', [$t('invoices.status')])"
                                >
                                    <a-select-option
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :label="option.label"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.date_of_issue')"
                                name="date_of_issue"
                                :help="rules.date_of_issue ? rules.date_of_issue.message : null"
                                :validateStatus="rules.date_of_issue ? 'error' : null"
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.date_of_issue"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.date_of_issue = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.payment_due_on')"
                                name="payment_due_on"
                                :help="rules.payment_due_on ? rules.payment_due_on.message : null"
                                :validateStatus="rules.payment_due_on ? 'error' : null"
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.payment_due_on"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.payment_due_on = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- Patient Information Section -->
                    <a-divider orientation="left">
                        {{ $t('invoices.patient_information') }}
                    </a-divider>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.patient')"
                                name="patient_id"
                                :help="rules.patient_id ? rules.patient_id.message : null"
                                :validateStatus="rules.patient_id ? 'error' : null"
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.patient_id"
                                    show-search
                                    optionFilterProp="title"
                                    :placeholder="$t('common.select_default_text', [$t('invoices.patient')])"
                                    @change="onPatientChange"
                                >
                                    <a-select-option
                                        v-for="patient in patients"
                                        :key="patient.xid"
                                        :title="patient.name"
                                        :value="patient.xid"
                                    >
                                        {{ patient.name }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.person_name')"
                                name="person_name"
                                :help="rules.person_name ? rules.person_name.message : null"
                                :validateStatus="rules.person_name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.person_name"
                                    :placeholder="$t('common.placeholder_default_text', [$t('invoices.person_name')])"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="$t('common.placeholder_default_text', [$t('invoices.email')])"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('invoices.phone_number')"
                                name="phone_number"
                                :help="rules.phone_number ? rules.phone_number.message : null"
                                :validateStatus="rules.phone_number ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.phone_number"
                                    :placeholder="$t('common.placeholder_default_text', [$t('invoices.phone_number')])"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('invoices.company_name')"
                                name="company_name"
                                :help="rules.company_name ? rules.company_name.message : null"
                                :validateStatus="rules.company_name ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.company_name"
                                    :placeholder="$t('common.placeholder_default_text', [$t('invoices.company_name')])"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- Invoice Items Section -->
                    <a-divider orientation="left">
                        {{ $t('invoices.invoice_items') }}
                    </a-divider>
                    
                    <div class="invoice-items">
                        <a-table
                            :columns="invoiceItemColumns"
                            :data-source="formData.invoice_details"
                            :pagination="false"
                            size="middle"
                            bordered
                            :rowKey="(record) => record.id || Math.random().toString(36).substr(2, 9)"
                        >
                            <template #bodyCell="{ column, record, index }">
                                <template v-if="column.dataIndex === 'product_name'">
                                    <a-input
                                        v-model:value="record.product_name"
                                        :placeholder="$t('common.placeholder_default_text', [$t('invoices.product_name')])"
                                        @change="updateTotals"
                                    />
                                </template>
                                <template v-if="column.dataIndex === 'quantity'">
                                    <a-input-number
                                        v-model:value="record.quantity"
                                        :min="1"
                                        style="width: 100%"
                                        @change="() => updateItemTotal(index)"
                                    />
                                </template>
                                <template v-if="column.dataIndex === 'price_at_time'">
                                    <a-input-number
                                        v-model:value="record.price_at_time"
                                        :min="0"
                                        :precision="2"
                                        style="width: 100%"
                                        @change="() => updateItemTotal(index)"
                                    />
                                </template>
                                <template v-if="column.dataIndex === 'total'">
                                    {{ record.total ? formatCurrency(record.total) : formatCurrency(0) }}
                                </template>
                                <template v-if="column.dataIndex === 'action'">
                                    <a-button
                                        type="danger"
                                        size="small"
                                        @click="removeInvoiceItem(index)"
                                    >
                                        <template #icon><DeleteOutlined /></template>
                                    </a-button>
                                </template>
                            </template>
                        </a-table>

                        <a-button 
                            type="dashed" 
                            style="width: 100%; margin-top: 16px" 
                            @click="addInvoiceItem"
                        >
                            <PlusOutlined /> {{ $t('invoices.add_item') }}
                        </a-button>
                    </div>

                    <!-- Summary Section -->
                    <a-divider orientation="left">
                        {{ $t('invoices.summary') }}
                    </a-divider>
                    <div class="invoice-summary">
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12" :offset="12">
                                <div class="summary-row">
                                    <span>{{ $t('invoices.subtotal') }}:</span>
                                    <span>{{ formatCurrency(formData.subtotal) }}</span>
                                </div>
                                <a-row :gutter="16">
                                    <a-col :span="12">
                                        <a-form-item
                                            :label="$t('invoices.tax')"
                                            name="tax"
                                        >
                                            <a-input-number
                                                v-model:value="formData.tax"
                                                :min="0"
                                                :precision="2"
                                                style="width: 100%"
                                                @change="updateTotals"
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col :span="12">
                                        <a-form-item
                                            :label="$t('invoices.discount')"
                                            name="discount"
                                        >
                                            <a-input-number
                                                v-model:value="formData.discount"
                                                :min="0"
                                                :max="formData.subtotal"
                                                :precision="2"
                                                style="width: 100%"
                                                @change="updateTotals"
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <div class="summary-row total">
                                    <span>{{ $t('invoices.total_payable') }}:</span>
                                    <span>{{ formatCurrency(formData.total_payable) }}</span>
                                </div>
                            </a-col>
                        </a-row>
                    </div>
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
import { defineComponent, ref, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import fields from "./fields";
import DateTimePicker from "../../../common/components/common/calendar/DateTimePicker.vue";

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
        DateTimePicker,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { statusOptions, patients } = fields();

        // Invoice item columns for the table
        const invoiceItemColumns = [
            {
                title: "Product/Service",
                dataIndex: "product_name",
                width: "40%",
            },
            {
                title: "Quantity",
                dataIndex: "quantity",
                width: "15%",
            },
            {
                title: "Price",
                dataIndex: "price_at_time",
                width: "20%",
            },
            {
                title: "Total",
                dataIndex: "total",
                width: "20%",
            },
            {
                title: "Action",
                dataIndex: "action",
                width: "5%",
            },
        ];

        // Initialize form or populate with existing data
        if (props.addEditType === "add" && (!props.formData.invoice_details || props.formData.invoice_details.length === 0)) {
            props.formData.invoice_details = [getEmptyInvoiceItem()];
        }

        // Function to get an empty invoice item
        function getEmptyInvoiceItem() {
            return {
                id: Math.random().toString(36).substr(2, 9),
                product_name: "",
                quantity: 1,
                price_at_time: 0,
                total: 0,
            };
        }

        // Add a new invoice item to the list
        const addInvoiceItem = () => {
            props.formData.invoice_details.push(getEmptyInvoiceItem());
        };

        // Remove an invoice item from the list
        const removeInvoiceItem = (index) => {
            props.formData.invoice_details.splice(index, 1);
            updateTotals();
        };

        // Update item total when quantity or price changes
        const updateItemTotal = (index) => {
            const item = props.formData.invoice_details[index];
            item.total = (item.quantity || 0) * (item.price_at_time || 0);
            updateTotals();
        };

        // Update all totals
        const updateTotals = () => {
            let subtotal = 0;
            
            // Calculate subtotal from all items
            props.formData.invoice_details.forEach((item) => {
                item.total = (item.quantity || 0) * (item.price_at_time || 0);
                subtotal += item.total || 0;
            });
            
            props.formData.subtotal = subtotal;
            
            // Calculate final total
            props.formData.total_payable = 
                (props.formData.subtotal || 0) + 
                (props.formData.tax || 0) - 
                (props.formData.discount || 0);
        };

        // Format currency values
        const formatCurrency = (value) => {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(value || 0);
        };

        // Handle patient selection
        const onPatientChange = (patientId) => {
            if (patientId) {
                const patient = patients.value.find(p => p.xid === patientId);
                if (patient) {
                    props.formData.person_name = patient.name;
                    props.formData.email = patient.email || '';
                    props.formData.phone_number = patient.phone || '';
                }
            }
        };

        // Submit form
        const onSubmit = () => {
            // Ensure there's at least one invoice detail
            if (!props.formData.invoice_details || props.formData.invoice_details.length === 0) {
                props.formData.invoice_details = [getEmptyInvoiceItem()];
            }
            
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        // Close form
        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            invoiceItemColumns,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "80%",
            addInvoiceItem,
            removeInvoiceItem,
            updateItemTotal,
            updateTotals,
            formatCurrency,
            statusOptions,
            patients,
            onPatientChange
        };
    },
});
</script>

<style scoped>
.invoice-items {
    margin-bottom: 24px;
}

.invoice-summary {
    margin-top: 24px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-row.total {
    font-weight: bold;
    font-size: 16px;
    border-top: 1px solid #d9d9d9;
    margin-top: 8px;
    padding-top: 16px;
}
</style>
