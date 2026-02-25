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
        <a-tabs v-model:activeKey="activeTab">
            <a-tab-pane key="personal" :tab="$t('doctors.personal_info')">
                <a-form layout="vertical">
                    <a-row>
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="$t('user.profile_image')"
                                name="profile_image"
                                :help="
                                    rules.profile_image
                                        ? rules.profile_image.message
                                        : null
                                "
                                :validateStatus="
                                    rules.profile_image ? 'error' : null
                                "
                            >
                                <UploadFileEmit
                                    :formData="formData"
                                    folder="user"
                                    uploadField="profile_image"
                                    :acceptFormat="'image/*'"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.profile_image = file.file;
                                            formData.profile_image_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="18" :lg="18">
                            <a-row
                                :gutter="16"
                                v-if="permsArray.includes('admin')"
                            >
                                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                    <a-form-item
                                        :label="$t('user.role')"
                                        name="role_id"
                                        :help="
                                            rules.role_id
                                                ? rules.role_id.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.role_id ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <span style="display: flex">
                                            <a-select
                                                v-model:value="formData.role_id"
                                                style="width: 100%"
                                                :allowClear="true"
                                            >
                                                <a-select-option
                                                    v-for="r in roles"
                                                    :key="r.xid"
                                                    :value="r.xid"
                                                    >{{
                                                        r.name
                                                    }}</a-select-option
                                                >
                                            </a-select>
                                            <RoleAddButton
                                                @onAddSuccess="roleAdded"
                                            />
                                        </span>
                                    </a-form-item>
                                </a-col>
                            </a-row>

                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.name')"
                                        name="name"
                                        :help="
                                            rules.name
                                                ? rules.name.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.name ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.name"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('user.name')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.last_name')"
                                        name="last_name"
                                        :help="
                                            rules.last_name
                                                ? rules.last_name.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.last_name ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-input
                                            v-model:value="formData.last_name"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('user.last_name')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>

                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.phone')"
                                        name="phone"
                                        :help="
                                            rules.phone
                                                ? rules.phone.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.phone ? 'error' : null
                                        "
                                    >
                                        <PhoneSelect
                                            :value="formData.phone"
                                            :countryCode="formData.country_code"
                                            :disabled="loading"
                                            @onUpdate="(data) => { formData.phone = data.phone; formData.country_code = data.country_code; }"
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.status')"
                                        name="status"
                                        :help="
                                            rules.status
                                                ? rules.status.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.status ? 'error' : null
                                        "
                                        class="required"
                                    >
                                        <a-switch
                                            v-model:checked="formData.status"
                                            checkedValue="enabled"
                                            uncheckedValue="disabled"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                    </a-row>

                    <!-- Email & Password Section -->
                    <a-divider orientation="left" style="margin-top: 24px">{{
                        $t("user.login_information")
                    }}</a-divider>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.email'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.password')"
                                name="password"
                                :help="
                                    rules.password
                                        ? rules.password.message
                                        : null
                                "
                                :validateStatus="
                                    rules.password ? 'error' : null
                                "
                                class="required"
                            >
                                <a-input-password
                                    v-model:value="formData.password"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.password'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-divider orientation="left" style="margin-top: 24px">{{
                        $t("common.address")
                    }}</a-divider>
                </a-form>
            </a-tab-pane>

            <!-- Clinic Access Tab -->
            <a-tab-pane
                key="clinic_access"
                :tab="$t('menu.clinics')"
                v-if="permsArray.includes('admin')"
                :disabled="addEditType === 'add'"
            >
                <ClinicAccessManager
                    :userId="addEditType === 'edit' ? data.xid : 'new'"
                    :availableRoles="roles"
                    :userRoleId="formData.role_id || data?.x_role_id"
                    @update:clinics="handleClinicsUpdate"
                    @update:defaultClinicId="handleDefaultClinicUpdate"
                />
            </a-tab-pane>
        </a-tabs>
        <template #footer>
            <a-button
                v-if="activeTab === 'personal'"
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
            <a-button
                v-else-if="activeTab === 'clinic_access'"
                type="primary"
                @click="onSaveClinics"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{ $t("common.update") }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import apiAdmin from "../../../common/composable/apiAdmin";
import UploadFileEmit from "../../../common/core/ui/file/UploadFileEmit.vue";
import RoleAddButton from "../settings/roles/AddButton.vue";
import common from "../../../common/composable/common";
import AddressForm from "../../../common/components/common/address/AddressForm.vue";
import ClinicAccessManager from "./ClinicAccessManager.vue";
import PhoneSelect from "../../../common/components/common/select/PhoneSelect.vue";

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
        UploadFileEmit,
        RoleAddButton,
        AddressForm,
        ClinicAccessManager,
        PhoneSelect,
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const { permsArray, user, appSetting } = common();
        const { addEditFileRequestAdmin, addEditRequestAdmin, loading, rules } =
            apiAdmin();
        const roles = ref([]);
        const roleUrl = "roles?limit=10000";
        const store = useStore();
        const activeTab = ref("personal");
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
        const selectedClinics = ref([]);
        const defaultClinicId = ref(null);

        onMounted(() => {
            const rolesPromise = axiosAdmin.get(roleUrl);

            Promise.all([rolesPromise]).then(([rolesResponse]) => {
                roles.value = rolesResponse.data;
            });
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
                            addressForm.value.postal_code =
                                addr.zip_code.code || "";  // Fixed: was zip_code
                            addressForm.value.city = addr.zip_code.city || "";
                            addressForm.value.municipality =
                                addr.zip_code.municipality || "";

                            // If zip_code has estado, extract state info
                            if (addr.zip_code.estado) {
                                addressForm.value.state =
                                    addr.zip_code.estado.name || "";  // Fixed: was state_name
                            }
                        } else {
                            // Fallback if no zip_code object or primitive fields
                            addressForm.value.postal_code = addr.postal_code || addr.zip_code || "";  // Fixed: was zip_code
                            addressForm.value.city = addr.city || "";
                            addressForm.value.state = addr.state || addr.state_name || "";  // Fixed: was state_name
                        }
                    } else {
                        // Reset address form for new user or user with no address
                        addressForm.value = {
                            address_line_1: "",
                            address_line_2: "",
                            neighborhood: "",
                            postal_code: "",  // Fixed: was zip_code
                            zip_code_id: undefined,
                            city: "",
                            municipality: "",
                            state: "",  // Fixed: was state_name
                            country_code: "MX",
                        };

                        // Fallback for legacy flat fields
                        if (props.formData.address) {
                            addressForm.value.address_line_1 =
                                props.formData.address;
                        }
                    }
                }
            },
        );

        const onSubmit = () => {
            // Debug: Log addressForm data
            console.log("DEBUG: Users addressForm.value in onSubmit:", addressForm.value);

            // Compose addresses payload with correct field mappings
            props.formData.addresses = [
                {
                    address_line_1: addressForm.value.address_line_1 || "",
                    address_line_2: addressForm.value.address_line_2 || "",
                    neighborhood: addressForm.value.neighborhood || "",
                    postal_code: addressForm.value.postal_code || "",  // Fixed: was zip_code
                    city: addressForm.value.city || "",  // Added missing city field
                    state: addressForm.value.state || "",  // Added missing state field
                    zip_code_id: addressForm.value.zip_code_id || undefined,
                    address_type: "home",
                    is_default: true,
                    status: true,
                },
            ];

            // Debug: Log addresses array
            console.log("DEBUG: Users addresses array:", props.formData.addresses);

            // Keep legacy flat fields for backward compatibility
            props.formData.address =
                addressForm.value.address_line_1 || props.formData.address;

            // Remove profile_image_url from formData before sending
            const filteredFormData = { ...props.formData };
            delete filteredFormData.profile_image_url;

            // Add clinic access data
            filteredFormData.clinics = selectedClinics.value;
            if (defaultClinicId.value) {
                filteredFormData.default_clinic_id = defaultClinicId.value;
            }

            addEditFileRequestAdmin({
                url: props.url,
                fieldTypes: {
                    json: ["addresses", "clinics"], // Validate that addresses and clinics are handled as JSON
                    file: ["profile_image"],
                },
                data: filteredFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                    if (user.value.xid == res.xid) {
                        store.dispatch("auth/updateUser");
                    }
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const roleAdded = () => {
            axiosAdmin.get(roleUrl).then((response) => {
                roles.value = response.data;
            });
        };

        const handleClinicsUpdate = (clinics) => {
            selectedClinics.value = clinics;
        };

        const handleDefaultClinicUpdate = (id) => {
            defaultClinicId.value = id;
        };

        const onSaveClinics = () => {
            addEditRequestAdmin({
                url: `users/${props.data.xid}/update-clinics`,
                data: {
                    clinics: JSON.stringify(selectedClinics.value),
                    default_clinic_id: defaultClinicId.value,
                },
                successMessage:
                    t("doctor_clinics.clinic_access_updated") ||
                    "Clinic access updated successfully",
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                    if (user.value.xid == res.xid) {
                        store.dispatch("auth/updateUser");
                    }
                },
            });
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            roles,

            roleAdded,
            permsArray,
            appSetting,

            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
            activeTab,
            addressForm,
            handleClinicsUpdate,
            handleDefaultClinicUpdate,
            onSaveClinics,
        };
    },
});
</script>
