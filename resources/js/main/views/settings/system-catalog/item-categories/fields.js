import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "item-categories?fields=id,xid,name,description";
    const addEditUrl = "item_categories";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        description: "",
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
        hashableColumns,
    };
};

export default fields;
