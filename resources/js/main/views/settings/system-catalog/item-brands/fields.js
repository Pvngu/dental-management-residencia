import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "item-brands?fields=id,xid,name";
    const addEditUrl = "item-brands";
    const { t } = useI18n();

    const initData = {
        name: "",
    };

    const columns = [
        {
            title: t("common.name"),
            dataIndex: "name",
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
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
    };
};

export default fields;
