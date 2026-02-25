import { useI18n } from "vue-i18n";

const fields = () => {
    const addEditUrl = "superadmin/companies";
    const { t } = useI18n();
    const multiDimensalObjectColumns = {
        'user_email': 'admin.email',
    };

    const initData = {
        name: "",
        short_name: "",
        email: "",
        phone: "",
        address: "",
        dark_logo: undefined,
        dark_logo_url: undefined,
        light_logo: undefined,
        light_logo_url: undefined,
        small_light_logo: undefined,
        small_light_logo_url: undefined,
        small_dark_logo: undefined,
        small_dark_logo_url: undefined,
        timezone: 'Asia/Kolkata',
        status: "active",
        enable_landing_page: 0,
        max_clinic_locations: 1,
        user_email: "",
        user_password: "",
        grace_period: 0,
    };

    const columns = [
        {
            title: t("company.logo"),
            dataIndex: "logo",
        },
        {
            title: t("company.name"),
            dataIndex: "name",
        },
        {
            title: t("company.email"),
            dataIndex: "email",
        },
        {
            title: t("company.max_clinic_locations"),
            dataIndex: "max_clinic_locations",
        },
        {
            title: t("company.grace_period"),
            dataIndex: "grace_period",
        },
        {
            title: t("company.details"),
            dataIndex: "details",
        },
        {
            title: t("subscription_plans.subscription_plan"),
            dataIndex: "subscription_plan",
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
            value: t("company.name")
        },
        {
            key: "max_clinic_locations",
            value: t("company.max_clinic_locations")
        },
    ];

    return {
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        multiDimensalObjectColumns
    }
}

export default fields;
