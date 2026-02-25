import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "doctor-departments?fields=id,xid,name,description,created_at";
    const addEditUrl = "doctor-departments";
    const { t } = useI18n();

    const initData = {
        name: "",
        description: "",
    };

    const columns = [
        {
            title: t("doctor_departments.department"),
            dataIndex: "name",
        },
        {
            title: t("common.description"),
            dataIndex: "description",
            width: "64%",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("doctor_departments.department"),
        }
    ]

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
    }
}

export default fields;
