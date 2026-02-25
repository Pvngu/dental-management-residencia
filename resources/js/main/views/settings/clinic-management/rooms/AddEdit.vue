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
                                :label="$t('common.name')"
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
                                        $t('common.placeholder_default_text', [
                                            $t('common.name'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('room.floor')"
                                name="floor"
                                :help="
                                    rules.floor
                                        ? rules.floor.message
                                        : null
                                "
                                :validateStatus="
                                    rules.floor ? 'error' : null
                                "
                            >
                                <a-input-number
                                    v-model:value="formData.floor"
                                    :min="1"
                                    :max="300"
                                    style="width: 100%"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('room.floor'),
                                        ])
                                    "
                                    :formatter="value => {
                                        if (!value) return '';
                                        const suffix = value == 1 ? 'st' : value == 2 ? 'nd' : value == 3 ? 'rd' : 'th';
                                        return `${value}${suffix} floor`;
                                    }"
                                    :parser="value => {
                                        if (!value) return '';
                                        return parseInt(value.replace(/(st|nd|rd|th)\s*floor/, ''));
                                    }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('room.room_type')"
                                name="room_type_id"
                                :help="
                                    rules.room_type_id
                                        ? rules.room_type_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.room_type_id ? 'error' : null
                                "
                                class="required"
                            >
                                <SelectInput
                                    :value="formData.room_type_id"
                                    simple-form
                                    show-status
                                    url="room-types"
                                    :placeholder="$t('room.room_type')"
                                    :options="roomTypes"
                                    @onChange="(value) => formData.room_type_id = value"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('room.capacity')"
                                name="capacity"
                                :help="
                                    rules.capacity
                                        ? rules.capacity.message
                                        : null
                                "
                                :validateStatus="
                                    rules.capacity ? 'error' : null
                                "
                            >
                                <a-input-number
                                    v-model:value="formData.capacity"
                                    :min="1"
                                    :max="1000"
                                    style="width: 100%"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('room.capacity'),
                                        ])
                                    "
                                    :formatter="value => {
                                        if (!value) return '';
                                        return `${value} ${value == 1 ? $t('room.patient') : $t('room.patients')}`;
                                    }"
                                    :parser="value => {
                                        if (!value) return '';
                                        return parseInt(value.replace(` ${$t('room.patient')}`, '').replace(` ${$t('room.patients')}`, ''));
                                    }"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('common.status')"
                                name="status"
                                :help="
                                    rules.status
                                        ? rules.status.message
                                        : null
                                "
                                :validateStatus="
                                    rules.status ? 'error' : null
                                "
                            >
                                <a-select
                                    v-model:value="formData.status"
                                    :placeholder="
                                        $t('common.select_default_text', [
                                            $t('common.status'),
                                        ])
                                    "
                                >
                                    <a-select-option value="Available">
                                        {{ $t("room.available") }}
                                    </a-select-option>
                                    <a-select-option value="Occupied">
                                        {{ $t("room.occupied") }}
                                    </a-select-option>
                                    <a-select-option value="Reserved">
                                        {{ $t("room.reserved") }}
                                    </a-select-option>
                                    <a-select-option value="Maintenance">
                                        {{ $t("room.maintenance") }}
                                    </a-select-option>
                                </a-select>
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-form-item
                                :label="$t('room.notes')"
                                name="notes"
                                :help="
                                    rules.notes
                                        ? rules.notes.message
                                        : null
                                "
                                :validateStatus="
                                    rules.notes ? 'error' : null
                                "
                            >
                                <a-textarea
                                    v-model:value="formData.notes"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('room.notes'),
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
                    addEditType == "add" ? $t("common.create") : $t("common.update")
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
import apiAdmin from "../../../../../common/composable/apiAdmin";
import SelectInput from "../../../../../common/components/common/select/SelectInput.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "roomTypes",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        SelectInput,
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
