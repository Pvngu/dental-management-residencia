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
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('open_cases.title')"
                                name="title"
                                :help="rules.title ? rules.title.message : null"
                                :validateStatus="rules.title ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.title"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('open_cases.title'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('open_cases.patient')"
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
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('open_cases.priority')"
                                name="priority"
                                :help="rules.priority ? rules.priority.message : null"
                                :validateStatus="rules.priority ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.priority"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('open_cases.priority'),
                                        ])
                                    "
                                >
                                    <a-select-option value="low">
                                        {{ $t('open_cases.low') }}
                                    </a-select-option>
                                    <a-select-option value="medium">
                                        {{ $t('open_cases.medium') }}
                                    </a-select-option>
                                    <a-select-option value="high">
                                        {{ $t('open_cases.high') }}
                                    </a-select-option>
                                    <a-select-option value="critical">
                                        {{ $t('open_cases.critical') }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>

                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('open_cases.status')"
                                name="status"
                                :help="rules.status ? rules.status.message : null"
                                :validateStatus="rules.status ? 'error' : null"
                                class="required"
                            >
                                <a-select
                                    style="width: 100%"
                                    v-model:value="formData.status"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('open_cases.status'),
                                        ])
                                    "
                                >
                                    <a-select-option value="open">
                                        {{ $t('open_cases.open') }}
                                    </a-select-option>
                                    <a-select-option value="in_progress">
                                        {{ $t('open_cases.in_progress') }}
                                    </a-select-option>
                                    <a-select-option value="resolved">
                                        {{ $t('open_cases.resolved') }}
                                    </a-select-option>
                                    <a-select-option value="closed">
                                        {{ $t('open_cases.closed') }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('open_cases.description')"
                                name="description"
                                :help="
                                    rules.description
                                        ? rules.description.message
                                        : null
                                "
                                :validateStatus="rules.description ? 'error' : null"
                                class="required"
                            >
                                <a-textarea
                                    v-model:value="formData.description"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('open_cases.description'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 4, maxRows: 6 }"
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
import apiAdmin from "../../../common/composable/apiAdmin";
import UserSelect from "../../../common/components/common/select/UserSelect.vue";

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
        UserSelect,
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
            drawerWidth: window.innerWidth <= 991 ? "90%" : "40%",
        };
    },
});
</script>
