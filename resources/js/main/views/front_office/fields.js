import { useI18n } from "vue-i18n";
import { ref } from "vue";
const axiosAdmin = window.axiosAdmin;

const fields = () => {
    const url = "postals?fields=id,xid,patient_id,postal_type,reference_no,date,mail_type_id,courier_method,tracking_number,status,file,created_at,mailType,sender_name,sender{xid,id,name,last_name},receivedBy{xid,id,name,last_name}";
    const addEditUrl = "postals";
    const { t } = useI18n();
    const hashableColumns = ["patient_id", "created_by", "mail_type_id", "received_by", "sender_by"];

    const patients = ref([]);

    const initData = {
        patient_id: undefined,
        postal_type: "received",
        sender_name: "",
        organization: "",
        sender_by: undefined,
        reference_no: "",
        date: null,
        address: [],
        received_by: undefined,
        assigned_to: undefined,
        mail_type_id: undefined,
        courier_method: "",
        tracking_number: "",
        status: "Pending",
        file: null,
    };

    const receivedColumns = [
        {
            title: t("mail_management.sender_name"),
            dataIndex: "sender_name",
        },
        {
            title: t("mail_management.organization"),
            dataIndex: "organization",
        },
        {
            title: t("mail_management.mail_type"),
            dataIndex: "mail_type",
        },
        {
            title: t("mail_management.received_by"),
            dataIndex: "received_by",
        },
        {
            title: t("mail_management.assigned_to"),
            dataIndex: "assigned_to",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const dispatchedColumns = [
        {
            title: t("mail_management.reference_no"),
            dataIndex: "reference_no",
        },
        {
            title: t("mail_management.recipient"),
            dataIndex: "patient",
        },
        {
            title: t("mail_management.address"),
            dataIndex: "address",
        },
        {
            title: t("mail_management.mail_type"),
            dataIndex: "mail_type",
        },
        {
            title: t("mail_management.date_sent"),
            dataIndex: "date",
        },
        {
            title: t("mail_management.sent_by"),
            dataIndex: "sender"
        },
        {
            title: t("mail_management.courier"),
            dataIndex: "courier_method",
        },
        {
            title: t("mail_management.tracking_number"),
            dataIndex: "tracking_number",
        },
        {
            title: t("mail_management.status"),
            dataIndex: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "reference_no",
            value: t("mail_management.reference_no"),
        },
        {
            key: "tracking_number",
            value: t("mail_management.tracking_number"),
        },
    ];

    const mailTypes = ref([]);

    const getPrefetchData = () => {
        const patientsPromise = axiosAdmin.get("patients?fields=id,xid,user_id&with=user");
        const mailTypesPromise = axiosAdmin.get("mail-types/all");

        return Promise.all([patientsPromise, mailTypesPromise]).then(
            ([patientsResponse, mailTypesResponse]) => {
                patients.value = patientsResponse.data;
                mailTypes.value = mailTypesResponse.data;
            }
        );
    };

    return {
        url,
        addEditUrl,
        initData,
        receivedColumns,
        dispatchedColumns,
        filterableColumns,
        hashableColumns,
        patients,
        mailTypes,
        getPrefetchData,
    };
};

export default fields;