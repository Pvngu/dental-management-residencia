import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "expense-categories?fields=id,xid,name,description,is_active";
    const addEditUrl = "expense-categories";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        description: "",
        is_active: true,
    };

    const columns = [
        {
            title: t("expense_categories.name"),
            dataIndex: "name",
        },
        {
            title: t("expense_categories.description"),
            dataIndex: "description",
        },
        {
            title: t("expense_categories.is_active"),
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
            value: t("expense_categories.name"),
        },
        {
            key: "is_active",
            value: t("expense_categories.is_active"),
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
