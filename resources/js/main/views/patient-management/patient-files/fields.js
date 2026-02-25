import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "patient-files?fields=id,xid,patient_id,x_patient_id,name,file,file_url,file_type,file_size,created_at,creator,patient{xid,id},patient:user{xid,name,last_name,profile_image_url}";
    const addEditUrl = "patient-files";
    const { t } = useI18n();
    const hashableColumns = ["patient_id"];

    const initData = {
        name: "",
        patient_id: undefined,
        file: "",
        file_url: "",
        file_type: "",
        file_size: 0,
    };

    const columns = [
        {
            title: t("common.name"),
            dataIndex: "name",
        },
        {
            title: t("patient_files.patient"),
            dataIndex: "patient",
        },
        {
            title: t("patient_files.uploaded_by"),
            dataIndex: "uploaded_by",
        },
        {
            title: t("patient_files.size"),
            dataIndex: "file_size",
            width: 100,
        },
        {
            title: t("patient_files.uploaded_at"),
            dataIndex: "created_at",
            width: 100,
        },
        {
            title: t("common.action"),
            dataIndex: "action",
            width: 20,
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("common.name"),
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
