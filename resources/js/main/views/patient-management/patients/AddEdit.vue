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
        <a-spin :spinning="detailLoading" tip="Loading patient details...">
            <a-tabs v-model:activeKey="activeTab">
                <a-tab-pane key="personal" :tab="$t('patients.personal_info')">
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
                                                formData.profile_image =
                                                    file.file;
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
                                            class="required"
                                        >
                                            <a-input
                                                v-model:value="
                                                    formData.last_name
                                                "
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
                                        >
                                            <a-radio-group
                                                v-model:value="formData.gender"
                                            >
                                                <a-radio value="male">{{
                                                    $t("user.male")
                                                }}</a-radio>
                                                <a-radio value="female">{{
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
                                            class="required"
                                        >
                                            <a-switch
                                                v-model:checked="
                                                    formData.status
                                                "
                                                checkedValue="enabled"
                                                uncheckedValue="disabled"
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <a-row :gutter="16">
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
                                                :countryCode="
                                                    formData.country_code
                                                "
                                                :disabled="loading"
                                                @onUpdate="
                                                    (data) => {
                                                        formData.phone =
                                                            data.phone;
                                                        formData.country_code =
                                                            data.country_code;
                                                    }
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
                                                    ? rules.date_of_birth
                                                          .message
                                                    : null
                                            "
                                            :validateStatus="
                                                rules.date_of_birth
                                                    ? 'error'
                                                    : null
                                            "
                                        >
                                            <DateTimePicker
                                                :isFutureDateDisabled="true"
                                                :dateTime="
                                                    formData.date_of_birth
                                                "
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
                                            :label="$t('patients.blood_type')"
                                            name="blood_type"
                                            :help="
                                                rules.blood_type
                                                    ? rules.blood_type.message
                                                    : null
                                            "
                                            :validateStatus="
                                                rules.blood_type
                                                    ? 'error'
                                                    : null
                                            "
                                        >
                                            <a-select
                                                v-model:value="
                                                    formData.blood_type
                                                "
                                                :placeholder="
                                                    $t(
                                                        'common.select_default_text',
                                                        [
                                                            $t(
                                                                'patients.blood_type',
                                                            ),
                                                        ],
                                                    )
                                                "
                                                style="width: 100%"
                                                :allowClear="true"
                                            >
                                                <a-select-option value="A+"
                                                    >A+</a-select-option
                                                >
                                                <a-select-option value="A-"
                                                    >A-</a-select-option
                                                >
                                                <a-select-option value="B+"
                                                    >B+</a-select-option
                                                >
                                                <a-select-option value="B-"
                                                    >B-</a-select-option
                                                >
                                                <a-select-option value="AB+"
                                                    >AB+</a-select-option
                                                >
                                                <a-select-option value="AB-"
                                                    >AB-</a-select-option
                                                >
                                                <a-select-option value="O+"
                                                    >O+</a-select-option
                                                >
                                                <a-select-option value="O-"
                                                    >O-</a-select-option
                                                >
                                            </a-select>
                                        </a-form-item>
                                    </a-col>
                                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                        <a-form-item
                                            :label="
                                                $t('patients.preferred_doctor')
                                            "
                                            name="preferred_doctor_id"
                                            :help="
                                                rules.preferred_doctor_id
                                                    ? rules.preferred_doctor_id
                                                          .message
                                                    : null
                                            "
                                            :validateStatus="
                                                rules.preferred_doctor_id
                                                    ? 'error'
                                                    : null
                                            "
                                        >
                                            <UserSelect
                                                @onChange="
                                                    (id) => {
                                                        formData.preferred_doctor_id =
                                                            id;
                                                    }
                                                "
                                                v-model:value="
                                                    formData.preferred_doctor_id
                                                "
                                                :userType="'doctors'"
                                                :placeholder="
                                                    $t(
                                                        'common.select_default_text',
                                                        [
                                                            $t(
                                                                'patients.preferred_doctor',
                                                            ),
                                                        ],
                                                    )
                                                "
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="6" :lg="6"></a-col>
                            <a-col :xs="24" :sm="24" :md="18" :lg="18">
                                <a-row :gutter="16">
                                    <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                        <a-form-item
                                            :label="$t('patients.ssn')"
                                            name="ssn"
                                            :help="
                                                rules.ssn
                                                    ? rules.ssn.message
                                                    : null
                                            "
                                            :validateStatus="
                                                rules.ssn ? 'error' : null
                                            "
                                        >
                                            <a-input
                                                v-model:value="formData.ssn"
                                                :placeholder="
                                                    $t(
                                                        'common.placeholder_default_text',
                                                        [$t('patients.ssn')],
                                                    )
                                                "
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                            </a-col>
                        </a-row>

                        <!-- Email & Password Section -->
                        <a-divider
                            orientation="left"
                            style="margin-top: 24px"
                            >{{ $t("user.login_information") }}</a-divider
                        >
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('user.email')"
                                    name="email"
                                    :help="
                                        rules.email ? rules.email.message : null
                                    "
                                    :validateStatus="
                                        rules.email ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.email"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('user.email')],
                                            )
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
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('doctors.password')],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <!-- How did you hear about us? Section -->
                        <a-divider
                            orientation="left"
                            style="margin-top: 24px"
                            >{{
                                $t("patients.marketing_information")
                            }}</a-divider
                        >
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.heard_from')"
                                    name="heard_from"
                                    :help="
                                        rules.heard_from
                                            ? rules.heard_from.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.heard_from ? 'error' : null
                                    "
                                >
                                    <a-select
                                        v-model:value="formData.heard_from"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t('patients.heard_from'),
                                            ])
                                        "
                                        style="width: 100%"
                                        @change="onHeardFromChange"
                                    >
                                        <a-select-option
                                            v-for="source in marketingSources"
                                            :key="source.value"
                                            :value="source.value"
                                        >
                                            {{ source.label }}
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.heard_from_channel')"
                                    name="heard_from_channel"
                                    :help="
                                        rules.heard_from_channel
                                            ? rules.heard_from_channel.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.heard_from_channel
                                            ? 'error'
                                            : null
                                    "
                                >
                                    <a-select
                                        v-model:value="
                                            formData.heard_from_channel
                                        "
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t(
                                                    'patients.heard_from_channel_placeholder',
                                                ),
                                            ])
                                        "
                                        mode="tags"
                                        style="width: 100%"
                                        :disabled="!formData.heard_from"
                                        optionLabelProp="label"
                                        @change="onChannelChange"
                                    >
                                        <a-select-option
                                            v-for="channel in channelOptions"
                                            :key="channel.value"
                                            :value="channel.value"
                                            :label="channel.label"
                                        >
                                            <div>
                                                <a-tag :color="channel.color">{{
                                                    channel.label
                                                }}</a-tag>
                                            </div>
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                        </a-row>

                        <!-- Emergency Contact Information Section -->
                        <a-divider
                            orientation="left"
                            style="margin-top: 24px"
                            >{{ $t("patients.emergency_contact") }}</a-divider
                        >
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <a-table
                                    :dataSource="emergencyContacts"
                                    :pagination="false"
                                    :bordered="true"
                                    size="small"
                                >
                                    <a-table-column
                                        key="name"
                                        :title="
                                            $t(
                                                'patients.emergency_contact_name',
                                            )
                                        "
                                        data-index="name"
                                        width="33%"
                                    >
                                        <template #default="{ record, index }">
                                            <a-input
                                                v-model:value="record.name"
                                                :placeholder="
                                                    $t(
                                                        'common.placeholder_default_text',
                                                        [
                                                            $t(
                                                                'patients.emergency_contact_name',
                                                            ),
                                                        ],
                                                    )
                                                "
                                            />
                                        </template>
                                    </a-table-column>
                                    <a-table-column
                                        key="phone"
                                        :title="
                                            $t(
                                                'patients.emergency_contact_phone',
                                            )
                                        "
                                        data-index="phone"
                                        width="33%"
                                    >
                                        <template #default="{ record }">
                                            <a-input
                                                v-model:value="record.phone"
                                                :placeholder="
                                                    $t(
                                                        'common.placeholder_default_text',
                                                        [
                                                            $t(
                                                                'patients.emergency_contact_phone',
                                                            ),
                                                        ],
                                                    )
                                                "
                                            />
                                        </template>
                                    </a-table-column>
                                    <a-table-column
                                        key="relation"
                                        :title="
                                            $t(
                                                'patients.emergency_contact_relation',
                                            )
                                        "
                                        data-index="relation"
                                        width="28%"
                                    >
                                        <template #default="{ record }">
                                            <a-input
                                                v-model:value="record.relation"
                                                :placeholder="
                                                    $t(
                                                        'common.placeholder_default_text',
                                                        [
                                                            $t(
                                                                'patients.emergency_contact_relation',
                                                            ),
                                                        ],
                                                    )
                                                "
                                            />
                                        </template>
                                    </a-table-column>
                                    <a-table-column
                                        key="action"
                                        :title="$t('common.action')"
                                        width="6%"
                                    >
                                        <template #default="{ index }">
                                            <a-button
                                                type="danger"
                                                size="small"
                                                @click="
                                                    removeEmergencyContact(
                                                        index,
                                                    )
                                                "
                                                v-if="
                                                    emergencyContacts.length > 1
                                                "
                                            >
                                                <template #icon
                                                    ><DeleteOutlined
                                                /></template>
                                            </a-button>
                                        </template>
                                    </a-table-column>
                                </a-table>
                                <a-button
                                    type="dashed"
                                    style="width: 100%; margin-top: 8px"
                                    @click="addEmergencyContact"
                                >
                                    <PlusOutlined />
                                    {{ $t("patients.add_emergency_contact") }}
                                </a-button>
                            </a-col>
                        </a-row>

                        <!-- Preferred Pharmacy Section -->
                        <a-divider
                            orientation="left"
                            style="margin-top: 24px"
                            >{{ $t("patients.preferred_pharmacy") }}</a-divider
                        >
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.pharmacy_name')"
                                    name="pharmacy_name"
                                    :help="
                                        rules.pharmacy_name
                                            ? rules.pharmacy_name.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.pharmacy_name ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.pharmacy_name"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('patients.pharmacy_name')],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.pharmacy_phone')"
                                    name="pharmacy_phone"
                                    :help="
                                        rules.pharmacy_phone
                                            ? rules.pharmacy_phone.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.pharmacy_phone ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.pharmacy_phone"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('patients.pharmacy_phone')],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>

                        <!-- Insurance Information Section -->
                        <a-divider
                            orientation="left"
                            style="margin-top: 24px"
                            >{{
                                $t("patients.insurance_information")
                            }}</a-divider
                        >

                        <!-- Primary Insurance Selection -->
                        <a-row
                            :gutter="16"
                            v-if="formData.has_secondary_insurance"
                        >
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <a-form-item
                                    :label="$t('patients.primary_insurance')"
                                    name="primary_insurance"
                                >
                                    <a-radio-group
                                        v-model:value="
                                            formData.primary_insurance
                                        "
                                    >
                                        <a-radio value="primary">{{
                                            $t("patients.first_insurance")
                                        }}</a-radio>
                                        <a-radio value="secondary">{{
                                            $t("patients.second_insurance")
                                        }}</a-radio>
                                    </a-radio-group>
                                </a-form-item>
                            </a-col>
                        </a-row>

                        <!-- First Insurance -->
                        <a-row>
                            <a-col :xs="24" :sm="24" :md="24" :lg="24">
                                <div class="insurance-header">
                                    <h4>
                                        {{
                                            formData.has_secondary_insurance
                                                ? $t("patients.first_insurance")
                                                : $t(
                                                      "patients.insurance_information",
                                                  )
                                        }}
                                    </h4>
                                    <a-form-item
                                        name="has_secondary_insurance"
                                        style="margin-bottom: 0"
                                    >
                                        <a-checkbox
                                            v-model:checked="
                                                formData.has_secondary_insurance
                                            "
                                            @change="onSecondaryInsuranceChange"
                                            >{{
                                                $t(
                                                    "patients.add_secondary_insurance",
                                                )
                                            }}</a-checkbox
                                        >
                                    </a-form-item>
                                </div>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.insurance_provider')"
                                    name="provider_id"
                                    :help="
                                        rules.provider_id
                                            ? rules.provider_id.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.provider_id ? 'error' : null
                                    "
                                >
                                    <a-select
                                        v-model:value="formData.provider_id"
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t(
                                                    'patients.insurance_provider',
                                                ),
                                            ])
                                        "
                                        :allowClear="true"
                                        style="width: 100%"
                                        optionFilterProp="title"
                                        show-search
                                    >
                                        <a-select-option
                                            v-for="provider in insuranceProviders"
                                            :key="provider.xid"
                                            :title="provider.name"
                                            :value="provider.xid"
                                        >
                                            {{ provider.name }}
                                        </a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.policy_holder_name')"
                                    name="policy_holder_name"
                                    :help="
                                        rules.policy_holder_name
                                            ? rules.policy_holder_name.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.policy_holder_name
                                            ? 'error'
                                            : null
                                    "
                                >
                                    <a-input
                                        v-model:value="
                                            formData.policy_holder_name
                                        "
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [
                                                    $t(
                                                        'patients.policy_holder_name',
                                                    ),
                                                ],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="
                                        $t('patients.relationship_to_holder')
                                    "
                                    name="relationship_to_holder"
                                    :help="
                                        rules.relationship_to_holder
                                            ? rules.relationship_to_holder
                                                  .message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.relationship_to_holder
                                            ? 'error'
                                            : null
                                    "
                                >
                                    <a-select
                                        v-model:value="
                                            formData.relationship_to_holder
                                        "
                                        :placeholder="
                                            $t('common.select_default_text', [
                                                $t(
                                                    'patients.relationship_to_holder',
                                                ),
                                            ])
                                        "
                                        style="width: 100%"
                                    >
                                        <a-select-option value="self">{{
                                            $t("patients.self")
                                        }}</a-select-option>
                                        <a-select-option value="spouse">{{
                                            $t("patients.spouse")
                                        }}</a-select-option>
                                        <a-select-option value="child">{{
                                            $t("patients.child")
                                        }}</a-select-option>
                                        <a-select-option value="other">{{
                                            $t("patients.other")
                                        }}</a-select-option>
                                    </a-select>
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.member_id')"
                                    name="member_id"
                                    :help="
                                        rules.member_id
                                            ? rules.member_id.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.member_id ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.member_id"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('patients.member_id')],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>
                        <a-row :gutter="16">
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.group_number')"
                                    name="group_number"
                                    :help="
                                        rules.group_number
                                            ? rules.group_number.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.group_number ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.group_number"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('patients.group_number')],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                            <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                <a-form-item
                                    :label="$t('patients.plan_type')"
                                    name="plan_type"
                                    :help="
                                        rules.plan_type
                                            ? rules.plan_type.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.plan_type ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="formData.plan_type"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('patients.plan_type')],
                                            )
                                        "
                                    />
                                </a-form-item>
                            </a-col>
                        </a-row>

                        <!-- Secondary Insurance -->
                        <template v-if="formData.has_secondary_insurance">
                            <a-divider
                                style="margin-top: 16px; margin-bottom: 16px"
                                >{{
                                    $t("patients.second_insurance")
                                }}</a-divider
                            >
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="
                                            $t('patients.insurance_provider')
                                        "
                                        name="secondary_provider_id"
                                        :help="
                                            rules.secondary_provider_id
                                                ? rules.secondary_provider_id
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.secondary_provider_id
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-select
                                            v-model:value="
                                                formData.secondary_provider_id
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.select_default_text',
                                                    [
                                                        $t(
                                                            'patients.insurance_provider',
                                                        ),
                                                    ],
                                                )
                                            "
                                            :allowClear="true"
                                            style="width: 100%"
                                            optionFilterProp="title"
                                            show-search
                                        >
                                            <a-select-option
                                                v-for="provider in insuranceProviders"
                                                :key="provider.xid"
                                                :title="provider.name"
                                                :value="provider.xid"
                                            >
                                                {{ provider.name }}
                                            </a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="
                                            $t('patients.policy_holder_name')
                                        "
                                        name="secondary_policy_holder_name"
                                        :help="
                                            rules.secondary_policy_holder_name
                                                ? rules
                                                      .secondary_policy_holder_name
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.secondary_policy_holder_name
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.secondary_policy_holder_name
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [
                                                        $t(
                                                            'patients.policy_holder_name',
                                                        ),
                                                    ],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="
                                            $t(
                                                'patients.relationship_to_holder',
                                            )
                                        "
                                        name="secondary_relationship_to_holder"
                                        :help="
                                            rules.secondary_relationship_to_holder
                                                ? rules
                                                      .secondary_relationship_to_holder
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.secondary_relationship_to_holder
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-select
                                            v-model:value="
                                                formData.secondary_relationship_to_holder
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.select_default_text',
                                                    [
                                                        $t(
                                                            'patients.relationship_to_holder',
                                                        ),
                                                    ],
                                                )
                                            "
                                            style="width: 100%"
                                        >
                                            <a-select-option value="self">{{
                                                $t("patients.self")
                                            }}</a-select-option>
                                            <a-select-option value="spouse">{{
                                                $t("patients.spouse")
                                            }}</a-select-option>
                                            <a-select-option value="child">{{
                                                $t("patients.child")
                                            }}</a-select-option>
                                            <a-select-option value="other">{{
                                                $t("patients.other")
                                            }}</a-select-option>
                                        </a-select>
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('patients.member_id')"
                                        name="secondary_member_id"
                                        :help="
                                            rules.secondary_member_id
                                                ? rules.secondary_member_id
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.secondary_member_id
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.secondary_member_id
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('patients.member_id')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                            <a-row :gutter="16">
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('patients.group_number')"
                                        name="secondary_group_number"
                                        :help="
                                            rules.secondary_group_number
                                                ? rules.secondary_group_number
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.secondary_group_number
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.secondary_group_number
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [
                                                        $t(
                                                            'patients.group_number',
                                                        ),
                                                    ],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                                    <a-form-item
                                        :label="$t('patients.plan_type')"
                                        name="secondary_plan_type"
                                        :help="
                                            rules.secondary_plan_type
                                                ? rules.secondary_plan_type
                                                      .message
                                                : null
                                        "
                                        :validateStatus="
                                            rules.secondary_plan_type
                                                ? 'error'
                                                : null
                                        "
                                    >
                                        <a-input
                                            v-model:value="
                                                formData.secondary_plan_type
                                            "
                                            :placeholder="
                                                $t(
                                                    'common.placeholder_default_text',
                                                    [$t('patients.plan_type')],
                                                )
                                            "
                                        />
                                    </a-form-item>
                                </a-col>
                            </a-row>
                        </template>
                    </a-form>
                </a-tab-pane>

                <a-tab-pane key="addresses" :tab="$t('address.addresses')">
                    <a-form layout="vertical">
                        <AddressManager
                            v-model="managedAddresses"
                            :rules="rules"
                            :userId="
                                formData.user
                                    ? formData.user.xid
                                    : data.user
                                      ? data.user.xid
                                      : ''
                            "
                        />
                    </a-form>
                </a-tab-pane>
            </a-tabs>
        </a-spin>
        <template #footer>
            <div class="flex justify-end w-full">
                <div>
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
                    <a-button @click="onClose">
                        {{ $t("common.cancel") }}
                    </a-button>
                </div>
            </div>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, watch, ref } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    DeleteOutlined,
    MedicineBoxOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";
import Upload from "../../../../common/core/ui/file/Upload.vue";
import DateTimePicker from "../../../../common/components/common/calendar/DateTimePicker.vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import { notification } from "ant-design-vue";
import UploadFileEmit from "../../../../common/core/ui/file/UploadFileEmit.vue";
import AddressManager from "./components/AddressManager.vue";
import UserSelect from "../../../../common/components/common/select/UserSelect.vue";
import common from "../../../../common/composable/common";
import PhoneSelect from "../../../../common/components/common/select/PhoneSelect.vue";

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
        DeleteOutlined,
        Upload,
        DateTimePicker,
        UploadFileEmit,
        AddressManager,
        UserSelect,
        PhoneSelect,
        MedicineBoxOutlined,
    },
    setup(props, { emit }) {
        const { addEditFileRequestAdmin, addEditRequestAdmin, loading, rules } =
            apiAdmin();
        const { permsArray } = common();
        const store = useStore();
        const activeTab = ref("personal");
        const channelOptions = ref([]);
        const emergencyContacts = ref([{ name: "", phone: "", relation: "" }]);
        const managedAddresses = ref([]);
        const detailLoading = ref(false);
        const insuranceProviders = ref([]);

        const currentDoctorId = ref(
            store.state.auth?.user?.doctor_id ||
                store.state.auth?.user?.xid ||
                "",
        );

        const addEmergencyContact = () => {
            emergencyContacts.value.push({ name: "", phone: "", relation: "" });
        };

        const removeEmergencyContact = (index) => {
            emergencyContacts.value.splice(index, 1);
        };

        const onSecondaryInsuranceChange = (e) => {
            const hasSecondary = e.target.checked;
            if (hasSecondary) {
                // When secondary insurance is added, set primary insurance selection
                props.formData.primary_insurance =
                    props.formData.primary_insurance || "primary";
            }
        };

        const { t } = useI18n();

        const marketingSources = [
            { value: "social_media", label: t("patients.social_media") },
            { value: "newspaper", label: t("patients.newspaper") },
            { value: "magazine", label: t("patients.magazine") },
            { value: "printed", label: t("patients.printed") },
            { value: "billboard", label: t("patients.billboard") },
            { value: "search_engine", label: t("patients.search_engine") },
            { value: "tv", label: t("patients.tv") },
            { value: "referrals", label: t("patients.referrals") },
            { value: "radio", label: t("patients.radio") },
            { value: "other", label: t("patients.other") },
        ];

        const marketingChannels = {
            social_media: [
                {
                    value: "facebook",
                    label: "Facebook",
                    category: "social_media",
                },
                {
                    value: "instagram",
                    label: "Instagram",
                    category: "social_media",
                },
                {
                    value: "twitter",
                    label: "Twitter",
                    category: "social_media",
                },
                {
                    value: "linkedin",
                    label: "LinkedIn",
                    category: "social_media",
                },
                { value: "tiktok", label: "TikTok", category: "social_media" },
            ],
            search_engine: [
                { value: "google", label: "Google", category: "search_engine" },
                { value: "bing", label: "Bing", category: "search_engine" },
                { value: "yahoo", label: "Yahoo", category: "search_engine" },
            ],
            tv: [
                { value: "local_tv", label: "Local TV", category: "tv" },
                { value: "national_tv", label: "National TV", category: "tv" },
                { value: "cable_tv", label: "Cable TV", category: "tv" },
            ],
            radio: [
                {
                    value: "local_radio",
                    label: "Local Radio",
                    category: "radio",
                },
                {
                    value: "national_radio",
                    label: "National Radio",
                    category: "radio",
                },
                {
                    value: "online_radio",
                    label: "Online Radio",
                    category: "radio",
                },
            ],
        };

        // Define colors for each category
        const categoryColors = {
            social_media: "#1890ff", // Blue
            search_engine: "#52c41a", // Green
            tv: "#722ed1", // Purple
            radio: "#fa8c16", // Orange
            newspaper: "#eb2f96", // Pink
            magazine: "#f5222d", // Red
            printed: "#fa541c", // Volcano
            billboard: "#faad14", // Gold
            referrals: "#13c2c2", // Cyan
            other: "#8c8c8c", // Gray
        };

        // Get all channel options for the select dropdown
        const getAllChannelOptions = () => {
            const allOptions = [];

            // Add options from marketingChannels object
            Object.keys(marketingChannels).forEach((category) => {
                marketingChannels[category].forEach((channel) => {
                    allOptions.push({
                        ...channel,
                        label: `${t(`patients.${category}`)}: ${channel.label}`,
                        color: categoryColors[category] || "#8c8c8c",
                    });
                });
            });

            // Add generic options for categories that don't have specific channels
            [
                "newspaper",
                "magazine",
                "printed",
                "billboard",
                "referrals",
                "other",
            ].forEach((category) => {
                allOptions.push({
                    value: category,
                    label: t(`patients.${category}`),
                    category: category,
                    color: categoryColors[category] || "#8c8c8c",
                });
            });

            return allOptions;
        };

        // Create a structured data format for channels organized by category
        const structureChannelData = (channels) => {
            const structured = {};

            // If channels is not an array, convert to array
            const channelsArray = Array.isArray(channels)
                ? channels
                : channels
                  ? [channels]
                  : [];

            channelsArray.forEach((channel) => {
                // Check if this is a structured channel (contains :)
                if (typeof channel === "string" && channel.includes(":")) {
                    const [categoryLabel, value] = channel.split(":", 2);
                    const trimmedCategoryLabel = categoryLabel.trim();
                    const trimmedValue = value.trim();

                    // Find the category value from the label
                    const categorySource = marketingSources.find(
                        (s) => s.label === trimmedCategoryLabel,
                    );
                    const categoryKey = categorySource
                        ? categorySource.value
                        : trimmedCategoryLabel
                              .toLowerCase()
                              .replace(/\s+/g, "_");

                    if (!structured[categoryKey]) {
                        structured[categoryKey] = [];
                    }
                    structured[categoryKey].push(trimmedValue);
                } else {
                    // Try to determine the category based on predefined channels
                    let found = false;
                    for (const [category, options] of Object.entries(
                        marketingChannels,
                    )) {
                        if (options.some((opt) => opt.value === channel)) {
                            if (!structured[category]) {
                                structured[category] = [];
                            }
                            structured[category].push(channel);
                            found = true;
                            break;
                        }
                    }

                    // If we couldn't identify the category, use the current selected category
                    if (!found && props.formData.heard_from) {
                        const category = props.formData.heard_from;
                        if (!structured[category]) {
                            structured[category] = [];
                        }
                        structured[category].push(channel);
                    }
                }
            });

            return structured;
        };

        // Handle when a channel is added or removed
        const onChannelChange = (values) => {
            // If it's a custom value (not in our options), format it properly
            if (values && values.length > 0) {
                const allOptionValues = channelOptions.value.map(
                    (opt) => opt.value,
                );

                // Find new values (custom entered by user)
                const customValues = values.filter(
                    (value) => !allOptionValues.includes(value),
                );

                if (customValues.length > 0) {
                    // Format new values with category prefix
                    const currentCategory =
                        props.formData.heard_from || "other";
                    const categoryLabel =
                        marketingSources.find(
                            (s) => s.value === currentCategory,
                        )?.label || currentCategory;

                    // Replace the custom values with properly formatted ones
                    const formattedValues = values.map((value) => {
                        if (customValues.includes(value)) {
                            // Only add the prefix if it doesn't already have one
                            if (!value.includes(":")) {
                                return `${categoryLabel}: ${value}`;
                            }
                        }
                        return value;
                    });

                    // Replace the value in the form
                    props.formData.heard_from_channel = formattedValues;

                    // Regenerate channel options to include new custom values
                    updateChannelOptions();
                }

                // Store structured data - this will now properly handle multiple categories
                props.formData.structured_channels = structureChannelData(
                    props.formData.heard_from_channel,
                );
            }
        };

        // Update channel options based on the current selection and previously selected values
        const updateChannelOptions = () => {
            if (!props.formData.heard_from) {
                // If no value is selected, show all options but disable the selector
                channelOptions.value = getAllChannelOptions();
                return;
            }

            // Get all channels for the selected category
            const categoryChannels =
                props.formData.heard_from in marketingChannels
                    ? marketingChannels[props.formData.heard_from].map(
                          (c) => c.value,
                      )
                    : [props.formData.heard_from];

            // Keep previously selected options
            const currentlySelected = props.formData.heard_from_channel || [];
            const selectedChannels = Array.isArray(currentlySelected)
                ? currentlySelected
                : [currentlySelected];

            // Create base options from predefined channels
            const baseOptions = getAllChannelOptions();

            // Add any custom channels that don't exist in the predefined options
            const customOptions = selectedChannels
                .filter(
                    (channel) =>
                        !baseOptions.some((opt) => opt.value === channel),
                )
                .map((channel) => {
                    // Handle both "Category: Channel" and simple channel formats
                    let categoryColor = "#8c8c8c";
                    let displayLabel = channel;

                    if (channel.includes(":")) {
                        const [categoryLabel, channelName] = channel.split(
                            ":",
                            2,
                        );
                        const trimmedCategoryLabel = categoryLabel.trim();
                        const categorySource = marketingSources.find(
                            (s) => s.label === trimmedCategoryLabel,
                        );
                        if (categorySource) {
                            categoryColor =
                                categoryColors[categorySource.value] ||
                                "#8c8c8c";
                        }
                        displayLabel = channel; // Keep the full "Category: Channel" format
                    } else {
                        // Simple channel format, use current category color
                        categoryColor =
                            categoryColors[props.formData.heard_from] ||
                            "#8c8c8c";
                    }

                    return {
                        value: channel,
                        label: displayLabel,
                        color: categoryColor,
                        category: props.formData.heard_from,
                    };
                });

            // Combine all options
            const allOptions = [...baseOptions, ...customOptions];

            // Filter to show only the current category + any previously selected options
            channelOptions.value = allOptions.filter(
                (option) =>
                    categoryChannels.includes(option.value) ||
                    selectedChannels.includes(option.value),
            );
        };

        const getCategoryColor = (category) => {
            // Try to match with a defined category
            for (const [key, source] of Object.entries(marketingSources)) {
                if (source.value === category || source.label === category) {
                    return categoryColors[source.value] || "#8c8c8c";
                }
            }
            return categoryColors[category] || "#8c8c8c";
        };

        const onHeardFromChange = (value) => {
            if (!value) {
                props.formData.heard_from_channel = undefined;
            }

            // Update the channel options
            updateChannelOptions();
        };

        const onSubmit = () => {
            // Add emergency contacts to form data
            props.formData.emergency_contacts = emergencyContacts.value;
            // Add addresses to form data
            props.formData.addresses = managedAddresses.value;

            // Remove profile_image_url from formData before sending
            const filteredFormData = {
                ...props.formData,
                _method: props.addEditType == "add" ? "POST" : "PUT",
            };
            delete filteredFormData.profile_image_url;

            addEditFileRequestAdmin({
                url:
                    props.url +
                    (props.addEditType == "add" ? "/store" : "/update"),
                fieldTypes: {
                    json: [
                        "emergency_contacts",
                        "heard_from_channel",
                        "structured_channels",
                        "user",
                        "addresses",
                    ],
                    file: ["profile_image"],
                },
                data: filteredFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            activeTab.value = "personal";
            // Reset emergency contacts
            emergencyContacts.value = [{ name: "", phone: "", relation: "" }];
            // Reset managed addresses
            managedAddresses.value = [];
            emit("closed");
        };

        // Fetch complete patient data when editing
        const fetchPatientDetails = async () => {
            detailLoading.value = true;
            try {
                const response = await axiosAdmin.get(
                    `${props.url}?fields=id,xid,allergies,updated_at,user,user:addresses,pharmacy_name,pharmacy_phone,blood_type,emergencyContacts,insurances,insurances:provider,media_channels,preferred_doctor_id,x_preferred_doctor_id`,
                );

                if (response && response.data) {
                    const patientData = response.data;

                    // Merge patient data with formData
                    Object.assign(props.formData, patientData);
                    Object.assign(props.formData, patientData.user);

                    // Initialize emergency contacts
                    if (
                        patientData.emergency_contacts &&
                        patientData.emergency_contacts.length > 0
                    ) {
                        emergencyContacts.value =
                            patientData.emergency_contacts;
                    } else {
                        emergencyContacts.value = [
                            { name: "", phone: "", relation: "" },
                        ];
                    }

                    // Initialize addresses
                    if (
                        patientData.user?.addresses &&
                        patientData.user.addresses.length > 0
                    ) {
                        managedAddresses.value = patientData.user?.addresses || [];
                    } else {
                        managedAddresses.value = [];
                    }

                    // Handle media_channels
                    if (patientData.media_channels) {
                        parseMediaChannels(patientData.media_channels);
                    }

                    if (patientData.x_preferred_doctor_id) {
                        props.formData.preferred_doctor_id =
                            patientData.x_preferred_doctor_id;
                    }

                    // Initialize insurance fields
                    if (
                        patientData.insurances &&
                        patientData.insurances.length > 0
                    ) {
                        // Sort insurances by ID to maintain consistent order
                        const sortedInsurances = [
                            ...patientData.insurances,
                        ].sort((a, b) => parseInt(a.id) - parseInt(b.id));

                        // First insurance (goes to primary form section)
                        const firstIns = sortedInsurances[0];
                        if (firstIns) {
                            props.formData.provider_id =
                                firstIns.x_provider_id || "";
                            props.formData.policy_holder_name =
                                firstIns.policy_holder_name || "";
                            props.formData.relationship_to_holder =
                                firstIns.relationship_to_holder || "self";
                            props.formData.member_id = firstIns.member_id || "";
                            props.formData.group_number =
                                firstIns.group_number || "";
                            props.formData.plan_type = firstIns.plan_type || "";

                            // Set radio button based on which insurance has is_primary=true
                            if (firstIns.is_primary) {
                                props.formData.primary_insurance = "primary";
                            }
                        }

                        // Second insurance (goes to secondary form section)
                        if (sortedInsurances.length > 1) {
                            const secondIns = sortedInsurances[1];
                            props.formData.has_secondary_insurance = true;
                            props.formData.secondary_provider_id =
                                secondIns.x_provider_id || "";
                            props.formData.secondary_policy_holder_name =
                                secondIns.policy_holder_name || "";
                            props.formData.secondary_relationship_to_holder =
                                secondIns.relationship_to_holder || "self";
                            props.formData.secondary_member_id =
                                secondIns.member_id || "";
                            props.formData.secondary_group_number =
                                secondIns.group_number || "";
                            props.formData.secondary_plan_type =
                                secondIns.plan_type || "";

                            // Set radio button if second insurance is marked as primary
                            if (secondIns.is_primary) {
                                props.formData.primary_insurance = "secondary";
                            }
                        }
                    }

                    // Initialize marketing channel options
                    updateChannelOptions();

                    // Initialize structured channel data if it doesn't exist
                    if (
                        !props.formData.structured_channels &&
                        props.formData.heard_from_channel
                    ) {
                        props.formData.structured_channels =
                            structureChannelData(
                                props.formData.heard_from_channel,
                            );
                    }
                }
            } catch (error) {
                console.error("Error fetching patient details:", error);
            } finally {
                detailLoading.value = false;
            }
        };

        // Initialize channel options on component setup
        channelOptions.value = getAllChannelOptions();

        // Fetch insurance providers
        const fetchInsuranceProviders = () => {
            axiosAdmin
                .get(
                    "insurance-providers?fields=id,xid,name&filters=is_active eq 1",
                )
                .then((response) => {
                    insuranceProviders.value = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching insurance providers:", error);
                });
        };

        // Load insurance providers on component setup
        fetchInsuranceProviders();

        watch(
            () => props.visible,
            (newValue) => {
                if (newValue) {
                    // If editing, fetch complete patient data
                    if (props.addEditType === "edit") {
                        fetchPatientDetails();
                    } else {
                        emergencyContacts.value = [
                            { name: "", phone: "", relation: "" },
                        ];
                        managedAddresses.value = [];
                    }
                } else {
                    activeTab.value = "personal";
                }
            },
        );

        // Parse media channels JSON into heard_from and heard_from_channel
        const parseMediaChannels = (mediaChannelsJson) => {
            if (!mediaChannelsJson) return;

            try {
                let mediaChannels;
                // Handle both string and object formats
                if (typeof mediaChannelsJson === "string") {
                    mediaChannels = JSON.parse(mediaChannelsJson);
                } else {
                    mediaChannels = mediaChannelsJson;
                }

                if (!mediaChannels || typeof mediaChannels !== "object") return;

                // Get all categories and channels
                const categories = Object.keys(mediaChannels);
                if (categories.length > 0) {
                    // If there's only one category, use it as heard_from
                    if (categories.length === 1) {
                        const primaryCategory = categories[0];
                        props.formData.heard_from = primaryCategory;

                        // Set the channels for this category
                        const channels = mediaChannels[primaryCategory];
                        if (Array.isArray(channels) && channels.length > 0) {
                            props.formData.heard_from_channel = channels;
                        }
                    } else {
                        // If there are multiple categories, convert all to the format category: channel
                        const allChannels = [];
                        categories.forEach((category) => {
                            const channels = mediaChannels[category];
                            if (Array.isArray(channels)) {
                                channels.forEach((channel) => {
                                    // Find the category label
                                    const categorySource =
                                        marketingSources.find(
                                            (s) => s.value === category,
                                        );
                                    const categoryLabel = categorySource
                                        ? categorySource.label
                                        : category;
                                    allChannels.push(
                                        `${categoryLabel}: ${channel}`,
                                    );
                                });
                            }
                        });

                        // Use the first category as heard_from but include all channels
                        const primaryCategory = categories[0];
                        props.formData.heard_from = primaryCategory;
                        props.formData.heard_from_channel = allChannels;
                    }

                    // Update UI with proper options
                    updateChannelOptions();
                }
            } catch (error) {
                console.error("Error parsing media_channels:", error);
            }
        };

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "64%",
            activeTab,
            managedAddresses,
            marketingSources,
            channelOptions,
            onHeardFromChange,
            onChannelChange,
            emergencyContacts,
            addEmergencyContact,
            removeEmergencyContact,
            onSecondaryInsuranceChange,
            getAllChannelOptions,
            updateChannelOptions,
            structureChannelData,
            parseMediaChannels,
            fetchPatientDetails,
            detailLoading,
            permsArray,
            currentDoctorId,
            insuranceProviders,
        };
    },
});
</script>

<style scoped>
.insurance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.insurance-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 500;
}
</style>
