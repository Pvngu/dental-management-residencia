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
                        :label="addEditType === 'add' ? $t('doctor_holidays.doctors') : $t('doctor_holidays.doctor')"
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
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('doctor_holidays.holiday_type')"
                        name="holiday_type"
                        :help="rules.holiday_type ? rules.holiday_type.message : null"
                        :validateStatus="rules.holiday_type ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.holiday_type"
                            :placeholder="$t('common.select_default_text', [$t('doctor_holidays.holiday_type')])"
                        >
                            <a-select-option value="vacation">
                                {{ $t("doctor_holidays.vacation") }}
                            </a-select-option>
                            <a-select-option value="sick_leave">
                                {{ $t("doctor_holidays.sick_leave") }}
                            </a-select-option>
                            <a-select-option value="personal">
                                {{ $t("doctor_holidays.personal") }}
                            </a-select-option>
                            <a-select-option value="other">
                                {{ $t("doctor_holidays.other") }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('doctor_holidays.status')"
                        name="status"
                        :help="rules.status ? rules.status.message : null"
                        :validateStatus="rules.status ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.status"
                            :placeholder="$t('common.select_default_text', [$t('doctor_holidays.status')])"
                        >
                            <a-select-option value="pending">
                                {{ $t("doctor_holidays.pending") }}
                            </a-select-option>
                            <a-select-option value="approved">
                                {{ $t("doctor_holidays.approved") }}
                            </a-select-option>
                            <a-select-option value="rejected">
                                {{ $t("doctor_holidays.rejected") }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('doctor_holidays.dates')"
                        name="dateRange"
                        :help="rules.start_date ? rules.start_date.message : (rules.end_date ? rules.end_date.message : null)"
                        :validateStatus="rules.start_date || rules.end_date ? 'error' : null"
                        class="required"
                    >
                        <DateRangePicker
                            @dateTimeChanged="(changedDateTime) => {
                                if (changedDateTime && changedDateTime.length === 2) {
                                    formData.dateRange = changedDateTime;
                                    formData.start_date = dayjs(changedDateTime[0]).format('YYYY-MM-DD');
                                    formData.end_date = dayjs(changedDateTime[1]).format('YYYY-MM-DD');
                                } else {
                                    formData.dateRange = [];
                                    formData.start_date = '';
                                    formData.end_date = '';
                                }
                            }"
                            :dateRange="formData.dateRange"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('doctor_holidays.reason')"
                        name="reason"
                        :help="rules.reason ? rules.reason.message : null"
                        :validateStatus="rules.reason ? 'error' : null"
                    >
                        <a-textarea
                            v-model:value="formData.reason"
                            :placeholder="$t('common.placeholder_default_text', [$t('doctor_holidays.reason')])"
                            :rows="4"
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
import common from "../../../../common/composable/common";
import UserSelect from "../../../../common/components/common/select/UserSelect.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";

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
        DateRangePicker,
        UserSelect,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { dayjs } = common();

        const onSubmit = () => {
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    console.log(res);
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
            dayjs,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>