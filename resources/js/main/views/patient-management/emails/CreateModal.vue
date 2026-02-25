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
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('common.recipient')"
                        name="recipient"
                        :help="rules.recipient ? rules.recipient.message : null"
                        :validateStatus="rules.recipient ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.recipient"
                            :placeholder="$t('common.placeholder_default_text', [$t('common.recipient')])"
                            type="email"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('common.subject')"
                        name="subject"
                        :help="rules.subject ? rules.subject.message : null"
                        :validateStatus="rules.subject ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.subject"
                            :placeholder="$t('common.placeholder_default_text', [$t('common.subject')])"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('common.body')"
                        name="body"
                        :help="rules.body ? rules.body.message : null"
                        :validateStatus="rules.body ? 'error' : null"
                        class="required"
                    >
                        <a-textarea
                            v-model:value="formData.body"
                            :placeholder="$t('common.placeholder_default_text', [$t('common.body')])"
                            :auto-size="{ minRows: 6, maxRows: 12 }"
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('common.status')"
                        name="status"
                    >
                        <a-select
                            v-model:value="formData.status"
                            :placeholder="$t('common.select_default_text', [$t('common.status')])"
                        >
                            <a-select-option value="draft">
                                {{ $t("common.draft") }}
                            </a-select-option>
                            <a-select-option value="scheduled">
                                {{ $t("common.scheduled") }}
                            </a-select-option>
                            <a-select-option value="sent">
                                {{ $t("common.sent") }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
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
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";

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
        SaveOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();

        const onSubmit = () => {
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

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "50%",
        };
    },
});
</script>
