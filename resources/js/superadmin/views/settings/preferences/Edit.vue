<template>
    <SuperAdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.preferences`)" class="p-0!">
                <template #extra>
                    <a-button type="primary" @click="onSubmit">
                        <template #icon> <SaveOutlined /> </template>
                        {{ $t("common.update") }}
                    </a-button>
                </template>
            </a-page-header>
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
                    {{ $t("menu.preferences") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </SuperAdminPageHeader>

    <a-row>
        <a-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4" class="bg-setting-sidebar">
            <SettingSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <admin-page-table-content>
                <a-card
                    class="page-content-container mt-1 mb-5"
                    :bodyStyle="{ paddingTop: '15px' }"
                >
                    <a-form layout="vertical">
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('common.language')"
                                    name="language"
                                    :help="rules.language ? rules.language.message : null"
                                    :validateStatus="rules.language ? 'error' : null"
                                    class="required"
                                >
                                    <a-select
                                        v-model:value="formData.language"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('common.language'),
                                            ])
                                        "
                                        style="width: 100%"
                                    >
                                        <a-select-option value="en">
                                            English
                                        </a-select-option>
                                        <a-select-option value="es">
                                            Español
                                        </a-select-option>
                                    </a-select>
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
        </a-col>
    </a-row>
</template>
<script>
import { onMounted, ref } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import apiAdmin from "../../../../common/composable/apiAdmin";
import SettingSidebar from "../SettingSidebar.vue";
import SuperAdminPageHeader from "../../../layouts/SuperAdminPageHeader.vue";

export default {
    components: {
        SaveOutlined,
        SettingSidebar,
        SuperAdminPageHeader,
    },
    setup() {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { t } = useI18n();
        const store = useStore();
        const formData = ref({});
        const user = store.state.auth.user;

        onMounted(() => {
            formData.value = {
                language: user.language || 'en',
            };
        });

        const onSubmit = () => {
            addEditRequestAdmin({
                url: `preferences`,
                data: formData.value,
                successMessage: t("user.preferences_updated"),
                success: (res) => {
                    store.commit("auth/updateUser", res.user);
                    // Update locale if language changed
                    if (res.user.language) {
                        store.commit("auth/updateLang", res.user.language);
                        window.location.reload();
                    }
                },
            });
        };

        return {
            loading,
            rules,
            formData,
            onSubmit,
        };
    },
};
</script>
