<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.profile`)" class="p-0!">
            </a-page-header>
        </template>
        <template #actions>
            <a-button type="primary" @click="onSubmit">
                <template #icon> <SaveOutlined /> </template>
                {{ $t("common.update") }}
            </a-button>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" class="text-xs">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.settings") }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t("menu.profile") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <a-card class="page-content-container mt-1 mb-5">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('user.name')"
                            name="name"
                            :help="rules.name ? rules.name.message : null"
                            :validateStatus="rules.name ? 'error' : null"
                            class="required"
                        >
                            <a-input
                                v-model:value="formData.name"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('user.name'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('user.email')"
                            name="email"
                            :help="rules.email ? rules.email.message : null"
                            :validateStatus="rules.email ? 'error' : null"
                            class="required"
                        >
                            <a-input
                                :value="formData.email"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('user.email'),
                                    ])
                                "
                                disabled
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('user.password')"
                            name="password"
                            :help="
                                rules.password ? rules.password.message : null
                            "
                            :validateStatus="rules.password ? 'error' : null"
                        >
                            <a-input
                                v-model:value="formData.password"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('user.password'),
                                    ])
                                "
                                type="password"
                                autocomplete="off"
                            />
                            <small class="small-text-message">
                                {{ $t("user.password_blank") }}
                            </small>
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('user.phone')"
                            name="phone"
                            :help="rules.phone ? rules.phone.message : null"
                            :validateStatus="rules.phone ? 'error' : null"
                        >
                            <PhoneSelect
                                :value="formData.phone"
                                :countryCode="formData.country_code"
                                :disabled="loading"
                                @onUpdate="(data) => { formData.phone = data.phone; formData.country_code = data.country_code; }"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
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
                                :acceptFormat="'image/*,.pdf'"
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
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('user.address')"
                            name="address"
                            :help="rules.address ? rules.address.message : null"
                            :validateStatus="rules.address ? 'error' : null"
                        >
                            <a-textarea
                                v-model:value="formData.address"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('user.address'),
                                    ])
                                "
                                :rows="4"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item>
                            <a-button
                                type="primary"
                                @click="onSubmit"
                                :loading="loading"
                            >
                                <template #icon> <SaveOutlined /> </template>
                                {{ $t("common.update") }}
                            </a-button>
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>
    </admin-page-table-content>
</template>
<script>
import { onMounted, ref } from "vue";
import {
    EyeOutlined,
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import apiAdmin from "../../../../common/composable/apiAdmin";
import UploadFileEmit from "../../../../common/core/ui/file/UploadFileEmit.vue";
import SettingSidebar from "../SettingSidebar.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import PhoneSelect from "../../../../common/components/common/select/PhoneSelect.vue";

export default {
    components: {
        EyeOutlined,
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        ExclamationCircleOutlined,
        SaveOutlined,

        UploadFileEmit,
        SettingSidebar,
        AdminPageHeader,
        PhoneSelect,
    },
    setup() {
        const { addEditFileRequestAdmin, loading, rules } = apiAdmin();
        const { t } = useI18n();
        const store = useStore();
        const formData = ref({});
        const currencies = ref({});
        const user = store.state.auth.user;

        onMounted(() => {
            formData.value = {
                name: user.name,
                email: user.email,
                password: "",
                phone: user.phone,
                country_code: user.country_code || 'US',
                address: user.address || '',
                profile_image: user.profile_image,
                profile_image_url: user.profile_image_url,
            };
        });

        const onSubmit = () => {
            // Remove profile_image_url from formData before sending
            const filteredFormData = { ...formData.value };
            delete filteredFormData.profile_image_url;

            addEditFileRequestAdmin({
                url: `profile`,
                fieldTypes: {
                    json: [],
                    file: ["profile_image"],
                },
                data: filteredFormData,
                successMessage: t("user.profile_updated"),
                success: (res) => {
                    store.commit("auth/updateUser", res.user);
                },
            });
        };

        return {
            loading,
            rules,
            formData,
            currencies,
            onSubmit,
        };
    },
};
</script>
