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
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('expenses.amount')"
                        name="amount"
                        :help="rules.amount ? rules.amount.message : null"
                        :validateStatus="rules.amount ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.amount"
                            :placeholder="$t('common.placeholder_default_text', [$t('expenses.amount')])"
                            type="number"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('expenses.category_id')"
                        name="category_id"
                        :help="rules.category_id ? rules.category_id.message : null"
                        :validateStatus="rules.category_id ? 'error' : null"
                        class="required"
                    >
                        <SelectInput
                            :value="formData.category_id"
                            simple-form
                            :options="expenseCategories"
                            url="expense-categories"
                            :placeholder="$t('expenses.category_id')"
                            @onChange="(id) => { formData.category_id = id; }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('expenses.expense_for')"
                        name="expense_for"
                        :help="rules.expense_for ? rules.expense_for.message : null"
                        :validateStatus="rules.expense_for ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.expense_for"
                            :placeholder="$t('common.placeholder_default_text', [$t('expenses.expense_for')])"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('expenses.payment_type')"
                        name="payment_type"
                        :help="rules.payment_type ? rules.payment_type.message : null"
                        :validateStatus="rules.payment_type ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.payment_type"
                            :placeholder="$t('common.select_default_text', [$t('expenses.payment_type')])"
                            style="width: 100%"
                        >
                            <a-select-option 
                                v-for="type in paymentTypes" 
                                :key="type.value" 
                                :value="type.value"
                            >
                                {{ type.label }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('expenses.reference_number')"
                        name="reference_number"
                        :help="rules.reference_number ? rules.reference_number.message : null"
                        :validateStatus="rules.reference_number ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.reference_number"
                            :placeholder="$t('common.placeholder_default_text', [$t('expenses.reference_number')])"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('expenses.date')"
                        name="date"
                        :help="rules.date ? rules.date.message : null"
                        :validateStatus="rules.date ? 'error' : null"
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
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('expenses.notes')"
                        name="notes"
                        :help="rules.notes ? rules.notes.message : null"
                        :validateStatus="rules.notes ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.notes"
                            :placeholder="$t('common.placeholder_default_text', [$t('expenses.notes')])"
                            :auto-size="{ minRows: 4, maxRows: 6 }"
                        />
                    </a-form-item>
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
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import fields from "./fields";
import SelectInput from "../../../../common/components/common/select/SelectInput.vue";

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
        DateTimePicker,
        SelectInput,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { expenseCategories, paymentTypes } = fields();

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

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "40%",
            expenseCategories,
            paymentTypes,
        };
    },
});
</script>
