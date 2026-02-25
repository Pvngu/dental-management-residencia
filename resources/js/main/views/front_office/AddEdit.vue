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

                    <!-- Received postal fields -->
                    <template v-if="formData.postal_type === 'received'">
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.sender_name')"
                                    name="sender_name"
                                    :help="
                                        rules.sender_name
                                            ? rules.sender_name.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.sender_name ? 'error' : null
                                    "
                                    class="required"
                                >
                                    <a-input
                                        v-model:value="formData.sender_name"
                                        placeholder="e.g. Dr. Sarah Mitchell"
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.mail_type')"
                                    name="mail_type_id"
                                    :help="
                                        rules.mail_type_id
                                            ? rules.mail_type_id.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.mail_type_id ? 'error' : null
                                    "
                                    class="required"
                                >
                                    <a-select
                                        v-model:value="formData.mail_type_id"
                                        placeholder="Select type"
                                        style="width: 100%"
                                        optionFilterProp="title"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="mailType in data.mailTypes"
                                            :key="mailType.xid"
                                            :title="mailType.name"
                                            :value="mailType.xid"
                                        >
                                            {{ mailType.name }}
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.received_by')"
                                    name="received_by"
                                    :help="
                                        rules.received_by
                                            ? rules.received_by.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.received_by ? 'error' : null
                                    "
                                >
                                    <UserSelect
                                        @onChange="
                                            (id) => {
                                                formData.received_by = id;
                                            }
                                        "
                                        :value="formData.received_by"
                                        :placeholder="'Select staff'"
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.assigned_to')"
                                    name="assigned_to"
                                    :help="
                                        rules.assigned_to
                                            ? rules.assigned_to.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.assigned_to ? 'error' : null
                                    "
                                >
                                    <UserSelect
                                        @onChange="
                                            (id) => {
                                                formData.assigned_to = id;
                                            }
                                        "
                                        :value="formData.assigned_to"
                                        :placeholder="'Select assignee'"
                                    />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('common.file')"
                                    name="file"
                                    :help="rules.file ? rules.file.message : null"
                                    :validateStatus="rules.file ? 'error' : null"
                                >
                                    <UploadFileBig
                                        :acceptFormat="'image/*,.pdf'"
                                        :formData="formData"
                                        folder="postals"
                                        uploadField="file"
                                        @onFileUploaded="
                                            (file) => {
                                                formData.file = file.file;
                                                formData.file_url = file.file_url;
                                                formData.file_name = file.file_name;
                                            }
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                    </template>

                    <!-- Dispatched postal fields -->
                    <template v-if="formData.postal_type === 'dispatched'">
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.patient_name')"
                                    name="patient_id"
                                    :help="
                                        rules.patient_id
                                            ? rules.patient_id.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.patient_id ? 'error' : null
                                    "
                                >
                                    <UserSelect
                                        @onChange="
                                            (id) => {
                                                formData.patient_id = id;
                                            }
                                        "
                                        :value="formData.patient_id"
                                        :userType="'patients'"
                                    />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.type')"
                                    name="type"
                                    :help="rules.type ? rules.type.message : null"
                                    :validateStatus="rules.type ? 'error' : null"
                                    class="required"
                                >
                                    <a-select
                                        v-model:value="formData.type"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('mail_management.type'),
                                            ])
                                        "
                                    >
                                        <a-select-option value="Package">
                                            Package
                                        </a-select-option>
                                        <a-select-option value="Letter">
                                            Letter
                                        </a-select-option>
                                        <a-select-option value="Document">
                                            Document
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.carrier')"
                                    name="carrier"
                                    :help="
                                        rules.carrier ? rules.carrier.message : null
                                    "
                                    :validateStatus="rules.carrier ? 'error' : null"
                                >
                                    <a-input
                                        v-model:value="formData.carrier"
                                        :placeholder="
                                            $t('common.placeholder_default_text', [
                                                $t('mail_management.carrier'),
                                            ])
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.tracking_number')"
                                    name="tracking_number"
                                    :help="
                                        rules.tracking_number
                                            ? rules.tracking_number.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.tracking_number ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.tracking_number"
                                        :placeholder="
                                            $t('common.placeholder_default_text', [
                                                $t('mail_management.tracking_number'),
                                            ])
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.status')"
                                    name="status"
                                    :help="
                                        rules.status ? rules.status.message : null
                                    "
                                    :validateStatus="rules.status ? 'error' : null"
                                    class="required"
                                >
                                    <a-select
                                        v-model:value="formData.status"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('mail_management.status'),
                                            ])
                                        "
                                    >
                                        <a-select-option value="Pending">
                                            Pending
                                        </a-select-option>
                                        <a-select-option value="Shipped">
                                            Shipped
                                        </a-select-option>
                                        <a-select-option value="In Transit">
                                            In Transit
                                        </a-select-option>
                                        <a-select-option value="Received">
                                            Received
                                        </a-select-option>
                                        <a-select-option value="Delivered">
                                            Delivered
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('mail_management.dispatched_by')"
                                    name="dispatched_by"
                                    :help="
                                        rules.dispatched_by
                                            ? rules.dispatched_by.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.dispatched_by ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.dispatched_by"
                                        :placeholder="
                                            $t('common.placeholder_default_text', [
                                                $t('mail_management.dispatched_by'),
                                            ])
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                    </template>
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
import DateTimePicker from "../../../common/components/common/calendar/DateTimePicker.vue";
import UploadFileBig from "../../../common/core/ui/file/UploadFileBig.vue";
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
        DateTimePicker,
        UploadFileBig,
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
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>
