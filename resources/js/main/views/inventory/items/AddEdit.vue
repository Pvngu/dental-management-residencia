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
                        <!-- Left: main fields (larger) -->
                        <a-col :xs="24" :sm="24" :md="18" :lg="18">
                            <a-row :gutter="16">
                                <!-- Type above name to match design -->
                                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                                    <a-form-item
                                        :label="$t('items.type')"
                                        name="type"
                                        :help="rules.type ? rules.type.message : null"
                                        :validateStatus="rules.type ? 'error' : null"
                                        class="required"
                                    >
                                        <a-select
                                            v-model:value="formData.type"
                                            style="width: 100%"
                                            :placeholder="$t('common.select_default_text', [$t('items.type')])"
                                        >
                                            <a-select-option value="goods">{{ $t('items.type_goods') }}</a-select-option>
                                            <a-select-option value="service">{{ $t('items.type_service') }}</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>

                                <a-col :xs="24" :sm="24" :md="18" :lg="18">
                                    <a-form-item
                                        :label="$t('common.name')"
                                        name="name"
                                        :help="rules.name ? rules.name.message : null"
                                        :validateStatus="rules.name ? 'error' : null"
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.name"
                                            :placeholder="$t('common.placeholder_default_text', [$t('common.name')])"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>

                            <!-- SKU + Unit + Returnable on one row -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        name="sku"
                                        :help="rules.sku ? rules.sku.message : null"
                                        :validateStatus="rules.sku ? 'error' : null"
                                    >
                                        <template #label>
                                            <a-tooltip :title="$t('items.sku_tooltip')">
                                                <span class="mr-1">{{ $t('items.sku') }}</span>
                                                <InfoCircleOutlined />
                                            </a-tooltip>
                                        </template>
                                        <a-input
                                            v-model:value="formData.sku"
                                            :placeholder="$t('common.placeholder_default_text', [$t('items.sku')])"
                                        />
                                    </a-form-item>
                                </a-col>

                                <a-col :xs="24" :sm="24" :md="8" :lg="8">
                                    <a-form-item
                                        :label="$t('items.unit')"
                                        name="unit"
                                        :help="rules.unit ? rules.unit.message : null"
                                        :validateStatus="rules.unit ? 'error' : null"
                                        class="required"
                                    >
                                        <a-select
                                            v-model:value="formData.unit"
                                            style="width: 100%"
                                            :placeholder="$t('common.select_default_text', [$t('items.unit')])"
                                        >
                                            <a-select-option
                                                v-for="u in units"
                                                :key="u.value"
                                                :value="u.value"
                                            >
                                                {{ u.label }}
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>

                                <a-col :xs="24" :sm="24" :md="4" :lg="4" style="display:flex;align-items:center;">
                                        <a-checkbox v-model:checked="formData.returnable">
                                            {{ $t('items.returnable') || 'Artículo retornable' }}
                                        </a-checkbox>
                                </a-col>
                            </a-row>
                        
                            <!-- Category row (moved down) -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('items.item_category')"
                                        name="category_id"
                                        :help="rules.category_id ? rules.category_id.message : null"
                                        :validateStatus="rules.category_id ? 'error' : null"
                                    >
                                        <SelectInput
                                            :value="formData.category_id"
                                            simple-form
                                            show-status
                                            url="item-categories"
                                            :placeholder="$t('items.item_category')"
                                            :options="categories"
                                            @onChange="(value) => formData.category_id = value"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>

                        <!-- Right: image uploader -->
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="$t('items.image')"
                                name="image"
                                :help="rules.image ? rules.image.message : null"
                                :validateStatus="rules.image ? 'error' : null"
                            >
                                <UploadFileEmit
                                    :formData="formData"
                                    folder="items"
                                    uploadField="image"
                                    :acceptFormat="'image/*,.pdf'"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.image = file.file;
                                            formData.image_url = file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="$t('items.alert_quantity')"
                                name="alert_quantity"
                                :help="rules.alert_quantity ? rules.alert_quantity.message : null"
                                :validateStatus="rules.alert_quantity ? 'error' : null"
                            >
                                <a-input-number
                                    v-model:value="formData.alert_quantity"
                                    style="width: 100%"
                                    :min="0"
                                    :placeholder="$t('items.alert_quantity')"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('items.dimensions') + ' (' + $t('items.length') + ' x ' + $t('items.width') + ' x ' + $t('items.height') + ')'"
                                name="item_length"
                                :help="rules.item_length ? rules.item_length.message : null"
                                :validateStatus="rules.item_length ? 'error' : null"
                            >
                                <a-input-group compact class="ant-input" :class="{'ant-input-status-error': rules.item_length}" style="display: flex; justify-content: space-between; align-items: center;">
                                    <a-input-number
                                        v-model:value="formData.item_length"
                                        :placeholder="$t('items.length')"
                                        :bordered="false"
                                        :min="0"
                                    />
                                    <span>x</span>
                                    <a-input-number
                                        v-model:value="formData.item_width"
                                        :placeholder="$t('items.width')"
                                        :bordered="false"
                                        :min="0"
                                    />
                                    <span>x</span>
                                    <a-input-number
                                        v-model:value="formData.item_height"
                                        :placeholder="$t('items.height')"
                                        :bordered="false"
                                        :min="0"
                                    />
                                    <a-select
                                        v-model:value="formData.dimension_unit"
                                        :placeholder="$t('common.unit')"
                                        :bordered="false"
                                    >
                                        <a-select-option
                                            v-for="d in dimensionUnits"
                                            :key="d"
                                            :value="d"
                                        >
                                            {{ d.toUpperCase() }}
                                        </a-select-option>
                                    </a-select>
                                </a-input-group>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('items.weight')"
                                name="weight"
                                :help="rules.weight ? rules.weight.message : null"
                                :validateStatus="rules.weight ? 'error' : null"
                            >
                                <a-input-group compact>
                                    <a-input-number
                                        v-model:value="formData.weight"
                                        style="width: 80%"
                                        :min="0"
                                        :placeholder="$t('common.placeholder_default_text', [$t('items.weight')])"
                                    />
                                    <a-select
                                        v-model:value="formData.weight_unit"
                                        style="width: 20%"
                                        :placeholder="$t('common.unit')"
                                    >
                                        <a-select-option
                                            v-for="w in weightUnits"
                                            :key="w"
                                            :value="w"
                                        >
                                            {{ w }}
                                        </a-select-option>
                                    </a-select>
                                </a-input-group>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('items.manufacturer')"
                                name="manufacturer_id"
                                :help="rules.manufacturer_id ? rules.manufacturer_id.message : null"
                                :validateStatus="rules.manufacturer_id ? 'error' : null"
                            >
                                <SelectInput
                                    :value="formData.manufacturer_id"
                                    simple-form
                                    show-status
                                    url="item-manufactures"
                                    :placeholder="$t('items.item_category')"
                                    :options="manufacturers"
                                    @onChange="(value) => formData.manufacturer_id = value"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('items.brand')"
                                name="brand_id"
                                :help="rules.brand_id ? rules.brand_id.message : null"
                                :validateStatus="rules.brand_id ? 'error' : null"
                            >
                                <SelectInput
                                    :value="formData.brand_id"
                                    simple-form
                                    show-status
                                    url="item-brands"
                                    :placeholder="$t('items.item_category')"
                                    :options="brands"
                                    @onChange="(value) => formData.brand_id = value"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('common.description')"
                                name="description"
                                :help="rules.description ? rules.description.message : null"
                                :validateStatus="rules.description ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.description"
                                    :placeholder="$t('common.placeholder_default_text', [$t('common.description')])"
                                    :auto-size="{ minRows: 4, maxRows: 6 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- Sales and Purchase Information Section -->
                    <a-divider />
                    <a-row :gutter="16">
                        <!-- Sales Information -->
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <div class="section-header">
                                <h4>{{ $t('items.sales_information') }}</h4>
                                <a-checkbox v-model:checked="formData.is_sellable">
                                    {{ $t('items.sellable') }}
                                </a-checkbox>
                            </div>
                            <a-form-item
                                :label="$t('items.sale_price')"
                                name="sale_price"
                                :help="rules.sale_price ? rules.sale_price.message : null"
                                :validateStatus="rules.sale_price ? 'error' : null"
                                :class="{ 'required': formData.is_sellable }"
                            >
                                <a-input-number
                                    v-model:value="formData.sale_price"
                                    style="width: 100%"
                                    :min="0"
                                    :precision="2"
                                    :disabled="!formData.is_sellable"
                                    :placeholder="$t('common.placeholder_default_text', [$t('items.sale_price')])"
                                >
                                    <template #addonBefore>MXN</template>
                                </a-input-number>
                            </a-form-item>
                            <a-form-item
                                :label="$t('common.description')"
                                name="sale_description"
                                :help="rules.sale_description ? rules.sale_description.message : null"
                                :validateStatus="rules.sale_description ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.sale_description"
                                    :disabled="!formData.is_sellable"
                                    :placeholder="$t('common.placeholder_default_text', [$t('common.description')])"
                                    :auto-size="{ minRows: 3, maxRows: 5 }"
                                />
                            </a-form-item>
                        </a-col>

                        <!-- Purchase Information -->
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <div class="section-header">
                                <h4>{{ $t('items.purchase_information') }}</h4>
                                <a-checkbox v-model:checked="formData.is_purchasable">
                                    {{ $t('items.purchasable') }}
                                </a-checkbox>
                            </div>
                            <a-form-item
                                :label="$t('items.cost_price')"
                                name="cost_price"
                                :help="rules.cost_price ? rules.cost_price.message : null"
                                :validateStatus="rules.cost_price ? 'error' : null"
                                :class="{ 'required': formData.is_purchasable }"
                            >
                                <a-input-number
                                    v-model:value="formData.cost_price"
                                    style="width: 100%"
                                    :min="0"
                                    :precision="2"
                                    :disabled="!formData.is_purchasable"
                                    :placeholder="$t('common.placeholder_default_text', [$t('items.cost_price')])"
                                >
                                    <template #addonBefore>MXN</template>
                                </a-input-number>
                            </a-form-item>
                            <a-form-item
                                :label="$t('common.description')"
                                name="purchase_description"
                                :help="rules.purchase_description ? rules.purchase_description.message : null"
                                :validateStatus="rules.purchase_description ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.purchase_description"
                                    :disabled="!formData.is_purchasable"
                                    :placeholder="$t('common.placeholder_default_text', [$t('common.description')])"
                                    :auto-size="{ minRows: 3, maxRows: 5 }"
                                />
                            </a-form-item>
                            <a-form-item
                                :label="$t('items.preferred_supplier')"
                                name="supplier_id"
                                :help="rules.supplier_id ? rules.supplier_id.message : null"
                                :validateStatus="rules.supplier_id ? 'error' : null"
                            >
                                <SelectInput
                                    :value="formData.supplier_id"
                                    simple-form
                                    show-status
                                    url="suppliers"
                                    :placeholder="$t('items.preferred_supplier')"
                                    :options="suppliers"
                                    :disabled="!formData.is_purchasable"
                                    @onChange="(value) => formData.supplier_id = value"
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
import { defineComponent } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    InfoCircleOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import SelectInput from "../../../../common/components/common/select/SelectInput.vue";
import UploadFileEmit from "../../../../common/core/ui/file/UploadFileEmit.vue";
import fieldsFactory from "./fields";

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
        "manufacturers",
        "brands",
        "suppliers",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        InfoCircleOutlined,
        SelectInput,
        UploadFileEmit,
    },
    setup(props, { emit }) {
        const { addEditFileRequestAdmin, loading, rules } = apiAdmin();
        const { units, dimensionUnits, weightUnits } = fieldsFactory();
        
        const manufacturersOptions = props.manufacturers.map((item) => ({
            label: item.name,
            value: item.xid,
        }));

        const onSubmit = () => {
            // Remove image_url from formData before sending
            const filteredFormData = { ...props.formData };
            delete filteredFormData.image_url;

            // Convert booleans to 1/0 for FormData
            filteredFormData.is_sellable = filteredFormData.is_sellable ? 1 : 0;
            filteredFormData.is_purchasable = filteredFormData.is_purchasable ? 1 : 0;
            filteredFormData.returnable = filteredFormData.returnable ? 1 : 0;

            addEditFileRequestAdmin({
                url: props.url,
                fieldTypes: {
                    json: [],
                    file: ["image"]
                },
                data: filteredFormData,
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
            units,
            dimensionUnits,
            weightUnits,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "64%",
        };
    },
});
</script>

<style scoped>
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.section-header h4 {
    margin: 0;
    font-weight: 600;
}
</style>