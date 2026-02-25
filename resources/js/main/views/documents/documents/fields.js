import { useI18n } from "vue-i18n";
import { ref } from "vue";

const documentTypes = ref([]);
const patients = ref([]);

const fields = () => {
    const url = "documents?fields=id,xid,title,document_type_id,patient_id,x_patient_id,uploaded_by,x_uploaded_by,notes,documentType{xid,name},patient:user{xid,name,last_name},uploadedBy{xid,name,last_name}";
    const addEditUrl = "documents";
    const { t } = useI18n();
    const hashableColumns = ["document_type_id", "patient_id"];

    const initData = {
        title: "",
        document_type_id: undefined,
        patient_id: undefined,
        uploaded_by: "",
        notes: "",
    };

    const columns = [
        {
            title: t("documents.title"),
            dataIndex: "title",
        },
        {
            title: t("documents.document_type_id"),
            dataIndex: "document_type",
        },
        {
            title: t("documents.patient_id"),
            dataIndex: "patient",
        },
        {
            title: t("documents.uploaded_by"),
            dataIndex: "uploaded_by",
        },
        {
            title: t("documents.notes"),
            dataIndex: "notes",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "title",
            value: t("documents.title"),
        },
    ];

    const getPrefetchData = () => {
        const docTypePromise = axiosAdmin.get("document-types/all");
        const patientPromise = axiosAdmin.get("patients");
        return Promise.all([docTypePromise, patientPromise]).then(([docTypeRes, patientRes]) => {
            documentTypes.value = docTypeRes.data;
            patients.value = patientRes.data;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        documentTypes,
        patients,
        getPrefetchData,
    };
};

export default fields;
