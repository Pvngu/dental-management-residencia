import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "superadmin/questionnaire-templates?fields=id,xid,code,version,name,description,is_active,is_evergreen,normative_ref,target_population,estimated_duration";
    const addEditUrl = "superadmin/questionnaire-templates";
    const { t } = useI18n();
    const hashableColumns = [];

    const initData = {
        code: "",
        version: "",
        name: "",
        description: "",
        is_active: true,
        is_evergreen: false,
        normative_ref: "",
        target_population: "ALL",
        estimated_duration: null,
    };

    const columns = [
        {
            title: t("questionnaire_templates.code"),
            dataIndex: "code",
        },
        {
            title: t("questionnaire_templates.version"),
            dataIndex: "version",
        },
        {
            title: t("questionnaire_templates.name"),
            dataIndex: "name",
        },
        {
            title: t("questionnaire_templates.target_population"),
            dataIndex: "target_population",
        },
        {
            title: t("questionnaire_templates.is_active"),
            dataIndex: "is_active",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "code",
            value: t("questionnaire_templates.code"),
        },
        {
            key: "name",
            value: t("questionnaire_templates.name"),
        },
        {
            key: "target_population",
            value: t("questionnaire_templates.target_population"),
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
