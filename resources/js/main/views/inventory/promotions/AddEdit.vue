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
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('promotions.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('promotions.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('promotions.promotion_targets')"
                                name="promotion_targets"
                                class="required"
                            >
                                <div class="target-container">
                                    <div class="add-target-section" style="border: 1px solid #f0f0f0; padding: 16px; margin-bottom: 16px; border-radius: 4px;">
                                        <div style="font-weight: 500; margin-bottom: 10px;">{{ $t('common.add') }} {{ $t('promotions.target') }}</div>
                                        <a-row :gutter="8">
                                            <a-col :xs="24" :sm="8">
                                                <a-select
                                                    v-model:value="targetForm.target_type"
                                                    style="width: 100%"
                                                    :placeholder="$t('common.select_default_text', [$t('promotions.target_type')])"
                                                >
                                                    <a-select-option value="product">
                                                        {{ $t('promotions.specific_product') }}
                                                    </a-select-option>
                                                    <a-select-option value="brand">
                                                        {{ $t('promotions.entire_brand') }}
                                                    </a-select-option>
                                                    <a-select-option value="category">
                                                        {{ $t('promotions.product_category') }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-col>
                                            <a-col :xs="24" :sm="12">
                                                <template v-if="targetForm.target_type === 'product'">
                                                    <a-select
                                                        v-model:value="targetForm.target_id"
                                                        style="width: 100%"
                                                        show-search
                                                        optionFilterProp="title"
                                                        :placeholder="$t('common.select_default_text', [$t('promotions.product')])"
                                                    >
                                                        <a-select-option
                                                            v-for="item in filteredItems"
                                                            :key="item.xid"
                                                            :title="item.name"
                                                            :value="item.xid"
                                                        >
                                                            {{ item.name }}
                                                        </a-select-option>
                                                    </a-select>
                                                </template>
                                                <template v-else-if="targetForm.target_type === 'brand'">
                                                    <a-select
                                                        v-model:value="targetForm.target_id"
                                                        style="width: 100%"
                                                        show-search
                                                        optionFilterProp="title"
                                                        :placeholder="$t('common.select_default_text', [$t('promotions.brand')])"
                                                    >
                                                        <a-select-option
                                                            v-for="brand in filteredItemBrands"
                                                            :key="brand.xid"
                                                            :title="brand.name"
                                                            :value="brand.xid"
                                                        >
                                                            {{ brand.name }}
                                                        </a-select-option>
                                                    </a-select>
                                                </template>
                                                <template v-else-if="targetForm.target_type === 'category'">
                                                    <a-select
                                                        v-model:value="targetForm.target_id"
                                                        style="width: 100%"
                                                        show-search
                                                        optionFilterProp="title"
                                                        :placeholder="$t('common.select_default_text', [$t('promotions.category')])"
                                                    >
                                                        <a-select-option
                                                            v-for="category in filteredItemCategories"
                                                            :key="category.xid"
                                                            :title="category.name"
                                                            :value="category.xid"
                                                        >
                                                            {{ category.name }}
                                                        </a-select-option>
                                                    </a-select>
                                                </template>
                                            </a-col>
                                            <a-col :xs="24" :sm="4">
                                                <a-button 
                                                    type="primary" 
                                                    block 
                                                    @click="addTarget"
                                                    :disabled="!targetForm.target_id || !targetForm.target_type"
                                                >
                                                    <PlusOutlined /> {{ $t('common.add') }}
                                                </a-button>
                                            </a-col>
                                        </a-row>
                                    </div>

                                    <div v-if="formData.promotion_targets && formData.promotion_targets.length > 0" 
                                        style="border: 1px solid #f0f0f0; padding: 16px; border-radius: 4px;">
                                        <div style="font-weight: 500; margin-bottom: 10px;">
                                            {{ $t('promotions.selected_targets') }} ({{ formData.promotion_targets.length }})
                                        </div>
                                        <a-tag 
                                            v-for="(target, index) in formData.promotion_targets" 
                                            :key="index"
                                            closable
                                            @close="removeTarget(index)"
                                            :color="getTargetColor(target.target_type)"
                                            style="margin-bottom: 8px; padding: 5px 10px;"
                                        >
                                            <span v-if="target.target_type === 'product'">
                                                <span style="font-weight: 500;">{{ $t('promotions.product') }}:</span> 
                                                {{ getItemName(target.target_id) }}
                                            </span>
                                            <span v-else-if="target.target_type === 'brand'">
                                                <span style="font-weight: 500;">{{ $t('promotions.brand') }}:</span> 
                                                {{ getBrandName(target.target_id) }}
                                            </span>
                                            <span v-else-if="target.target_type === 'category'">
                                                <span style="font-weight: 500;">{{ $t('promotions.category') }}:</span> 
                                                {{ getCategoryName(target.target_id) }}
                                            </span>
                                        </a-tag>
                                    </div>
                                </div>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('promotions.discount_type')"
                                name="discount_type"
                                :help="rules.discount_type ? rules.discount_type.message : null"
                                :validateStatus="rules.discount_type ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.discount_type"
                                    style="width: 100%"
                                    default-value="fixed"
                                >
                                    <a-select-option value="percentage">
                                        {{ $t('promotions.percentage') }} (%)
                                    </a-select-option>
                                    <a-select-option value="fixed">
                                        {{ $t('promotions.fixed') }} ($)
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('promotions.discount_value')"
                                name="discount_value"
                                :help="rules.discount_value ? rules.discount_value.message : null"
                                :validateStatus="rules.discount_value ? 'error' : null"
                                class="required"
                            >
                                <div v-if="formData.discount_type === 'percentage'" class="flex gap-4">
                                    <div class="flex-3">
                                        <a-slider
                                            v-model:value="formData.discount_value"
                                            :min="0"
                                            :max="100"
                                            :step="1"
                                            style="margin-bottom: 15px;"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <a-input-number
                                            v-model:value="formData.discount_value"
                                            style="width: 100%"
                                            :min="0"
                                            :max="100"
                                            :precision="2"
                                            :formatter="value => `${value}%`"
                                            :parser="value => value.replace('%', '')"
                                            :placeholder="$t('common.placeholder_default_text', [$t('promotions.discount_value')])"
                                        />
                                    </div>
                                </div>
                                <a-input-number
                                    v-else
                                    v-model:value="formData.discount_value"
                                    style="width: 100%"
                                    :min="0"
                                    :max="99999"
                                    :precision="2"
                                    :formatter="value => `$ ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                                    :parser="value => value.replace(/\$\s?|(,*)/g, '')"
                                    :placeholder="$t('common.placeholder_default_text', [$t('promotions.discount_value')])"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('promotions.start_date')"
                                name="start_date"
                                :help="rules.start_date ? rules.start_date.message : null"
                                :validateStatus="rules.start_date ? 'error' : null"
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.start_date"
                                    :isFutureDateDisabled="false"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.start_date = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="12" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('promotions.end_date')"
                                name="end_date"
                                :help="rules.end_date ? rules.end_date.message : null"
                                :validateStatus="rules.end_date ? 'error' : null"
                                class="required"
                            >
                                <DateTimePicker
                                    :dateTime="formData.end_date"
                                    :isFutureDateDisabled="false"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="(changedDateTime) => { formData.end_date = changedDateTime; }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item name="is_active">
                                <a-switch
                                    v-model:checked="formData.is_active"
                                    :checkedChildren="$t('promotions.promotion_is_active')"
                                    :unCheckedChildren="$t('promotions.promotion_is_inactive')"
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
import { defineComponent, ref, watch, computed } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "items",
        "itemCategories",
        "itemBrands",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        DateTimePicker,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const targetForm = ref({
            target_type: "product",
            target_id: undefined,
        });

        // Computed properties to filter out already selected items
        const filteredItems = computed(() => {
            return props.items?.filter(item => 
                !props.formData.promotion_targets.some(
                    target => target.target_type === 'product' && target.target_id === item.xid
                )
            ) || [];
        });

        const filteredItemBrands = computed(() => {
            return props.itemBrands?.filter(brand => 
                !props.formData.promotion_targets.some(
                    target => target.target_type === 'brand' && target.target_id === brand.xid
                )
            ) || [];
        });

        const filteredItemCategories = computed(() => {
            return props.itemCategories?.filter(category => 
                !props.formData.promotion_targets.some(
                    target => target.target_type === 'category' && target.target_id === category.xid
                )
            ) || [];
        });

        const addTarget = () => {
            if (!targetForm.value.target_id || !targetForm.value.target_type) {
                return;
            }
            
            // Check if this target already exists
            const exists = props.formData.promotion_targets.some(
                target => target.target_id === targetForm.value.target_id && 
                          target.target_type === targetForm.value.target_type
            );
            
            if (!exists) {
                props.formData.promotion_targets.push({
                    target_type: targetForm.value.target_type,
                    target_id: targetForm.value.target_id,
                });
            }
            
            // Reset the form
            targetForm.value = {
                target_type: "product",
                target_id: undefined,
            };
        };

        const removeTarget = (index) => {
            props.formData.promotion_targets.splice(index, 1);
        };

        const getItemName = (id) => {
            const item = props.items.find(i => i.xid === id);
            return item ? item.name : id;
        };

        const getBrandName = (id) => {
            const brand = props.itemBrands.find(b => b.xid === id);
            return brand ? brand.name : id;
        };

        const getCategoryName = (id) => {
            const category = props.itemCategories.find(c => c.xid === id);
            return category ? category.name : id;
        };

        const getTargetColor = (type) => {
            switch(type) {
                case 'product':
                    return 'blue'; // Blue for products
                case 'brand':
                    return 'purple'; // Purple for brands
                case 'category':
                    return 'green'; // Green for categories
                default:
                    return ''; // Default color
            }
        };

        const onSubmit = () => {
            // Check if promotion targets are added
            if (props.formData.promotion_targets.length === 0) {
                rules.value = { 
                    promotion_targets: { 
                        message: "Please add at least one promotion target." 
                    } 
                };
                return;
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

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        // Watch for data changes when editing
        watch(() => props.data, (newData) => {
            if (newData && props.addEditType === 'edit') {
                // Fetch promotion targets for this promotion if they're not included in the data
                if (!props.formData.promotion_targets || props.formData.promotion_targets.length === 0) {
                    apiAdmin().axiosAdmin.get(`promotion_targets?filters[promotion_id]=${newData.xid}`)
                        .then(response => {
                            if (response.data && Array.isArray(response.data)) {
                                props.formData.promotion_targets = response.data.map(target => ({
                                    target_type: target.target_type,
                                    target_id: target.x_target_id
                                }));
                            }
                        });
                }
            }
        }, { immediate: true });

        // Watch for discount type changes to adjust value constraints
        watch(() => props.formData.discount_type, (newType) => {
            if (newType === 'percentage' && props.formData.discount_value > 100) {
                props.formData.discount_value = 100;
            }
        });

        // Reset target_id when target_type changes to avoid invalid selections
        watch(() => targetForm.value.target_type, () => {
            targetForm.value.target_id = undefined;
        });

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            targetForm,
            addTarget,
            removeTarget,
            getItemName,
            getBrandName,
            getCategoryName,
            getTargetColor,
            filteredItems,
            filteredItemBrands,
            filteredItemCategories,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "50%",
        };
    },
});
</script>

<style scoped>
.target-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
</style>
