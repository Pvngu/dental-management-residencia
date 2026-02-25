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
            <a-row :gutter="16" v-if="!hideDoctorSelect">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="addEditType === 'add' ? $t('doctor_breaks.doctors') : $t('doctor_breaks.doctor')"
                        name="doctor_id"
                        :help="rules.doctor_id ? rules.doctor_id.message : null"
                        :validateStatus="rules.doctor_id ? 'error' : null"
                        class="required"
                    >
                        <UserSelect
                            @onChange="(id) => {
                                formData.doctor_id = id;
                            }"
                            :value="formData.doctor_id"
                            userType="doctor"
                            :mode="addEditType === 'add' ? 'multiple' : undefined"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('doctor_breaks.event')"
                        name="every_day"
                        :help="rules.every_day ? rules.every_day.message : null"
                        :validateStatus="rules.every_day ? 'error' : null"
                    >
                        <a-radio-group
                            v-model:value="formData.every_day"
                        >
                            <a-radio-button :value="1">
                                {{ $t('doctor_breaks.every_day') }}
                            </a-radio-button>
                            <a-radio-button :value="0">
                                {{ $t("doctor_breaks.single_day") }}
                            </a-radio-button>
                        </a-radio-group>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('doctor_breaks.break_from')"
                        name="break_from"
                        :help="rules.break_from ? rules.break_from.message : null"
                        :validateStatus="rules.break_from ? 'error' : null"
                    >
                        <TimePicker
                            :time="formData.break_from"
                            @timeChanged="
                                (changeDateTime) =>
                                    (formData.break_from = changeDateTime)
                            "
                            :dateFormat="false"
                            class="w-full"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('doctor_breaks.break_to')"
                        name="break_to"
                        :help="rules.break_to ? rules.break_to.message : null"
                        :validateStatus="rules.break_to ? 'error' : null"
                    >
                        <TimePicker
                            :time="formData.break_to"
                            @timeChanged="
                                (changeDateTime) =>
                                    (formData.break_to = changeDateTime)
                            "
                            :dateFormat="false"
                            class="w-full"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12" v-if="formData.every_day == 0">
                    <a-form-item
                        :label="$t('common.date')"
                        name="date"
                        :help="rules.date ? rules.date.message : null"
                        :validateStatus="rules.date ? 'error' : null"
                    >
                        <DateTimePicker
                            :dateTime="formData.date"
                            :onlyDate="true"
                            :showTime="false"
                            @dateTimeChanged="(changedDateTime) => { formData.date = changedDateTime; }"
                        />
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
import { SaveOutlined } from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import TimePicker from "../../../../common/components/common/calendar/TimePicker.vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import UserSelect from "../../../../common/components/common/select/UserSelect.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
        "statuses",
        "hideDoctorSelect",
    ],
    components: {
        SaveOutlined,
        UserSelect,
        TimePicker,
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
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>
