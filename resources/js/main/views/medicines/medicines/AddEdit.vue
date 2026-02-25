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
                                :label="$t('common.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('common.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.category')"
                                name="category_id"
                                :help="
                                    rules.category_id
                                        ? rules.category_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.category_id ? 'error' : null
                                "
                            >
                                <SelectInput
                                    :value="formData.category_id"
                                    simple-form
                                    url="item-categories"
                                    :params="{type: 'medicine'}"
                                    :placeholder="$t('medicine.category')"
                                    :options="categories"
                                    @onChange="(value) => formData.category_id = value"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.brand')"
                                name="brand_id"
                                :help="
                                    rules.brand_id
                                        ? rules.brand_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.brand_id ? 'error' : null
                                "
                            >
                                <SelectInput
                                    :value="formData.brand_id"
                                    simple-form
                                    url="item-brands"
                                    :params="{type: 'medicine'}"
                                    :placeholder="$t('medicine.brand')"
                                    :options="brands"
                                    @onChange="(value) => formData.brand_id = value"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.salt_composition')"
                                name="salt_composition"
                                :help="
                                    rules.salt_composition
                                        ? rules.salt_composition.message
                                        : null
                                "
                                :validateStatus="
                                    rules.salt_composition ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.salt_composition"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('medicine.salt_composition'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.selling_price')"
                                name="selling_price"
                                :help="
                                    rules.selling_price
                                        ? rules.selling_price.message
                                        : null
                                "
                                :validateStatus="
                                    rules.selling_price ? 'error' : null
                                "
                                class="required"
                            >
                                <CurrencyInput
                                    :value="formData.selling_price"
                                    @inputNumberChanged="(value) =>
                                        (formData.selling_price = value)
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.buying_price')"
                                name="buying_price"
                                :help="
                                    rules.buying_price
                                        ? rules.buying_price.message
                                        : null
                                "
                                :validateStatus="
                                    rules.buying_price ? 'error' : null
                                "
                                class="required"
                            >
                                <CurrencyInput
                                    :value="formData.buying_price"
                                    @inputNumberChanged="(value) =>
                                        (formData.buying_price = value)
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.sku')"
                                name="sku"
                                :help="
                                    rules.sku
                                        ? rules.sku.message
                                        : null
                                "
                                :validateStatus="
                                    rules.sku ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.sku"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('medicine.sku'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('medicine.alert_quantity')"
                                name="alert_quantity"
                                :help="
                                    rules.alert_quantity
                                        ? rules.alert_quantity.message
                                        : null
                                "
                                :validateStatus="
                                    rules.alert_quantity ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input-number
                                    style="width: 100%"
                                    v-model:value="formData.alert_quantity"
                                    :min="0"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('medicine.alert_quantity'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('medicine.description')"
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
                                            $t('medicine.description'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 4, maxRows: 6 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('medicine.side_effects')"
                                name="side_effects"
                                :help="
                                    rules.side_effects
                                        ? rules.side_effects.message
                                        : null
                                "
                                :validateStatus="
                                    rules.side_effects ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.side_effects"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('medicine.side_effects'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 4, maxRows: 6 }"
                                />
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
import { defineComponent, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import CurrencyInput from "../../../../common/components/common/input/CurrencyInput.vue";
import SelectInput from "../../../../common/components/common/select/SelectInput.vue";
import apiAdmin from "../../../../common/composable/apiAdmin";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "categories",
        "brands",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        CurrencyInput,
        SelectInput,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        watch(
            () => props.visible,
            (newVal) => {
                if (newVal) {
                    console.log("props.formData", props.formData);
                    if (props.formData.item) {
                        const item = props.formData.item;
                        // Populate item fields
                        props.formData.name = item.name;
                        props.formData.sku = item.sku;
                        props.formData.description = item.description;
                        props.formData.category_id = item.x_category_id;
                        props.formData.brand_id = item.x_brand_id;
                        props.formData.selling_price = item.sale_price;
                        props.formData.buying_price = item.cost_price;
                        props.formData.alert_quantity = item.alert_quantity;
                        
                        // Populate medicine-specific fields from top level
                        if (props.formData.salt_composition !== undefined) {
                            // Keep the salt_composition that's already set
                        }
                        if (props.formData.side_effects !== undefined) {
                            // Keep the side_effects that's already set
                        }
                    }
                }
            }
        );

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
            drawerWidth: window.innerWidth <= 991 ? "90%" : "50%",
        };
    },
});
</script>