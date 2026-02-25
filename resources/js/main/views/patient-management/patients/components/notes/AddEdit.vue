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
                                :label="$t('patient_notes.title')"
                                name="title"
                                :help="
                                    rules.title ? rules.title.message : null
                                "
                                :validateStatus="
                                    rules.title ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.title"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('patient_notes.title'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('patient_notes.content')"
                                name="content"
                                :help="
                                    rules.content ? rules.content.message : null
                                "
                                :validateStatus="
                                    rules.content ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.content"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('patient_notes.content'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 4, maxRows: 6 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('patient_notes.note_type')"
                                name="note_type"
                                :help="
                                    rules.note_type ? rules.note_type.message : null
                                "
                                :validateStatus="
                                    rules.note_type ? 'error' : null
                                "
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.note_type"
                                    show-search
                                    optionFilterProp="title"
                                    :placeholder="$t('common.select_default_text', [$t('patient_notes.note_type')])"
                                >
                                    <a-select-option
                                        v-for="noteType in noteTypes"
                                        :key="noteType.key"
                                        :title="noteType.value"
                                        :value="noteType.key"
                                    >
                                        {{ noteType.value }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('patient_notes.is_private')"
                                name="is_private"
                            >
                                <a-switch v-model:checked="formData.is_private" />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('patient_notes.is_highlighted')"
                                name="is_highlighted"
                            >
                                <a-switch v-model:checked="formData.is_highlighted" />
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
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../../../common/composable/apiAdmin";
import fields from "./fields";

export default defineComponent({
    props: [
        "patientId",
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
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { noteTypes } = fields();

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: {
                    ...props.formData,
                    patient_id: props.patientId,
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
            noteTypes,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "40%",
        };
    },
});
</script>
