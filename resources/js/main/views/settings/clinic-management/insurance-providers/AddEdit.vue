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
                                :label="$t('insurance_providers.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('insurance_providers.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('insurance_providers.payor_id')"
                                name="payor_id"
                                :help="
                                    rules.payor_id ? rules.payor_id.message : null
                                "
                                :validateStatus="rules.payor_id ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.payor_id"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('insurance_providers.payor_id'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('insurance_providers.fax_number')"
                                name="fax_number"
                                :help="
                                    rules.fax_number
                                        ? rules.fax_number.message
                                        : null
                                "
                                :validateStatus="rules.fax_number ? 'error' : null"
                            >
                                    <PhoneSelect
                                        :value="formData.fax_number"
                                        :countryCode="formData.country_code"
                                        :disabled="loading"
                                        @onUpdate="(data) => { formData.fax_number = data.phone; formData.country_code = data.country_code; }"
                                    />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('insurance_providers.phone_support')"
                                name="phone_support"
                                :help="
                                    rules.phone_support
                                        ? rules.phone_support.message
                                        : null
                                "
                                :validateStatus="
                                    rules.phone_support ? 'error' : null
                                "
                            >
                                    <PhoneSelect
                                        :value="formData.phone_support"
                                        :countryCode="formData.country_code"
                                        :disabled="loading"
                                        @onUpdate="(data) => { formData.phone_support = data.phone; formData.country_code = data.country_code; }"
                                    />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('insurance_providers.mailing_address')"
                                name="mailing_address"
                            >
                                <AddressForm
                                    v-model="addressForm"
                                    :rules="rules"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('insurance_providers.is_active')"
                                name="is_active"
                            >
                                <a-switch v-model:checked="formData.is_active" />
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
import { defineComponent, ref, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../../common/composable/apiAdmin";
import AddressForm from "../../../../../common/components/common/address/AddressForm.vue";
import PhoneSelect from "../../../../../common/components/common/select/PhoneSelect.vue";

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
        AddressForm,
        PhoneSelect,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        
        const addressForm = ref({
            address_line_1: "",
            address_line_2: "",
            neighborhood: "",
            postal_code: "",  // Fixed: changed from zip_code to postal_code
            zip_code_id: undefined,
            city: "",
            municipality: "",
            state: "",  // Fixed: changed from state_name to state
            state_code: undefined,
            state_xid: undefined,
            country_code: "MX",
            country_name: "",
            country_xid: undefined,
        });

        const onSubmit = () => {
            // Debug: Log addressForm data
            console.log("DEBUG: Insurance Provider addressForm.value in onSubmit:", addressForm.value);

            // Compose addresses payload with correct field mappings
            props.formData.addresses = [
                {
                    address_line_1: addressForm.value.address_line_1 || "",
                    address_line_2: addressForm.value.address_line_2 || "",
                    neighborhood: addressForm.value.neighborhood || "",
                    postal_code: addressForm.value.postal_code || "",  // Added missing postal_code field
                    city: addressForm.value.city || "",  // Added missing city field
                    state: addressForm.value.state || "",  // Added missing state field
                    zip_code_id: addressForm.value.zip_code_id || undefined,
                    address_type: 'other',
                    is_default: true,
                    status: true,
                },
            ];

            // Debug: Log addresses array
            console.log("DEBUG: Insurance Provider addresses array:", props.formData.addresses);

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
        watch(
            () => props.visible,
            (newValue) => {
                if (newValue && props.addEditType === "edit" && props.data) {
                    // Load address data if available
                    if (props.data.addresses && props.data.addresses.length > 0) {
                        const address = props.data.addresses[0];
                        addressForm.value = {
                            address_line_1: address.address_line_1 || "",
                            address_line_2: address.address_line_2 || "",
                            neighborhood: address.neighborhood || "",
                            postal_code: address.zip_code?.code || address.postal_code || "",  // Fixed: was zip_code
                            zip_code_id: address.zip_code?.xid || address.zip_code_id || undefined,
                            city: address.zip_code?.city || address.city || "",
                            municipality: address.zip_code?.municipality || address.municipality || "",
                            state: address.zip_code?.state?.name || address.state || "",  // Fixed: was state_name
                            state_code: address.zip_code?.state?.code || address.state_code || undefined,
                            state_xid: address.zip_code?.state?.xid || address.state_xid || undefined,
                            country_code: address.zip_code?.state?.country?.code || address.country_code || "MX",
                            country_name: address.zip_code?.state?.country?.name || address.country_name || "",
                            country_xid: address.zip_code?.state?.country?.xid || address.country_xid || undefined,
                        };
                    }
                } else if (newValue && props.addEditType === "add") {
                    // Reset form for new entries
                    addressForm.value = {
                        address_line_1: "",
                        address_line_2: "",
                        neighborhood: "",
                        postal_code: "",  // Fixed: was zip_code
                        zip_code_id: undefined,
                        city: "",
                        municipality: "",
                        state: "",  // Fixed: was state_name
                        state_code: undefined,
                        state_xid: undefined,
                        country_code: "MX",
                        country_name: "",
                        country_xid: undefined,
                    };
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            addressForm,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "40%",
        };
    },
});
</script>
