import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "invoices?fields=id,xid,invoice_number,date_of_issue,payment_due_on,status,total_payable,patient_id,email,phone_number,person_name";
    const addEditUrl = "invoices";
    const { t } = useI18n();
    const hashableColumns = ["patient_id"];
    const patients = ref([]);

    const initData = {
        invoice_number: "",
        patient_id: undefined,
        date_of_issue: "",
        payment_due_on: "",
        status: "pending",
        company_name: "",
        person_name: "",
        email: "",
        phone_number: "",
        subtotal: 0,
        tax: 0,
        discount: 0,
        total_payable: 0,
        invoice_details: []
    };

    const columns = [
        {
            title: t("invoices.invoice_number"),
            dataIndex: "invoice_number",
        },
        {
            title: t("invoices.date"),
            dataIndex: "date_of_issue",
        },
        {
            title: t("invoices.patient"),
            dataIndex: "person_name",
        },
        {
            title: t("invoices.status"),
            dataIndex: "status",
        },
        {
            title: t("invoices.total"),
            dataIndex: "total_payable",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        }
    ];

    const filterableColumns = [
        {
            key: "invoice_number",
            value: t("invoices.invoice_number"),
        },
        {
            key: "status",
            value: t("invoices.status"),
        },
        {
            key: "person_name",
            value: t("invoices.patient"),
        },
    ];

    const statusOptions = [
        { value: "pending", label: t("invoices.status_pending") },
        { value: "paid", label: t("invoices.status_paid") },
        { value: "overdue", label: t("invoices.status_overdue") },
        { value: "cancelled", label: t("invoices.status_cancelled") },
    ];

    const getPrefetchData = () => {
        const patientsPromise = axiosAdmin.get("patients?fields=id,xid,name,email,phone");
        return Promise.all([patientsPromise]).then(([patientsResponse]) => {
            patients.value = patientsResponse.data;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        statusOptions,
        patients,
        getPrefetchData
    };
};

export default fields;
