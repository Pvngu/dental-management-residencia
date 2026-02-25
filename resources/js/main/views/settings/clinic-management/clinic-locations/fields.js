import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "clinic-locations-management";
    const addEditUrl = "clinic-locations";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        phone_number: "",
        email: "",
        status: true,
        logo: undefined,
    };

    const columns = [
        {
            title: t("common.name"),
            dataIndex: "name",
        },
        {
            title: t("clinic_location.phone_number"),
            dataIndex: "phone_number",
        },
        {
            title: t("common.email"),
            dataIndex: "email",
        },
        {
            title: t("common.status"),
            dataIndex: "status",
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
            key: "email",
            value: t("common.email"),
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
