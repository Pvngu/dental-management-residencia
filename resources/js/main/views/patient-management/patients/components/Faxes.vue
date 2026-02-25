<template>
    <a-card :bordered="false" class="patient-faxes-card">
        <template #extra>
            <a-button
                v-if="
                    permsArray.includes('faxes_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                @click="addItem"
            >
                <PlusOutlined />
                {{ $t("fax.send_fax") }}
            </a-button>
        </template>

        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            :patients="patients"
            :insuranceProviders="insuranceProviders"
        />

        <a-row class="mt-10">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="filters.direction"
                    @change="setUrlData"
                    type="card"
                    class="table-tab-filters"
                >
                    <a-tab-pane key="">
                        <template #tab>
                            <span>
                                <FileOutlined />
                                {{ $t("common.all") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="outbound">
                        <template #tab>
                            <span>
                                <SendOutlined />
                                {{ $t("fax.outbound") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="inbound">
                        <template #tab>
                            <span>
                                <InboxOutlined />
                                {{ $t("fax.inbound") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>

        <a-row class="mt-5">
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        bordered
                        size="middle"
                    >
                        <template #title>
                            <a-row justify="end" align="middle" class="table-header">
                                <a-col
                                    :xs="24"
                                    :sm="18"
                                    :md="16"
                                    :lg="12"
                                    :xl="10"
                                >
                                    <a-input-search
                                        style="width: 100%"
                                        v-model:value="table.searchString"
                                        :placeholder="$t('common.search')"
                                        show-search
                                        @search="onTableSearch"
                                        @change="onTableSearch"
                                        :loading="table.loading"
                                    />
                                </a-col>
                                <a-col class="ml-2">
                                    <Filters @onReset="resetFilters" :filters="filters">
                                        <a-col :span="24">
                                            <a-form-item :label="$t('fax.status')">
                                                <a-select
                                                    v-model:value="filters.status"
                                                    :placeholder="$t('common.select_default_text', [$t('fax.status')])"
                                                    :allowClear="true"
                                                    style="width: 100%"
                                                    @change="setUrlData"
                                                >
                                                    <a-select-option value="queued">
                                                        {{ $t("fax.status_queued") }}
                                                    </a-select-option>
                                                    <a-select-option value="sending">
                                                        {{ $t("fax.status_sending") }}
                                                    </a-select-option>
                                                    <a-select-option value="sent">
                                                        {{ $t("fax.status_sent") }}
                                                    </a-select-option>
                                                    <a-select-option value="failed">
                                                        {{ $t("fax.status_failed") }}
                                                    </a-select-option>
                                                    <a-select-option value="received">
                                                        {{ $t("fax.status_received") }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                        </a-col>
                                        <a-col :span="24">
                                            <a-form-item :label="$t('common.date')">
                                                <DateRangePicker
                                                    @dateTimeChanged="
                                                        (changedDateTime) => {
                                                            extraFilters.dates = changedDateTime;
                                                            setUrlData();
                                                        }
                                                    "
                                                />
                                            </a-form-item>
                                        </a-col>
                                    </Filters>
                                </a-col>
                            </a-row>
                        </template>
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex === 'created_at'">
                                {{ formatDateTime(record.created_at) }}
                            </template>
                            <template v-if="column.dataIndex === 'direction'">
                                <a-tag :color="record.direction === 'outbound' ? 'blue' : 'green'">
                                    <SendOutlined v-if="record.direction === 'outbound'" />
                                    <InboxOutlined v-else />
                                    {{ $t(`fax.${record.direction}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'status'">
                                <a-tag :color="getStatusColor(record.status)">
                                    {{ $t(`fax.status_${record.status}`) }}
                                </a-tag>
                            </template>
                            <template v-if="column.dataIndex === 'insurance_provider'">
                                <span v-if="record.insurance_provider">
                                    {{ record.insurance_provider.name }}
                                </span>
                                <span v-else>-</span>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-dropdown>
                                    <a-button type="text" shape="circle">
                                        <template #icon><EllipsisOutlined /></template>
                                    </a-button>
                                    <template #overlay>
                                        <a-menu>
                                            <a-menu-item
                                                v-if="record.file"
                                                @click="downloadFax(record.xid)"
                                            >
                                                <DownloadOutlined /> {{ $t("common.download") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    (permsArray.includes('faxes_resend') ||
                                                        permsArray.includes('admin')) &&
                                                    record.status === 'failed'
                                                "
                                                @click="resendFax(record.xid)"
                                            >
                                                <RedoOutlined /> {{ $t("fax.resend") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    permsArray.includes('faxes_edit') ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="editItem(record)"
                                            >
                                                <EditOutlined /> {{ $t("common.edit") }}
                                            </a-menu-item>
                                            <a-menu-item
                                                v-if="
                                                    permsArray.includes('faxes_delete') ||
                                                    permsArray.includes('admin')
                                                "
                                                @click="showDeleteConfirm(record.xid)"
                                            >
                                                <DeleteOutlined /> {{ $t("common.delete") }}
                                            </a-menu-item>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </a-card>
</template>

<script>
import { ref, onMounted } from "vue";
import { notification } from "ant-design-vue";
import { useI18n } from "vue-i18n";
import {
    PlusOutlined,
    DeleteOutlined,
    FileOutlined,
    SendOutlined,
    InboxOutlined,
    EditOutlined,
    EllipsisOutlined,
    DownloadOutlined,
    RedoOutlined,
} from "@ant-design/icons-vue";
import crud from '../../../../../common/composable/crud';
import common from "../../../../../common/composable/common";
import AddEdit from "../../../../views/fax-center/faxes/AddEdit.vue";
import Filters from "../../../../../common/components/common/select/Filters.vue";
import DateRangePicker from "../../../../../common/components/common/calendar/DateRangePicker.vue";
const axiosAdmin = window.axiosAdmin;

export default {
    props: ["patientXid"],
    components: {
        PlusOutlined,
        DeleteOutlined,
        FileOutlined,
        SendOutlined,
        InboxOutlined,
        EditOutlined,
        EllipsisOutlined,
        DownloadOutlined,
        RedoOutlined,
        AddEdit,
        Filters,
        DateRangePicker,
    },
    setup(props) {
        const { t } = useI18n();
        const { permsArray, formatDateTime } = common();
        const crudVariables = crud();

        const patients = ref([]);
        const insuranceProviders = ref([]);

        const filters = ref({
            direction: "",
            status: "",
        });

        const extraFilters = ref({
            dates: [],
            patient_id: props.patientXid,
        });

        const hashableColumns = ["patient_id", "insurance_provider_id", "created_by"];

        const initData = {
            patient_id: props.patientXid,
            insurance_provider_id: undefined,
            to_number: "",
            from_number: "",
            direction: "outbound",
            status: "queued",
            file: null,
            file_url: "",
            file_name: "",
            transmitted_at: null,
            error_message: "",
            notes: "",
        };

        const columns = [
            {
                title: t("fax.date"),
                dataIndex: "created_at",
            },
            {
                title: t("fax.direction"),
                dataIndex: "direction",
            },
            {
                title: t("fax.status"),
                dataIndex: "status",
            },
            {
                title: t("fax.to_number"),
                dataIndex: "to_number",
            },
            {
                title: t("fax.from_number"),
                dataIndex: "from_number",
            },
            {
                title: t("fax.insurance_provider"),
                dataIndex: "insurance_provider",
            },
            {
                title: t("common.action"),
                dataIndex: "action",
            },
        ];

        const getPrefetchData = () => {
            const patientsPromise = axiosAdmin.get("patients?fields=id,xid,user{first_name,last_name}");
            const insuranceProvidersPromise = axiosAdmin.get("insurance-providers?fields=id,xid,name");
            
            return Promise.all([patientsPromise, insuranceProvidersPromise]).then(
                ([patientsResponse, insuranceProvidersResponse]) => {
                    patients.value = patientsResponse.data;
                    insuranceProviders.value = insuranceProvidersResponse.data;
                }
            );
        };

        onMounted(() => {
            getPrefetchData().then(() => {
                crudVariables.crudUrl.value = "faxes";
                crudVariables.langKey.value = "fax";
                crudVariables.initData.value = { ...initData };
                crudVariables.formData.value = { ...initData };

                setUrlData();
            });
        });

        const setUrlData = () => {
            const url = "faxes?fields=id,xid,patient_id,insurance_provider_id,to_number,from_number,direction,status,file,file_url,file_name,transmitted_at,error_message,notes,created_at,created_by&with=patient,insuranceProvider,creator";
            
            crudVariables.tableUrl.value = {
                url,
                filters,
                extraFilters,
            };

            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.fetch({
                page: 1,
            });
        };

        const resetFilters = () => {
            filters.value = {
                direction: "",
                status: "",
            };
            extraFilters.value = {
                dates: [],
                patient_id: props.patientXid,
            };
            setUrlData();
        };

        const getStatusColor = (status) => {
            const colors = {
                queued: 'default',
                sending: 'processing',
                sent: 'success',
                failed: 'error',
                received: 'success',
            };
            return colors[status] || 'default';
        };

        const downloadFax = (xid) => {
            axiosAdmin
                .get(`faxes/${xid}/download`, {
                    responseType: 'blob',
                })
                .then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `fax_${xid}.pdf`);
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    notification.success({
                        message: t('common.success'),
                        description: t('fax.download_success'),
                    });
                })
                .catch((error) => {
                    notification.error({
                        message: t('common.error'),
                        description: t('fax.download_error'),
                    });
                });
        };

        const resendFax = (xid) => {
            axiosAdmin
                .post(`faxes/${xid}/resend`)
                .then((response) => {
                    notification.success({
                        message: t('common.success'),
                        description: t('fax.resend_success'),
                    });
                    setUrlData();
                })
                .catch((error) => {
                    notification.error({
                        message: t('common.error'),
                        description: t('fax.resend_error'),
                    });
                });
        };

        return {
            ...crudVariables,
            permsArray,
            columns,
            setUrlData,
            filters,
            extraFilters,
            resetFilters,
            formatDateTime,
            getStatusColor,
            downloadFax,
            resendFax,
            patients,
            insuranceProviders,
        };
    },
};
</script>

<style scoped>
.patient-faxes-card {
    margin-top: 0;
}
</style>
