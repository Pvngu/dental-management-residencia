import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "patient-file-types?fields=id,xid,name,description";
    const addEditUrl = "patient-file-types";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        description: "",
    };

    const columns = [
        {
            title: t("patient_file_types.name"),
            dataIndex: "name",
        },
        {
            title: t("patient_file_types.description"),
            dataIndex: "description",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("patient_file_types.name"),
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
