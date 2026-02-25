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
                        :label="$t('treatment_types.name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('treatment_types.name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('treatment_types.category')"
                        name="category"
                        :help="rules.category ? rules.category.message : null"
                        :validateStatus="rules.category ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            style="width: 100%"
                            v-model:value="formData.category"
                            show-search
                            optionFilterProp="title"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('treatment_types.category'),
                                ])
                            "
                        >
                            <a-select-option
                                v-for="category in categoryOptions"
                                :key="category"
                                :title="category"
                                :value="category"
                            >
                                {{ category }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('treatment_types.duration')"
                        name="duration_minutes"
                        :help="rules.duration_minutes ? rules.duration_minutes.message : null"
                        :validateStatus="rules.duration_minutes ? 'error' : null"
                    >
                        <a-input-number
                            v-model:value="formData.duration_minutes"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('treatment_types.duration'),
                                ])
                            "
                            style="width: 100%"
                            :min="0"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('treatment_types.price')"
                        name="price"
                        :help="rules.price ? rules.price.message : null"
                        :validateStatus="rules.price ? 'error' : null"
                    >
                        <a-input-number
                            v-model:value="formData.price"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('treatment_types.price'),
                                ])
                            "
                            style="width: 100%"
                            :min="0"
                            :step="0.01"
                            :precision="2"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('treatment_types.description')"
                        name="description"
                        :help="rules.description ? rules.description.message : null"
                        :validateStatus="rules.description ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.description"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('treatment_types.description'),
                                ])
                            "
                            :rows="4"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('treatment_types.status')"
                        name="is_active"
                        :help="rules.is_active ? rules.is_active.message : null"
                        :validateStatus="rules.is_active ? 'error' : null"
                    >
                        <a-switch v-model:checked="formData.is_active" />
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
                {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>
<script>
import { defineComponent, ref } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../../../common/composable/apiAdmin";
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
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { categoryOptions } = fields();

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
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
            categoryOptions,
        };
    },
});
</script>
