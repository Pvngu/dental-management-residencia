import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "open-cases?fields=id,xid,title,description,priority,status,x_patient_id,created_at,updated_at,patient,patient:user{xid,id,name,last_name,phone},histories";
    const addEditUrl = "open-cases";
    const { t } = useI18n();
    const hashableColumns = ["patient_id"];

    const initData = {
        title: "",
        description: "",
        priority: "medium",
        status: "open",
        patient_id: undefined,
    };

    const columns = [
        {
            title: t("open_cases.title"),
            dataIndex: "title",
        },
        {
            title: t("open_cases.priority"),
            dataIndex: "priority",
        },
        {
            title: t("open_cases.status"),
            dataIndex: "status",
        },
        {
            title: t("common.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "title",
            value: t("open_cases.title"),
        },
        {
            key: "description",
            value: t("open_cases.description"),
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
    };
};

export default fields;
