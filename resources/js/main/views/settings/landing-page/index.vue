<template>
    <div class="landing-page-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-button type="primary" @click="onSubmit" :loading="loading">
                <SaveOutlined />
                {{ $t("common.update") }}
            </a-button>
        </div>

        <a-card :title="$t('landing_page.general_settings')" class="mb-4">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.title')"
                            name="landing_page_title"
                            :help="
                                rules.landing_page_title
                                    ? rules.landing_page_title.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_title ? 'error' : null
                            "
                        >
                            <a-input
                                v-model:value="formData.landing_page_title"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.title'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.phone')"
                            name="landing_page_phone"
                            :help="
                                rules.landing_page_phone
                                    ? rules.landing_page_phone.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_phone ? 'error' : null
                            "
                        >
                            <a-input
                                v-model:value="formData.landing_page_phone"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.phone'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.email')"
                            name="landing_page_email"
                            :help="
                                rules.landing_page_email
                                    ? rules.landing_page_email.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_email ? 'error' : null
                            "
                        >
                            <a-input
                                v-model:value="formData.landing_page_email"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.email'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.address')"
                            name="landing_page_address"
                            :help="
                                rules.landing_page_address
                                    ? rules.landing_page_address.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_address ? 'error' : null
                            "
                        >
                            <a-input
                                v-model:value="formData.landing_page_address"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.address'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('landing_page.description')"
                            name="landing_page_description"
                            :help="
                                rules.landing_page_description
                                    ? rules.landing_page_description.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_description ? 'error' : null
                            "
                        >
                            <a-textarea
                                v-model:value="
                                    formData.landing_page_description
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.description'),
                                    ])
                                "
                                :auto-size="{ minRows: 3, maxRows: 6 }"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>

        <a-card :title="$t('landing_page.page_section')" class="mb-4">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('landing_page.page_image')"
                            name="landing_page_page_image"
                            :help="
                                rules.landing_page_page_image
                                    ? rules.landing_page_page_image.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_page_image ? 'error' : null
                            "
                        >
                            <UploadFileBig
                                :acceptFormat="'image/*'"
                                :formData="formData"
                                folder="landing-page"
                                uploadField="landing_page_page_image"
                                @onFileUploaded="
                                    (file) => {
                                        formData.landing_page_page_image =
                                            file.file;
                                        formData.landing_page_page_image_url =
                                            file.file_url;
                                    }
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>

        <a-card :title="$t('landing_page.about_section')" class="mb-4">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.about_title')"
                            name="landing_page_about_title"
                            :help="
                                rules.landing_page_about_title
                                    ? rules.landing_page_about_title.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_about_title ? 'error' : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_about_title
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.about_title'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.services_title')"
                            name="landing_page_services_title"
                            :help="
                                rules.landing_page_services_title
                                    ? rules.landing_page_services_title.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_services_title
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_services_title
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.services_title'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.about_description')"
                            name="landing_page_about_description"
                            :help="
                                rules.landing_page_about_description
                                    ? rules.landing_page_about_description
                                          .message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_about_description
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-textarea
                                v-model:value="
                                    formData.landing_page_about_description
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.about_description'),
                                    ])
                                "
                                :auto-size="{ minRows: 3, maxRows: 6 }"
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.services_description')"
                            name="landing_page_services_description"
                            :help="
                                rules.landing_page_services_description
                                    ? rules.landing_page_services_description
                                          .message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_services_description
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-textarea
                                v-model:value="
                                    formData.landing_page_services_description
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.services_description'),
                                    ])
                                "
                                :auto-size="{ minRows: 3, maxRows: 6 }"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>

        <a-card :title="$t('landing_page.contact_section')" class="mb-4">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.contact_title')"
                            name="landing_page_contact_title"
                            :help="
                                rules.landing_page_contact_title
                                    ? rules.landing_page_contact_title.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_contact_title
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_contact_title
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.contact_title'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.contact_description')"
                            name="landing_page_contact_description"
                            :help="
                                rules.landing_page_contact_description
                                    ? rules.landing_page_contact_description
                                          .message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_contact_description
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-textarea
                                v-model:value="
                                    formData.landing_page_contact_description
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.contact_description'),
                                    ])
                                "
                                :auto-size="{ minRows: 2, maxRows: 4 }"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>

        <a-card :title="$t('landing_page.social_media')" class="mb-4">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.facebook_url')"
                            name="landing_page_social_facebook"
                            :help="
                                rules.landing_page_social_facebook
                                    ? rules.landing_page_social_facebook.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_social_facebook
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_social_facebook
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.facebook_url'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.instagram_url')"
                            name="landing_page_social_instagram"
                            :help="
                                rules.landing_page_social_instagram
                                    ? rules.landing_page_social_instagram
                                          .message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_social_instagram
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_social_instagram
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.instagram_url'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.twitter_url')"
                            name="landing_page_social_twitter"
                            :help="
                                rules.landing_page_social_twitter
                                    ? rules.landing_page_social_twitter.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_social_twitter
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_social_twitter
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.twitter_url'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.youtube_url')"
                            name="landing_page_social_youtube"
                            :help="
                                rules.landing_page_social_youtube
                                    ? rules.landing_page_social_youtube.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_social_youtube
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_social_youtube
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.youtube_url'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>

        <a-card :title="$t('landing_page.seo_settings')" class="mb-4">
            <a-form layout="vertical">
                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.meta_title')"
                            name="landing_page_meta_title"
                            :help="
                                rules.landing_page_meta_title
                                    ? rules.landing_page_meta_title.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_meta_title ? 'error' : null
                            "
                        >
                            <a-input
                                v-model:value="formData.landing_page_meta_title"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.meta_title'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                        <a-form-item
                            :label="$t('landing_page.meta_keywords')"
                            name="landing_page_meta_keywords"
                            :help="
                                rules.landing_page_meta_keywords
                                    ? rules.landing_page_meta_keywords.message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_meta_keywords
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-input
                                v-model:value="
                                    formData.landing_page_meta_keywords
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.meta_keywords'),
                                    ])
                                "
                            />
                        </a-form-item>
                    </a-col>
                </a-row>

                <a-row :gutter="16">
                    <a-col :xs="24" :sm="24" :md="24" :lg="24">
                        <a-form-item
                            :label="$t('landing_page.meta_description')"
                            name="landing_page_meta_description"
                            :help="
                                rules.landing_page_meta_description
                                    ? rules.landing_page_meta_description
                                          .message
                                    : null
                            "
                            :validateStatus="
                                rules.landing_page_meta_description
                                    ? 'error'
                                    : null
                            "
                        >
                            <a-textarea
                                v-model:value="
                                    formData.landing_page_meta_description
                                "
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('landing_page.meta_description'),
                                    ])
                                "
                                :auto-size="{ minRows: 2, maxRows: 4 }"
                            />
                        </a-form-item>
                    </a-col>
                </a-row>
            </a-form>
        </a-card>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import fields from "./fields";
import UploadFileBig from "../../../../common/core/ui/file/UploadFileBig.vue";

export default {
    components: {
        SaveOutlined,
        UploadFileBig,
    },
    setup() {
        const { addEditUrl, initData } = fields();
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { appSetting } = common();
        const formData = ref({ ...initData });

        onMounted(() => {
            // Load current landing page settings
            loadSettings();
        });

        const loadSettings = () => {
            addEditRequestAdmin({
                url: "companies/current",
                data: {},
                successMessage: "",
                success: (res) => {
                    // Handle new API structure with basic and advanced settings
                    if (res.basic) {
                        // Map basic settings
                        Object.keys(initData).forEach((key) => {
                            if (res.basic[key] !== undefined) {
                                formData.value[key] = res.basic[key];
                            }
                        });
                    } else if (res.data) {
                        // Legacy support - if still using old API structure
                        const company = res.data;
                        Object.keys(initData).forEach((key) => {
                            if (company[key] !== undefined) {
                                formData.value[key] = company[key];
                            }
                        });
                    }
                },
            });
        };

        const onSubmit = () => {
            addEditRequestAdmin({
                url: "companies/update-landing-page",
                data: {
                    basic: formData.value,
                },
                successMessage: "Landing page settings updated successfully",
                success: (res) => {
                    // Handle success - could reload settings here if needed
                },
            });
        };

        return {
            loading,
            rules,
            onSubmit,
            formData,
            appSetting,
        };
    },
};
</script>
