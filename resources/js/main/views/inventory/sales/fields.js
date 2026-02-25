import { useI18n } from "vue-i18n";
import { ref, computed } from "vue";

const fields = () => {
    const url = "sales?fields=id,xid,sale_number,patient_id,user_id,sold_at,status,subtotal,tax,discount,total&order=id%20desc";
    const addEditUrl = "sales";
    const { t } = useI18n();
    const hashableColumns = ["patient_id", "user_id"];
    
    // For the AddEdit component
    const patients = ref([]);

    const initData = {
        patient_id: undefined,
        sale_number: "",
        invoice_number: "",
        date_of_issue: new Date().toISOString().split('T')[0],
        payment_due_on: new Date().toISOString().split('T')[0],
        sold_at: new Date().toISOString().split('T')[0],
        status: "pending",
        subtotal: 0,
        tax: 0,
        discount: 0,
        total: 0,
        sale_details: [],
    };

    // Columns for sales list (used by index.vue) - use computed to ensure reactive translations
    const columns = computed(() => [
        {
            title: t("sales.sale_number"),
            dataIndex: "sale_number",
        },
        {
            title: t("sales.sold_at"),
            dataIndex: "sold_at",
        },
        {
            title: t("sales.status"),
            dataIndex: "status",
        },
        {
            title: t("sales.subtotal"),
            dataIndex: "subtotal",
        },
        {
            title: t("sales.tax"),
            dataIndex: "tax",
        },
        {
            title: t("sales.discount"),
            dataIndex: "discount",
        },
        {
            title: t("sales.total"),
            dataIndex: "total",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ]);

    // Filters available in the generic Filters component for this page
    const filterableColumns = computed(() => [
        {
            key: "sale_number",
            value: t("sales.sale_number"),
        },
        {
            key: "status",
            value: t("sales.status"),
        },
    ]);

    const getPrefetchData = () => {
        // For now, return an empty promise - you can add patient fetching logic here later
        return Promise.resolve();
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        getPrefetchData,
        patients,
    };
};

export default fields;
