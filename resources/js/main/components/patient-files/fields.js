import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "patient-files?fields=id,xid,patient_id,x_patient_id,name,file,file_url,file_type,file_size,created_at,creator";
    const hashableColumns = ["patient_id"];
    const addEditUrl = "patient-files";
    
    const { t } = useI18n();
    
    const initData = {
        name: "",
        file_path: "",
        file_type: "",
        file_size: "",
    };

    const columns = [
        { 
            title: t("common.name"), 
            dataIndex: "name", 
            key: "name" 
        },
        {
            title: t("patient_files.uploaded_by"),
            dataIndex: "uploaded_by",
            key: "uploaded_by",
        },
        { 
            title: t("patient_files.size"), 
            dataIndex: "file_size", 
            key: "file_size",
            width: 100,
        },
        {
            title: t("patient_files.uploaded_at"),
            dataIndex: "created_at",
            key: "created_at",
            width: 100,
        },
        { 
            dataIndex: "action", 
            key: "action", 
            width: 20 
        },
    ];

    const filterableColumns = [
        { 
            title: t("common.name"), 
            dataIndex: "name", 
            key: "name" 
        },
        {
            title: t("patient_files.uploaded_by"),
            dataIndex: "uploaded_by",
            key: "uploaded_by",
        },
        {
            title: t("patient_files.uploaded_at"),
            dataIndex: "created_at",
            key: "created_at",
        },
    ]

    return {
        url,
        addEditUrl,
        initData,
        columns,
        hashableColumns,
        filterableColumns,
    };
};

export default fields;
