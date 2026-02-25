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
                                :label="$t('documents.title')"
                                name="title"
                                :help="rules.title ? rules.title.message : null"
                                :validateStatus="rules.title ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.title"
                                    :placeholder="$t('common.placeholder_default_text', [$t('documents.title')])"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('documents.document_type_id')"
                                name="document_type_id"
                                :help="rules.document_type_id ? rules.document_type_id.message : null"
                                :validateStatus="rules.document_type_id ? 'error' : null"
                                class="required"
                            >
                                <SelectInput
                                    :value="formData.document_type_id"
                                    simple-form
                                    url="document-types"
                                    :placeholder="$t('documents.document_type_id')"
                                    :options="documentTypes"
                                    @onChange="(value) => formData.document_type_id = value"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('documents.patient_id')"
                                name="patient_id"
                                :help="rules.patient_id ? rules.patient_id.message : null"
                                :validateStatus="rules.patient_id ? 'error' : null"
                                class="required"
                            >
                                <UserSelect
                                    @onChange="(id) => { formData.patient_id = id; }"
                                    :value="formData.patient_id"
                                    userType="patient"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('documents.notes')"
                                name="notes"
                                :help="rules.notes ? rules.notes.message : null"
                                :validateStatus="rules.notes ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.notes"
                                    :placeholder="$t('common.placeholder_default_text', [$t('documents.notes')])"
                                    :auto-size="{ minRows: 4, maxRows: 6 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('documents.file')"
                                name="file"
                                :help="rules.file ? rules.file.message : null"
                                :validateStatus="rules.file ? 'error' : null"
                            >
                                <UploadFileEmit
                                    :acceptFormat="'image/*,.pdf'"
                                    :formData="formData"
                                    folder="documents"
                                    uploadField="file"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.file = file.file;
                                            formData.file_url = file.file_url;
                                        }
                                    "
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
import { defineComponent } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import UploadFileEmit from "../../../../common/core/ui/file/UploadFileEmit.vue";
import SelectInput from "../../../../common/components/common/select/SelectInput.vue";
import UserSelect from "../../../../common/components/common/select/UserSelect.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "documentTypes",
        "patients",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        UploadFileEmit,
        SelectInput,
        UserSelect
    },
    setup(props, { emit }) {
        const { addEditFileRequestAdmin, loading, rules } = apiAdmin();

        const onSubmit = () => {
            rules.value = {};
            loading.value = true;

            // Remove file_url from formData before sending
            const filteredFormData = { ...props.formData };
            delete filteredFormData.file_url;

            addEditFileRequestAdmin({
                url: props.url,
                fieldTypes: {
                    json: [],
                    file: ["file"]
                },
                data: filteredFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                    loading.value = false;
                },
                error: (err) => {
                    rules.value = err.response.data.errors;
                    loading.value = false;
                },
            });
        }

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
        };
    },
});
</script>
