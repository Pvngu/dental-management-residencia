import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "room-types?fields=id,xid,name,description,is_active";
    const addEditUrl = "room-types";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        description: "",
        is_active: 1
    };

    const columns = [
        {
            title: t("common.name"),
            dataIndex: "name",
        },
        {
            title: t("common.description"),
            dataIndex: "description",
        },
        {
            title: t("common.status"),
            dataIndex: "is_active",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
        },
        {
            key: "description",
            value: t("common.description"),
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
