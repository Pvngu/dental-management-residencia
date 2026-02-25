<template>
    <a-modal
        :open="visible"
        :closable="false"
        :centered="true"
        :title="pageTitle"
        @ok="onSubmit"
        :width="800"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('questionnaire_templates.code')"
                        name="code"
                        :help="rules.code ? rules.code.message : null"
                        :validateStatus="rules.code ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.code"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('questionnaire_templates.code'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('questionnaire_templates.version')"
                        name="version"
                        :help="rules.version ? rules.version.message : null"
                        :validateStatus="rules.version ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.version"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('questionnaire_templates.version'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('questionnaire_templates.name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('questionnaire_templates.name'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('questionnaire_templates.description')"
                        name="description"
                        :help="rules.description ? rules.description.message : null"
                        :validateStatus="rules.description ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.description"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('questionnaire_templates.description'),
                                ])
                            "
                            :rows="3"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('questionnaire_templates.target_population')"
                        name="target_population"
                        :help="rules.target_population ? rules.target_population.message : null"
                        :validateStatus="rules.target_population ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.target_population"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('questionnaire_templates.target_population'),
                                ])
                            "
                        >
                            <a-select-option value="ALL">All</a-select-option>
                            <a-select-option value="ADULT">Adult</a-select-option>
                            <a-select-option value="CHILD">Child</a-select-option>
                            <a-select-option value="ELDERLY">Elderly</a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('questionnaire_templates.estimated_duration')"
                        name="estimated_duration"
                        :help="rules.estimated_duration ? rules.estimated_duration.message : null"
                        :validateStatus="rules.estimated_duration ? 'error' : null"
                    >
                        <a-input-number
                            v-model:value="formData.estimated_duration"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('questionnaire_templates.estimated_duration'),
                                ])
                            "
                            :min="1"
                            style="width: 100%"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('questionnaire_templates.normative_ref')"
                        name="normative_ref"
                        :help="rules.normative_ref ? rules.normative_ref.message : null"
                        :validateStatus="rules.normative_ref ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.normative_ref"
                            :placeholder="
                                $t('common.placeholder_default_text', [
                                    $t('questionnaire_templates.normative_ref'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item :label="$t('questionnaire_templates.is_active')" name="is_active">
                        <a-switch
                            v-model:checked="formData.is_active"
                            :checkedChildren="$t('common.yes')"
                            :unCheckedChildren="$t('common.no')"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="12" :md="12" :lg="12">
                    <a-form-item :label="$t('questionnaire_templates.is_evergreen')" name="is_evergreen">
                        <a-switch
                            v-model:checked="formData.is_evergreen"
                            :checkedChildren="$t('common.yes')"
                            :unCheckedChildren="$t('common.no')"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

        </a-form>
        <template #footer>
            <a-space>
                <a-button
                    key="submit"
                    type="primary"
                    :loading="loading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <SaveOutlined />
                    </template>
                    {{ addEditType == "add" ? $t("common.create") : $t("common.update") }}
                </a-button>
                <a-button key="back" @click="onClose">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-modal>
</template>

<script>
import { defineComponent } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";

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
    },
    setup(props, { emit }) {
        const { appSetting } = common();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

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
            appSetting,
            loading,
            rules,
            onClose,
            onSubmit,
        };
    },
});
</script>
