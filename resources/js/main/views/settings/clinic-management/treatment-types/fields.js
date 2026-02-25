import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "treatment_types?fields=id,xid,name,description,duration_minutes,price,is_active,category";
    const addEditUrl = "treatment_types";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        description: "",
        duration_minutes: null,
        price: 0,
        is_active: true,
        category: "Preventive",
    };

    const columns = [
        {
            title: t("treatment_types.name"),
            dataIndex: "name",
        },
        {
            title: t("treatment_types.category"),
            dataIndex: "category",
        },
        {
            title: t("treatment_types.duration"),
            dataIndex: "duration_minutes",
        },
        {
            title: t("treatment_types.price"),
            dataIndex: "price",
        },
        {
            title: t("treatment_types.status"),
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
            value: t("treatment_types.name")
        },
        {
            key: "category",
            value: t("treatment_types.category")
        },
    ];

    const categoryOptions = [
        "Preventive",
        "Restorative",
        "Surgical",
        "Orthodontic",
        "Diagnostic",
        "Consultation"
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        categoryOptions
    };
};

export default fields;
