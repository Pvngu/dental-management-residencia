import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "emails?fields=id,xid,recipient,subject,body,status,sent_at,x_patient_id,x_sent_by_user_id,created_at,sentByUser";
    const addEditUrl = "emails";
    const { t } = useI18n();
    const hashableColumns = ["patient_id"];

    const initData = {
        patient_id: undefined,
        recipient: "",
        subject: "",
        body: "",
        status: "draft",
    };

    const columns = [
        {
            title: t("common.recipient"),
            dataIndex: "recipient",
            width: 200,
        },
        {
            title: t("common.subject"),
            dataIndex: "subject",
            width: 300,
        },
        {
            title: t("common.status"),
            dataIndex: "status",
            width: 100,
        },
        {
            title: t("common.sent_date"),
            dataIndex: "sent_at",
            width: 150,
        },
        {
            title: t("common.action"),
            dataIndex: "action",
            width: 100,
        },
    ];

    const filterableColumns = [
        {
            key: "status",
            value: t("common.status"),
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
