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
                                :label="$t('fax.direction')"
                                name="direction"
                                :help="rules.direction ? rules.direction.message : null"
                                :validateStatus="rules.direction ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    v-model:value="formData.direction"
                                    :placeholder="$t('common.select_default_text', [$t('fax.direction')])"
                                >
                                    <a-select-option value="outbound">
                                        {{ $t("fax.outbound") }}
                                    </a-select-option>
                                    <a-select-option value="inbound">
                                        {{ $t("fax.inbound") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('fax.to_number')"
                                name="to_number"
                                :help="rules.to_number ? rules.to_number.message : null"
                                :validateStatus="rules.to_number ? 'error' : null"
                                class="required"
                            >
                                    <PhoneSelect
                                        :value="formData.to_number"
                                        :countryCode="formData.country_code"
                                        :disabled="loading"
                                        @onUpdate="(data) => { formData.to_number = data.phone; formData.country_code = data.country_code; }"
                                    />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('fax.from_number')"
                                name="from_number"
                                :help="rules.from_number ? rules.from_number.message : null"
                                :validateStatus="rules.from_number ? 'error' : null"
                            >
                                    <PhoneSelect
                                        :value="formData.from_number"
                                        :countryCode="formData.country_code"
                                        :disabled="loading"
                                        @onUpdate="(data) => { formData.from_number = data.phone; formData.country_code = data.country_code; }"
                                    />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('fax.patient')"
                                name="patient_id"
                                :help="rules.patient_id ? rules.patient_id.message : null"
                                :validateStatus="rules.patient_id ? 'error' : null"
                            >
                                <UserSelect
                                    @onChange="
                                        (id) => {
                                            formData.patient_id = id;
                                        }
                                    "
                                    :value="formData.patient_id"
                                    :showPhone="true"
                                    userType="patient"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('fax.insurance_provider')"
                                name="insurance_provider_id"
                                :help="rules.insurance_provider_id ? rules.insurance_provider_id.message : null"
                                :validateStatus="rules.insurance_provider_id ? 'error' : null"
                            >
                                <a-select
                                    v-model:value="formData.insurance_provider_id"
                                    :placeholder="$t('common.select_default_text', [$t('fax.insurance_provider')])"
                                    :allowClear="true"
                                    style="width: 100%"
                                    optionFilterProp="title"
                                    show-search
                                >
                                    <a-select-option
                                        v-for="provider in insuranceProviders"
                                        :key="provider.xid"
                                        :title="provider.name"
                                        :value="provider.xid"
                                    >
                                        {{ provider.name }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('fax.notes')"
                                name="notes"
                                :help="rules.notes ? rules.notes.message : null"
                                :validateStatus="rules.notes ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.notes"
                                    :placeholder="$t('common.placeholder_default_text', [$t('fax.notes')])"
                                    :auto-size="{ minRows: 3, maxRows: 6 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('fax.file')"
                                name="file"
                                :help="rules.file ? rules.file.message : null"
                                :validateStatus="rules.file ? 'error' : null"
                            >
                                <UploadFileEmit
                                    :acceptFormat="'.pdf,.tiff,.tif'"
                                    :formData="formData"
                                    uploadField="file"
                                    folder="faxes"
                                    useSecondaryDesign
                                    @onFileUploaded="
                                        (fileData) => {
                                            formData.file = fileData.file;
                                            formData.file_url = fileData.file_url;
                                            formData.file_name = fileData.file_name;
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
import { defineComponent, computed } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import UploadFileEmit from "../../../../common/core/ui/file/UploadFileEmit.vue";
import UserSelect from "../../../../common/components/common/select/UserSelect.vue";
import PhoneSelect from "../../../../common/components/common/select/PhoneSelect.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "patients",
        "insuranceProviders",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        UploadFileEmit,
        UserSelect,
        PhoneSelect,
    },
    setup(props, { emit }) {
        const { addEditFileRequestAdmin, loading, rules } = apiAdmin();

        const onSubmit = () => {
            rules.value = {};
            loading.value = true;

            const filteredFormData = { ...props.formData };
            delete filteredFormData.file_url;

            addEditFileRequestAdmin({
                url:
                    props.url +
                    (props.addEditType == "add" ? "/store" : "/update"),
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
        };
    },
});
</script>
