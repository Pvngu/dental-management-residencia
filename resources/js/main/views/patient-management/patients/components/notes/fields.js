import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "patient-notes?fields=id,xid,patient_id,x_patient_id,user_id,x_user_id,related_type,related_id,x_related_id,note_type,title,content,is_private,is_highlighted,created_at,updated_at,user{xid,name}";
    const addEditUrl = "patient-notes";
    const { t } = useI18n();
    const hashableColumns = ["patient_id", "user_id", "related_id"];
    
    const noteTypes = ref([
        { key: "general", value: t("patient_notes.general") },
        { key: "medical", value: t("patient_notes.medical") },
        { key: "treatment", value: t("patient_notes.treatment") },
        { key: "insurance", value: t("patient_notes.insurance") },
        { key: "administrative", value: t("patient_notes.administrative") },
        { key: "billing", value: t("patient_notes.billing") },
    ]);

    const initData = {
        patient_id: undefined,
        user_id: undefined,
        related_type: "",
        related_id: undefined,
        note_type: "general",
        title: "",
        content: "",
        is_private: false,
        is_highlighted: false,
    };

    const columns = [
        {
            title: t("patient_notes.title"),
            dataIndex: "title",
        },
        {
            title: t("patient_notes.note_type"),
            dataIndex: "note_type",
        },
        {
            title: t("patient_notes.user"),
            dataIndex: "user_id",
        },
        {
            title: t("common.created_at"),
            dataIndex: "created_at",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "title",
            value: t("patient_notes.title"),
        },
        {
            key: "content",
            value: t("patient_notes.content"),
        },
        {
            key: "note_type",
            value: t("patient_notes.note_type"),
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        noteTypes,
    };
};

export default fields;
