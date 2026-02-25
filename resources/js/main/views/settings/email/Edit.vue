<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.email_settings`)" class="p-0!">
                <template #extra>
                    <a-button
                        type="primary"
                        @click="onSubmit"
                        :loading="
                            settingRef && settingRef.loading ? true : false
                        "
                    >
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
                    {{ $t("menu.email_settings") }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-table-content>
        <EmailSettings ref="settingRef" />
    </admin-page-table-content>
</template>

<script>
import { ref } from "vue";
import { SaveOutlined } from "@ant-design/icons-vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import EmailSettings from "../../common/settings/email/Edit.vue";

export default {
    components: {
        AdminPageHeader,
        EmailSettings,
        SaveOutlined,
    },
    setup() {
        const settingRef = ref(null);

        const onSubmit = () => {
            settingRef.value.onSubmit();
        };

        return {
            onSubmit,
            settingRef,
        };
    },
};
</script>
