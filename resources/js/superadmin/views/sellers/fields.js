import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    const url = "sellers?fields=id,xid,user_id,x_user_id,commission_rate,status,created_at,user{id,xid,name,last_name,email,phone,profile_image_url,status,gender,date_of_birth,address}";
    const addEditUrl = "sellers";
    const hashableColumns = ['user_id'];

    const initData = {
        name: "",
        last_name: "",
        email: "",
        password: "",
        phone: "",
        address: "",
        gender: undefined,
        date_of_birth: undefined,
        status: "enabled",
        commission_rate: "",
        profile_image: undefined,
    };

    const columns = [
        {
            title: t("seller.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("seller.email"),
            dataIndex: "email",
        },
        {
            title: t("seller.phone"),
            dataIndex: "phone",
        },
        {
            title: t("seller.commission_rate"),
            dataIndex: "commission_rate",
        },
        {
            title: t("seller.status"),
            dataIndex: "status",
            key: "status",
        },
        {
            title: t("seller.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("seller.name")
        },
        {
            key: "email",
            value: t("seller.email")
        },
        {
            key: "phone",
            value: t("seller.phone")
        },
    ];

    return {
        url,
        initData,
        columns,
        filterableColumns,
        addEditUrl,
        hashableColumns,
    }
}

export default fields;
