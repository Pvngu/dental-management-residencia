import { useI18n } from "vue-i18n";
import { ref } from "vue";
const axiosAdmin = window.axiosAdmin;

const fields = () => {
    const url = "faxes?fields=id,xid,patient_id,insurance_provider_id,to_number,from_number,direction,status,file,file_url,file_name,transmitted_at,error_message,notes,created_at,created_by&with=patient,insuranceProvider,creator";
    const addEditUrl = "faxes";
    const { t } = useI18n();
    const hashableColumns = ["patient_id", "insurance_provider_id", "created_by"];

    const patients = ref([]);
    const insuranceProviders = ref([]);

    const initData = {
        patient_id: undefined,
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
            title: t("fax.patient"),
            dataIndex: "patient",
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

    const filterableColumns = [
        {
            key: "to_number",
            value: t("fax.to_number"),
        },
        {
            key: "from_number",
            value: t("fax.from_number"),
        },
        {
            key: "file_name",
            value: t("fax.file_name"),
        },
    ];

    const getPrefetchData = () => {
        const insuranceProvidersPromise = axiosAdmin.get("insurance-providers?fields=id,xid,name");
        
        return Promise.all([insuranceProvidersPromise]).then(
            ([insuranceProvidersResponse]) => {
                insuranceProviders.value = insuranceProvidersResponse.data;
            }
        );
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        insuranceProviders,
        getPrefetchData,
    };
};

export default fields;
