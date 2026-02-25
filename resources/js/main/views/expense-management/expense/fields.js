import { useI18n } from "vue-i18n";
import { ref } from "vue";

const expenseCategories = ref([]);

const getPrefetchData = () => {
    const categoryPromise = axiosAdmin.get("expense-categories?fields=id,xid,name,is_active");
    return Promise.all([categoryPromise]).then(([categoryResponse]) => {
        expenseCategories.value = categoryResponse.data;
    });
};

const fields = () => {
    const url = "expenses?fields=id,xid,amount,category_id,x_category_id,expense_for,payment_type,reference_number,date,notes,category";
    const addEditUrl = "expenses";
    const { t } = useI18n();
    const hashableColumns = ["category_id"];

    const paymentTypes = [
        { value: 'cash', label: 'Cash' },
        { value: 'card', label: 'Card' },
        { value: 'bank', label: 'Bank' },
        { value: 'other', label: 'Other' }
    ];

    const initData = {
        amount: "",
        category_id: undefined,
        expense_for: "",
        payment_type: "",
        reference_number: "",
        date: "",
        notes: "",
    };

    const columns = [
        {
            title: t("expenses.amount"),
            dataIndex: "amount",
        },
        {
            title: t("expenses.expense_for"),
            dataIndex: "expense_for",
        },
        {
            title: t("expenses.payment_type"),
            dataIndex: "payment_type",
        },
        {
            title: t("expenses.reference_number"),
            dataIndex: "reference_number",
        },
        {
            title: t("expenses.date"),
            dataIndex: "date",
        },
        {
            title: t("expenses.category_id"),
            dataIndex: "category",
        },
        {
            title: t("expenses.notes"),
            dataIndex: "notes",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "expense_for",
            value: t("expenses.expense_for"),
        },
        {
            key: "payment_type",
            value: t("expenses.payment_type"),
        },
        {
            key: "reference_number",
            value: t("expenses.reference_number"),
        },
    ];

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        expenseCategories,
        paymentTypes,
        getPrefetchData,
    };
};

export default fields;
