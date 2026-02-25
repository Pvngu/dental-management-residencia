import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "landing-page-settings?fields=id,xid,landing_page_title,landing_page_description,landing_page_phone,landing_page_email,landing_page_address,landing_page_hero_image,landing_page_about_title,landing_page_about_description,landing_page_services_title,landing_page_services_description,landing_page_contact_title,landing_page_contact_description,landing_page_social_facebook,landing_page_social_instagram,landing_page_social_twitter,landing_page_social_youtube,landing_page_meta_title,landing_page_meta_description,landing_page_meta_keywords";
    const addEditUrl = "landing-page-settings";
    const { t } = useI18n();

    const initData = {
        landing_page_title: "",
        landing_page_description: "",
        landing_page_phone: "",
        landing_page_email: "",
        landing_page_address: "",
        landing_page_hero_image: "",
        landing_page_about_title: "",
        landing_page_about_description: "",
        landing_page_services_title: "",
        landing_page_services_description: "",
        landing_page_contact_title: "",
        landing_page_contact_description: "",
        landing_page_social_facebook: "",
        landing_page_social_instagram: "",
        landing_page_social_twitter: "",
        landing_page_social_youtube: "",
        landing_page_meta_title: "",
        landing_page_meta_description: "",
        landing_page_meta_keywords: "",
    };

    const columns = [
        {
            title: t("landing_page.setting"),
            dataIndex: "setting",
        },
        {
            title: t("landing_page.value"),
            dataIndex: "value",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "landing_page_title",
            value: t("landing_page.title"),
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
