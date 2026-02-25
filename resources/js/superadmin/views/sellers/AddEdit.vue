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
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        :label="$t('seller.profile_image')"
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
                            folder="sellers"
                            uploadField="profile_image"
                            :acceptFormat="'image/*'"
                            @onFileUploaded="
                                (file) => {
                                    formData.profile_image = file.file;
                                    formData.profile_image_url = file.file_url;
                                }
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="18" :lg="18">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.name')"
                                name="name"
                                :help="rules.name ? rules.name.message : null"
                                :validateStatus="rules.name ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('seller.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.last_name')"
                                name="last_name"
                                :help="
                                    rules.last_name
                                        ? rules.last_name.message
                                        : null
                                "
                                :validateStatus="
                                    rules.last_name ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.last_name"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('seller.last_name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                                class="required"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('seller.email'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.phone')"
                                name="phone"
                                :help="rules.phone ? rules.phone.message : null"
                                :validateStatus="rules.phone ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.phone"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('seller.phone'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.gender')"
                                name="gender"
                                :help="
                                    rules.gender ? rules.gender.message : null
                                "
                                :validateStatus="rules.gender ? 'error' : null"
                            >
                                <a-select
                                    v-model:value="formData.gender"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('seller.gender'),
                                        ])
                                    "
                                    :allowClear="true"
                                    style="width: 100%"
                                >
                                    <a-select-option value="male">
                                        {{ $t("seller.male") }}
                                    </a-select-option>
                                    <a-select-option value="female">
                                        {{ $t("seller.female") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.status')"
                                name="status"
                                :help="
                                    rules.status ? rules.status.message : null
                                "
                                :validateStatus="rules.status ? 'error' : null"
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

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.commission_rate')"
                                name="commission_rate"
                                :help="
                                    rules.commission_rate
                                        ? rules.commission_rate.message
                                        : null
                                "
                                :validateStatus="
                                    rules.commission_rate ? 'error' : null
                                "
                            >
                                <a-input
                                    v-model:value="formData.commission_rate"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('seller.commission_rate'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.date_of_birth')"
                                name="date_of_birth"
                                :help="
                                    rules.date_of_birth
                                        ? rules.date_of_birth.message
                                        : null
                                "
                                :validateStatus="
                                    rules.date_of_birth ? 'error' : null
                                "
                            >
                                <DateTimePicker
                                    :dateTime="formData.date_of_birth"
                                    :isFutureDateDisabled="true"
                                    :showTime="false"
                                    :onlyDate="true"
                                    @dateTimeChanged="
                                        (changedDateTime) => {
                                            formData.date_of_birth =
                                                changedDateTime;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('seller.address')"
                                name="address"
                                :help="
                                    rules.address ? rules.address.message : null
                                "
                                :validateStatus="rules.address ? 'error' : null"
                            >
                                <a-textarea
                                    v-model:value="formData.address"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('seller.address'),
                                        ])
                                    "
                                    :auto-size="{ minRows: 2, maxRows: 4 }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <a-row :gutter="16" v-if="addEditType === 'add'">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('seller.password')"
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
                                            $t('seller.password'),
                                        ])
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
import { defineComponent } from "vue";
import { PlusOutlined, LoadingOutlined, SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../common/composable/apiAdmin";
import UploadFileEmit from "../../../common/core/ui/file/UploadFileEmit.vue";
import DateTimePicker from "../../../common/components/common/calendar/DateTimePicker.vue";

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
        DateTimePicker,
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
