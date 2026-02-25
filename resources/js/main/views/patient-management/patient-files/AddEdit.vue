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
                                    :placeholder="$t('common.placeholder_default_text', [$t('common.name')])"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('patient_files.patient')"
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
                                :label="$t('patient_files.file')"
                                name="file"
                                :help="rules.file ? rules.file.message : null"
                                :validateStatus="rules.file ? 'error' : null"
                            >
                                <UploadFileBig
                                    :acceptFormat="'image/*,.pdf,.doc,.docx'"
                                    :formData="formData"
                                    folder="patient-files"
                                    uploadField="file"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.file = file.file;
                                            formData.file_url = file.file_url;
                                            formData.file_type = file.type;
                                            formData.file_size = file.size;
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
import UploadFileBig from "../../../../common/core/ui/file/UploadFileBig.vue";
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
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        UploadFileBig,
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
