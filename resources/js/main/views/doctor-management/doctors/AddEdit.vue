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
        <a-tabs v-model:activeKey="activeTab">
            <a-tab-pane key="personal" :tab="$t('doctors.personal_info')">
                <a-form layout="vertical">
                    <a-row>
                        <a-col :xs="24" :sm="24" :md="6" :lg="6">
                            <a-form-item
                                :label="$t('user.profile_image')"
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
                                    folder="user"
                                    uploadField="profile_image"
                                    :acceptFormat="'image/*'"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.profile_image = file.file;
                                            formData.profile_image_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="18" :lg="18">
                            <!-- Main Info -->
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.first_name')"
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
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('user.first_name')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.last_name')"
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
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('user.last_name')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('doctors.doctor_department')"
                                        name="doctor_department_id"
                                        :help="
                                            rules.doctor_department_id
                                                ? rules.doctor_department_id
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.doctor_department_id
                                                ? 'error'
                                                : null
                                        "
                                        class="required"
                                    >
                                        <SelectInput
                                            :value="
                                                formData.doctor_department_id
                                            "
                                            simple-form
                                            url="doctor-departments"
                                            :placeholder="
                                                $t('doctors.doctor_department')
                                            "
                                            :options="departments"
                                            @onChange="
                                                (value) =>
                                                    (formData.doctor_department_id =
                                                        value)
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('doctors.designation')"
                                        name="designation"
                                        :help="
                                            rules.designation
                                                ? rules.designation.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.designation ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.designation"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('doctors.designation')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.phone')"
                                        name="phone"
                                        :help="
                                            rules.phone
                                                ? rules.phone.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.phone ? 'error' : null
                                        "
                                    >
                                        <PhoneSelect
                                            :value="formData.phone"
                                            :countryCode="formData.country_code"
                                            @onUpdate="
                                                (data) => {
                                                    formData.phone = data.phone;
                                                    formData.country_code =
                                                        data.country_code;
                                                }
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('doctors.qualification')"
                                        name="qualification"
                                        :help="
                                            rules.qualification
                                                ? rules.qualification.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.qualification ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.qualification
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [
                                                        $t(
                                                            'doctors.qualification',
                                                        ),
                                                    ],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.date_of_birth')"
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
                                            :isFutureDateDisabled="true"
                                            :dateTime="formData.date_of_birth"
                                            @dateTimeChanged="
                                                (changeDateTime) =>
                                                    (formData.date_of_birth =
                                                        changeDateTime)
                                            "
                                            :onlyDate="true"
                                            :showTime="false"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('doctors.practice_id')"
                                        name="practice_id"
                                        :help="
                                            rules.practice_id
                                                ? rules.practice_id.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.practice_id ? 'error' : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="formData.practice_id"
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('doctors.practice_id')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('doctors.color')"
                                        name="color"
                                        :help="
                                            rules.color
                                                ? rules.color.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.color ? 'error' : null
                                        "
                                    >
                                        <ColorInput
                                            :value="formData.color"
                                            @colorChanged="
                                                (changeValue) =>
                                                    (formData.color =
                                                        changeValue)
                                            "
                                            placeholder="doctors.color"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.gender')"
                                        name="gender"
                                        :help="
                                            rules.gender
                                                ? rules.gender.message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.gender ? 'error' : null
                                        "
                                        required
                                    >
                                        <a-radio-group
                                            v-model:value="formData.gender"
                                        >
                                            <a-radio :value="'male'">{{
                                                $t("user.male")
                                            }}</a-radio>
                                            <a-radio :value="'female'">{{
                                                $t("user.female")
                                            }}</a-radio>
                                        </a-radio-group>
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('user.status')"
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
                                        <a-switch
                                            v-model:checked="formData.status"
                                            checkedValue="enabled"
                                            uncheckedValue="disabled"
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('doctors.specialization')"
                                name="specialist"
                                :help="
                                    rules.specialist
                                        ? rules.specialist.message
                                        : null
                                "
                                :validateStatus="
                                    rules.specialist ? 'error' : null
                                "
                            >
                                <SelectInput
                                    :value="formData.specialist"
                                    simple-form
                                    url="doctor-specialty"
                                    :placeholder="$t('doctors.specialization')"
                                    :options="specialists"
                                    @onChange="
                                        (value) => (formData.specialist = value)
                                    "
                                    mode="multiple"
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('doctors.appointment_charge')"
                                name="appointment_charge"
                                :help="
                                    rules.appointment_charge
                                        ? rules.appointment_charge.message
                                        : null
                                "
                                :validateStatus="
                                    rules.appointment_charge ? 'error' : null
                                "
                            >
                                <CurrencyInput
                                    :value="formData.appointment_charge"
                                    @inputNumberChanged="
                                        (changeValue) =>
                                            (formData.appointment_charge =
                                                changeValue)
                                    "
                                    min="0"
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('doctors.professional_id')"
                                name="professional_id"
                                :help="
                                    rules.professional_id
                                        ? rules.professional_id.message
                                        : null
                                "
                                :validateStatus="
                                    rules.professional_id ? 'error' : null
                                "
                            >
                                <UploadFileEmit
                                    :formData="formData"
                                    folder="user"
                                    uploadField="professional_id"
                                    :acceptFormat="'.pdf,image/*'"
                                    @onFileUploaded="
                                        (file) => {
                                            formData.professional_id =
                                                file.file;
                                            formData.professional_id_url =
                                                file.file_url;
                                        }
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- Email & Password Section -->
                    <a-divider orientation="left" style="margin-top: 24px">{{
                        $t("user.login_information")
                    }}</a-divider>
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('user.email')"
                                name="email"
                                :help="rules.email ? rules.email.message : null"
                                :validateStatus="rules.email ? 'error' : null"
                            >
                                <a-input
                                    v-model:value="formData.email"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('user.email'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                        <a-col :xs="24" :sm="24" :md="12" :lg="12">
                            <a-form-item
                                :label="$t('doctors.password')"
                                name="password"
                                :help="
                                    rules.password
                                        ? rules.password.message
                                        : null
                                "
                                :validateStatus="
                                    rules.password ? 'error' : null
                                "
                            >
                                <a-input-password
                                    v-model:value="formData.password"
                                    :placeholder="
                                        $t('common.placeholder_default_text', [
                                            $t('doctors.password'),
                                        ])
                                    "
                                />
                            </a-form-item>
                        </a-col>
                    </a-row>

                    <!-- Address Section -->
                    <a-divider orientation="left" style="margin-top: 24px">{{
                        $t("common.address")
                    }}</a-divider>
                    <AddressForm v-model="addressForm" :rules="rules" />
                </a-form>
            </a-tab-pane>

            <!-- <a-tab-pane
                key="schedule"
                :tab="$t('doctors.schedule')"
                :disabled="addEditType == 'add'"
            >
                <a-form layout="vertical">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <a-form-item
                            v-if="selectedClinicId !== 'all'"
                            :label="$t('doctor_schedules.per_patient_time')"
                            name="per_patient_time"
                            :help="
                                rules.per_patient_time
                                    ? rules.per_patient_time.message
                                    : null
                            "
                            :validateStatus="
                                rules.per_patient_time ? 'error' : null
                            "
                            class="required"
                        >
                            <a-input-number
                                v-model:value="
                                    formData.per_patient_time_minutes
                                "
                                :min="5"
                                :max="180"
                                :step="5"
                                :placeholder="
                                    $t('common.placeholder_default_text', [
                                        $t('doctor_schedules.per_patient_time'),
                                    ])
                                "
                                style="width: 100%"
                                :addon-after="$t('common.minutes')"
                            />
                        </a-form-item>
                    </div>

                    <MultiClinicScheduleTabs
                        v-if="selectedClinicId === 'all'"
                        :doctorId="formData.xid"
                    />

                    <ScheduleTable
                        v-else
                        :schedule="schedule"
                        :days="days"
                        :handleDaySwitch="handleDaySwitch"
                        :isDayAvailable="isDayAvailable"
                        :getDisabledTimesForDay="getDisabledTimesForDay"
                        :rules="rules"
                    />
                </a-form>
            </a-tab-pane> -->

            <!-- <a-tab-pane key="holidays" :disabled="addEditType == 'add'">
                <template #tab>
                    {{ $t("doctors.holidays") }}
                </template>
                <HolidaysTable :doctorId="selectedDoctorId" />
            </a-tab-pane> -->

            <!-- <a-tab-pane key="breaks" :disabled="addEditType == 'add'">
                <template #tab>
                    {{ $t("doctors.breaks") }}
                </template>
                <BreaksTable :doctorId="selectedDoctorId" />
            </a-tab-pane> -->

            <!-- Clinic Access Tab -->
            <!-- <a-tab-pane
                key="clinic_access"
                :tab="$t('menu.clinics')"
                :disabled="addEditType === 'add'"
            >
                <ClinicAccessManager
                    :userId="addEditType === 'edit' ? formData.xid : 'new'"
                    :availableRoles="allRoles"
                    @update:clinics="handleClinicsUpdate"
                    @update:defaultClinicId="handleDefaultClinicUpdate"
                />
            </a-tab-pane> -->
        </a-tabs>
        <template #footer>
            <a-button
                v-if="activeTab === 'personal'"
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
            <a-button
                v-else-if="activeTab === 'clinic_access'"
                type="primary"
                @click="onSaveClinics"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{ $t("common.update") }}
            </a-button>
            <a-button
                v-else-if="
                    activeTab === 'schedule' && selectedClinicId !== 'all'
                "
                type="primary"
                @click="onSaveSchedule"
                style="margin-right: 8px"
                :loading="loading"
            >
                <template #icon> <SaveOutlined /> </template>
                {{ $t("doctors.save_schedule") }}
            </a-button>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, watch, ref, onMounted } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { notification } from "ant-design-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import CurrencyInput from "../../../../common/components/common/input/CurrencyInput.vue";
import ColorInput from "../../../../common/components/common/input/ColorInput.vue";
import SelectInput from "../../../../common/components/common/select/SelectInput.vue";
import PhoneSelect from "../../../../common/components/common/select/PhoneSelect.vue";
import Upload from "../../../../common/core/ui/file/Upload.vue";
import ScheduleTable from "../components/ScheduleTable.vue";
import HolidaysTable from "../components/HolidaysTable.vue";
import BreaksTable from "../components/BreaksTable.vue";
import useSchedule from "../components/useSchedule";
import UploadFileEmit from "../../../../common/core/ui/file/UploadFileEmit.vue";
import AddressForm from "../../../../common/components/common/address/AddressForm.vue";
import ClinicAccessManager from "../../users/ClinicAccessManager.vue";
import MultiClinicScheduleTabs from "../components/MultiClinicScheduleTabs.vue";

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
        "departments",
        "specialists",
        "selectedDoctorId",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        DateTimePicker,
        CurrencyInput,
        ColorInput,
        SelectInput,
        PhoneSelect,
        Upload,
        ScheduleTable,
        HolidaysTable,
        BreaksTable,
        UploadFileEmit,
        AddressForm,
        ClinicAccessManager,
        MultiClinicScheduleTabs,
    },
    setup(props, { emit }) {
        const { t } = useI18n();
        const { addEditRequestAdmin, addEditFileRequestAdmin, loading, rules } =
            apiAdmin();
        const { selectedClinicId } = common();

        const activeTab = ref("personal");
        const perPatientTimeMinutes = ref(30); // Default to 30 minutes
        const addressForm = ref({
            address_line_1: "",
            address_line_2: "",
            neighborhood: "",
            postal_code: "", // Fixed: changed from zip_code to postal_code to match AddressForm
            zip_code_id: undefined,
            city: "",
            municipality: "",
            state: "", // Fixed: changed from state_name to state to match AddressForm
            state_code: undefined,
            state_xid: undefined,
            country_code: "MX",
            country_name: "",
            country_xid: undefined,
        });

        const selectedClinics = ref([]);
        const defaultClinicId = ref(null);
        const allRoles = ref([]);

        const {
            days,
            schedule,
            isDayAvailable,
            getDisabledTimesForDay,
            handleDaySwitch,
            initializeSchedule,
            resetSchedule,
            getScheduleDataObject,
        } = useSchedule();

        // Helper functions for time conversion
        const timeToMinutes = (timeString) => {
            if (!timeString) return 0;
            const [hours, minutes] = timeString.split(":").map(Number);
            return hours * 60 + minutes;
        };

        const minutesToTime = (minutes) => {
            if (!minutes || minutes === 0) return "00:00:00";
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return `${hours.toString().padStart(2, "0")}:${mins.toString().padStart(2, "0")}:00`;
        };

        const onSubmit = () => {
            // Debug: Log addressForm data
            console.log(
                "DEBUG: addressForm.value in onSubmit:",
                addressForm.value,
            );

            // Compose addresses payload with correct field mappings
            props.formData.addresses = [
                {
                    address_line_1: addressForm.value.address_line_1 || "",
                    address_line_2: addressForm.value.address_line_2 || "",
                    neighborhood: addressForm.value.neighborhood || "",
                    postal_code: addressForm.value.postal_code || "", // Fixed: was zip_code
                    city: addressForm.value.city || "", // Added missing city field
                    state: addressForm.value.state || "", // Added missing state field
                    zip_code_id: addressForm.value.zip_code_id || undefined,
                    address_type: "home",
                    is_default: true,
                    status: true,
                },
            ];

            // Debug: Log addresses array
            console.log("DEBUG: Addresses array:", props.formData.addresses);

            // Keep legacy flat fields for backward compatibility
            props.formData.address =
                addressForm.value.address_line_1 || props.formData.address;
            props.formData.city = addressForm.value.city || props.formData.city;
            props.formData.state =
                addressForm.value.state || props.formData.state; // Fixed: was state_name
            props.formData.zip_code =
                addressForm.value.postal_code || props.formData.zip_code;
            // Convert minutes to time format before submission
            if (props.formData.per_patient_time_minutes) {
                props.formData.per_patient_time = minutesToTime(
                    props.formData.per_patient_time_minutes,
                );
            }

            // Process schedule data if on schedule tab
            if (activeTab.value === "schedule") {
                props.formData.schedule = getScheduleDataObject();
            }

            // Remove profile_image_url from formData before sending
            const filteredFormData = { ...props.formData };
            delete filteredFormData.profile_image_url;
            delete filteredFormData.professional_id_url;
            delete filteredFormData.per_patient_time_minutes; // Remove the minutes field

            // Add clinic access data
            filteredFormData.clinics = selectedClinics.value;
            if (defaultClinicId.value) {
                filteredFormData.default_clinic_id = defaultClinicId.value;
            }

            addEditFileRequestAdmin({
                url:
                    props.url +
                    (props.addEditType == "add" ? "/store" : "/update"),
                fieldTypes: {
                    json: ["user", "addresses", "clinics"],
                    file: ["profile_image", "professional_id"],
                },
                data: filteredFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onSaveSchedule = () => {
            // Convert minutes to time format before submission
            if (props.formData.per_patient_time_minutes) {
                props.formData.per_patient_time = minutesToTime(
                    props.formData.per_patient_time_minutes,
                );
            }

            // Determine if we're creating or updating a schedule
            const scheduleId =
                props.formData.doctor_schedules &&
                props.formData.doctor_schedules.length > 0
                    ? props.formData.doctor_schedules[0].xid
                    : null;

            // Process schedule data
            const scheduleData = {
                doctor_id: props.selectedDoctorId,
                per_patient_time: props.formData.per_patient_time,
                schedule: getScheduleDataObject(),
                _method: scheduleId ? "PUT" : "POST",
            };

            const url = scheduleId
                ? `doctor-schedules/${scheduleId}/update`
                : "doctor-schedules/store";

            addEditRequestAdmin({
                url: url,
                data: scheduleData,
                successMessage: scheduleId
                    ? "Schedule updated successfully"
                    : "Schedule created successfully",
                success: (res) => {
                    emit("addEditSuccess", props.formData.xid);
                },
            });
        };

        const handleClinicsUpdate = (clinics) => {
            selectedClinics.value = clinics;
        };

        const handleDefaultClinicUpdate = (id) => {
            defaultClinicId.value = id;
        };

        const onSaveClinics = () => {
            addEditRequestAdmin({
                url: `users/${props.formData.xid}/update-clinics`,
                data: {
                    clinics: JSON.stringify(selectedClinics.value),
                    default_clinic_id: defaultClinicId.value,
                },
                successMessage:
                    t("doctor_clinics.clinic_access_updated") ||
                    "Clinic access updated successfully",
                success: (res) => {
                    emit("addEditSuccess", props.formData.xid);
                },
            });
        };

        onMounted(() => {
            // Fetch roles for the clinic access manager if strictly needed, or pass empty array if only doctors
            // But the ClinicAccessManager needs roles to assign specific roles per clinic.
            // If I don't fetch roles, the dropdown will be empty.
            axiosAdmin.get("roles?limit=10000").then((response) => {
                allRoles.value = response.data;
            });
        });

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        watch(
            () => props.visible,
            (newValue) => {
                if (newValue) {
                    // Defensive: ensure specialties is an array before mapping to avoid runtime errors
                    let specialtiesArray = [];
                    try {
                        if (Array.isArray(props.formData.specialties)) {
                            specialtiesArray = props.formData.specialties;
                        } else if (
                            props.formData.specialties &&
                            typeof props.formData.specialties === "object"
                        ) {
                            // If it's an object (single item), wrap into array
                            specialtiesArray = [props.formData.specialties];
                        } else if (
                            typeof props.formData.specialties === "string"
                        ) {
                            // If it's a JSON string, try to parse
                            try {
                                const parsed = JSON.parse(
                                    props.formData.specialties,
                                );
                                specialtiesArray = Array.isArray(parsed)
                                    ? parsed
                                    : [parsed];
                            } catch (e) {
                                specialtiesArray = [];
                            }
                        } else {
                            specialtiesArray = [];
                        }
                    } catch (e) {
                        specialtiesArray = [];
                    }

                    Object.assign(props.formData, {
                        qualification: props.formData.qualification || "",
                        specialist: specialtiesArray.map((s) => s.xid),
                        practice_id: props.formData.practice_id || "",
                        appointment_charge:
                            props.formData.appointment_charge || "",
                        description: props.formData.description || "",
                        doctor_department_id:
                            props.formData.x_doctor_department_id || undefined,
                        per_patient_time: props.formData.per_patient_time || "",
                        ...props.formData.user,
                        country_code:
                            props.formData.country_code ||
                            props.formData.user?.country_code ||
                            "US",
                    });

                    // Prefill address from addresses array if present, else fallback to flat fields
                    // Check both props.formData.addresses and props.formData.user.addresses
                    const addressesArray =
                        props.formData.addresses ||
                        (props.formData.user &&
                            props.formData.user.addresses) ||
                        [];

                    console.log(
                        "Doctor AddEdit - addressesArray:",
                        addressesArray,
                    ); // Debug log

                    if (addressesArray.length > 0) {
                        const addr = addressesArray[0];
                        console.log("Doctor AddEdit - address data:", addr); // Debug log

                        addressForm.value.address_line_1 =
                            addr.address_line_1 || "";
                        addressForm.value.address_line_2 =
                            addr.address_line_2 || "";
                        addressForm.value.neighborhood =
                            addr.neighborhood || "";
                        addressForm.value.zip_code_id =
                            addr.zip_code_id || addr.x_zip_code_id || undefined;

                        // If zip_code is an object, extract nested fields
                        if (
                            addr.zip_code &&
                            typeof addr.zip_code === "object"
                        ) {
                            addressForm.value.postal_code =
                                addr.zip_code.code || "";
                            addressForm.value.city = addr.zip_code.city || "";
                            addressForm.value.municipality =
                                addr.zip_code.municipality || "";
                            // If zip_code has estado, extract state info
                            if (addr.zip_code.estado) {
                                addressForm.value.state =
                                    addr.zip_code.estado.nombre || "";
                                addressForm.value.state_code =
                                    addr.zip_code.estado.codigo || undefined;
                                addressForm.value.state_xid =
                                    addr.zip_code.estado.xid || undefined;
                            } else {
                                addressForm.value.state = "";
                                addressForm.value.state_code = undefined;
                                addressForm.value.state_xid = undefined;
                            }
                            // Force reactive update
                            addressForm.value = { ...addressForm.value };
                            // If zip_code has pais, extract country info
                            if (addr.zip_code.pais) {
                                addressForm.value.country_name =
                                    addr.zip_code.pais.nombre || "";
                                addressForm.value.country_code =
                                    addr.zip_code.pais.codigo ||
                                    addressForm.value.country_code;
                                addressForm.value.country_xid =
                                    addr.zip_code.pais.xid || undefined;
                            } else {
                                addressForm.value.country_name = "";
                                addressForm.value.country_code =
                                    addressForm.value.country_code;
                                addressForm.value.country_xid = undefined;
                            }
                        } else {
                            // fallback to primitive fields on address entry
                            console.log(
                                "Doctor AddEdit - using primitive fields for:",
                                addr,
                            ); // Debug log
                            addressForm.value.postal_code =
                                addr.postal_code || addr.zip_code || "";
                            addressForm.value.city = addr.city || "";
                            addressForm.value.municipality =
                                addr.municipality || "";
                            addressForm.value.state =
                                addr.state || addr.state_name || "";
                            addressForm.value.state_code =
                                addr.state_code || undefined;
                            addressForm.value.state_xid =
                                addr.state_xid || undefined;
                            addressForm.value.country_name =
                                addr.country_name || "";
                            addressForm.value.country_code =
                                addr.country_code ||
                                addressForm.value.country_code;
                            addressForm.value.country_xid =
                                addr.country_xid || undefined;

                            console.log(
                                "Doctor AddEdit - final addressForm.value:",
                                addressForm.value,
                            ); // Debug log

                            // Force reactive update by creating a new object
                            addressForm.value = { ...addressForm.value };
                            console.log(
                                "Doctor AddEdit - after reactive update:",
                                addressForm.value,
                            ); // Debug log
                        }
                    } else {
                        addressForm.value.address_line_1 =
                            props.formData.address || "";
                        addressForm.value.city = props.formData.city || "";
                        addressForm.value.state = props.formData.state || "";
                        addressForm.value.postal_code =
                            props.formData.postal_code ||
                            props.formData.zip_code ||
                            "";
                        addressForm.value.address_line_2 = "";
                        addressForm.value.neighborhood = undefined;
                        addressForm.value.zip_code_id = undefined;
                        addressForm.value.municipality = "";
                        addressForm.value.state_code = undefined;
                        addressForm.value.state_xid = undefined;
                        addressForm.value.country_name = "";
                        addressForm.value.country_code = "MX";
                        addressForm.value.country_xid = undefined;
                    }

                    // Initialize schedule if in edit mode
                    if (
                        props.addEditType === "edit" &&
                        props.formData.doctor_schedules &&
                        props.formData.doctor_schedules.length > 0
                    ) {
                        const doctorSchedule =
                            props.formData.doctor_schedules[0];
                        if (doctorSchedule.per_patient_time) {
                            props.formData.per_patient_time =
                                doctorSchedule.per_patient_time;
                            props.formData.per_patient_time_minutes =
                                timeToMinutes(doctorSchedule.per_patient_time);
                        }
                        if (doctorSchedule.schedule) {
                            initializeSchedule(doctorSchedule.schedule);
                        }
                    } else {
                        // Set default value for new records
                        props.formData.per_patient_time_minutes = 30;
                        props.formData.color = "#039be5";
                    }
                }
                // Reset on close
                if (!newValue) {
                    activeTab.value = "personal";
                    resetSchedule();
                    addressForm.value = {
                        address_line_1: "",
                        address_line_2: "",
                        neighborhood: undefined,
                        zip_code: "",
                        zip_code_id: undefined,
                        city: "",
                        municipality: "",
                        state_name: "",
                        state_code: undefined,
                        state_xid: undefined,
                        country_code: "MX",
                        country_name: "",
                        country_xid: undefined,
                    };
                }
            },
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            onSaveSchedule,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "64%",
            activeTab,
            days,
            schedule,
            handleDaySwitch,
            isDayAvailable,
            getDisabledTimesForDay,
            addressForm,
            handleClinicsUpdate,
            handleDefaultClinicUpdate,
            allRoles,
            onSaveClinics,
            selectedClinicId,
        };
    },
});
</script>
