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
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('clinic_location.phone_number')"
                                name="phone_number"
                                :help="
                                    rules.phone_number
                                        ? rules.phone_number.message
                                        : null
                                "
                                :validateStatus="
                                    rules.phone_number ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.phone_number"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('clinic_location.phone_number'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('common.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('common.email'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('common.status')"
                                name="status"
                                :help="
                                    rules.status ? rules.status.message : null
                                "
                                :validateStatus="rules.status ? 'error' : null"
                            >
                                <a-switch
                                    v-model:checked="formData.status"
                                    :checkedChildren="$t('common.enabled')"
                                    :unCheckedChildren="$t('common.disabled')"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('clinic_location.logo')"
                                name="logo"
                                :help="rules.logo ? rules.logo.message : null"
                                :validateStatus="rules.logo ? 'error' : null"
                            >
                                <UploadFileBig
                                    :acceptFormat="'image/*'"
                                    :formData="formData"
                                    folder="clinic-logos"
                                    uploadField="logo"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.logo = file.file;
                                            formData.logo_url = file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-divider
                                orientation="left"
                                style="margin-top: 24px"
                                >{{ $t("common.address") }}</a-divider
                            >
                            <AddressForm v-model="addressForm" :rules="rules" />
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
import { defineComponent, ref, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../../common/composable/apiAdmin";
import UploadFileBig from "../../../../../common/core/ui/file/UploadFileBig.vue";
import AddressForm from "../../../../../common/components/common/address/AddressForm.vue";

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
        AddressForm,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const addressForm = ref({
            address_line_1: "",
            address_line_2: "",
            neighborhood: undefined,
            zip_code: "",
            zip_code_id: undefined,
            city: "",
            municipality: "",
            state_name: "",
            state_code: undefined,
            state_xid: undefined,
            country_code: "MX",
            country_name: "",
            country_xid: undefined,
        });

        watch(
            () => props.visible,
            (newValue) => {
                if (newValue) {
                    // Prefill address from addresses array if present
                    if (
                        props.formData.addresses &&
                        props.formData.addresses.length > 0
                    ) {
                        const addr = props.formData.addresses[0];
                        addressForm.value.address_line_1 =
                            addr.address_line_1 || "";
                        addressForm.value.address_line_2 =
                            addr.address_line_2 || "";
                        addressForm.value.neighborhood =
                            addr.neighborhood || "";
                        addressForm.value.zip_code_id =
                            addr.zip_code_id || addr.x_zip_code_id || undefined;

                        // If zip_code is an object, extract nested fields
                        if (
                            addr.zip_code &&
                            typeof addr.zip_code === "object"
                        ) {
                            addressForm.value.zip_code =
                                addr.zip_code.code || "";
                            addressForm.value.city = addr.zip_code.city || "";
                            addressForm.value.municipality =
                                addr.zip_code.municipality || "";

                            // If zip_code has estado, extract state info
                            if (addr.zip_code.estado) {
                                addressForm.value.state_name =
                                    addr.zip_code.estado.name || "";
                            }
                        } else {
                            // Fallback if no zip_code object (or just ID)
                            addressForm.value.zip_code = "";
                        }
                    } else {
                        // Reset address form
                        addressForm.value = {
                            address_line_1: "",
                            address_line_2: "",
                            neighborhood: undefined,
                            zip_code: "",
                            zip_code_id: undefined,
                            city: "",
                            municipality: "",
                            state_name: "",
                            country_code: "MX",
                        };
                    }
                }
            },
        );

        const onSubmit = () => {
            // Compose addresses payload (include zip_code_id)
            props.formData.addresses = [
                {
                    address_line_1: addressForm.value.address_line_1 || "",
                    address_line_2: addressForm.value.address_line_2 || "",
                    neighborhood: addressForm.value.neighborhood || "",
                    zip_code: addressForm.value.zip_code || "",
                    zip_code_id: addressForm.value.zip_code_id || undefined,
                    address_type: "home",
                    is_default: true,
                    status: true,
                },
            ];

            // Remove profile_image_url if it exists (copy paste safety, though not in this form)
            const filteredFormData = { ...props.formData };
            if (filteredFormData.logo_url) delete filteredFormData.logo_url;

            // Determine method and URL based on addEditType
            const isEdit = props.addEditType === "edit";
            const method = isEdit ? "put" : "post";
            const url = isEdit ? `${props.url}/${props.data.xid}` : props.url;

            addEditRequestAdmin({
                url: url,
                data: filteredFormData,
                method: method,
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
            addressForm,
        };
    },
});
</script>
