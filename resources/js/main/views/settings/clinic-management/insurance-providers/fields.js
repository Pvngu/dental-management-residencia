import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "insurance-providers?fields=id,xid,name,payor_id,fax_number,phone_support,is_active&with=addresses.zipCode.state.country";
    const addEditUrl = "insurance-providers";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        name: "",
        payor_id: "",
        fax_number: "",
        phone_support: "",
        is_active: true,
    };

    const columns = [
        {
            title: t("insurance_providers.name"),
            dataIndex: "name",
        },
        {
            title: t("insurance_providers.payor_id"),
            dataIndex: "payor_id",
        },
        {
            title: t("insurance_providers.phone_support"),
            dataIndex: "phone_support",
        },
        {
            title: t("insurance_providers.is_active"),
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
            value: t("insurance_providers.name"),
        },
        {
            key: "payor_id",
            value: t("insurance_providers.payor_id"),
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
