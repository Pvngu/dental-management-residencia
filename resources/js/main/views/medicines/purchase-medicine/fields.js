import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "purchase-medicines?fields=id,xid,reference_no,delivery_date,payment_type,payment_status,subtotal,discount,tax,adjustments,total,note,orderMedicines{purchase_medicine_id,x_medicine_id,lot_no,expiry_date,quantity,amount,rate,company_id}orderMedicines:medicine";
    const addEditUrl = "purchase-medicines";
    const { t } = useI18n();
    const hashableColumns = ["medicine_id"];
    const medicines = ref([]);

    const paymentTypes = ref([
        { key: "cash", value: t("purchase_medicine.cash") },
        { key: "card", value: t("purchase_medicine.card") },
        { key: "cheque", value: t("purchase_medicine.cheque") },
        { key: "bank_transfer", value: t("purchase_medicine.bank_transfer") },
    ]);

    const paymentStatuses = ref([
        { key: "paid", value: t("purchase_medicine.paid") },
        { key: "unpaid", value: t("purchase_medicine.unpaid") },
        { key: "partial", value: t("purchase_medicine.partial") },
    ]);

    const initData = {
        reference_no: "",
        delivery_date: "",
        discount_type: "fixed",
        payment_type: "cash",
        payment_status: "paid",
        order_medicines: [],
        subtotal: 0,
        discount: 0,
        tax: 0,
        adjustments: 0,
        total: 0,
        note: "",
    };

    const columns = [
        {
            title: t("purchase_medicine.reference_no"),
            dataIndex: "reference_no",
        },
        {
            title: t("purchase_medicine.delivery_date"),
            dataIndex: "delivery_date",
        },
        {
            title: t("purchase_medicine.payment_type"),
            dataIndex: "payment_type",
        },
        {
            title: t("purchase_medicine.payment_status"),
            dataIndex: "payment_status",
        },
        {
            title: t("purchase_medicine.total"),
            dataIndex: "total",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const medicineColumns = [
        {
            title: t('purchase_medicine.medicine'),
            dataIndex: 'medicine_id',
            key: 'medicine_id',
            width: '25%',
        },
        {
            title: t('purchase_medicine.lot_no'),
            dataIndex: 'lot_no',
            key: 'lot_no',
            width: '15%',
        },
        {
            title: t('purchase_medicine.expiry_date'),
            dataIndex: 'expiry_date',
            key: 'expiry_date',
            width: '15%',
        },
        {
            title: t('purchase_medicine.quantity'),
            dataIndex: 'quantity',
            key: 'quantity',
            width: '10%',
        },
        {
            title: t('purchase_medicine.rate'),
            dataIndex: 'rate',
            key: 'rate',
            width: '15%',
        },
        {
            title: t('purchase_medicine.amount'),
            dataIndex: 'amount',
            key: 'amount',
            width: '15%',
        },
        {
            title: t('common.action'),
            dataIndex: 'action',
            key: 'action',
            width: '5%',
        },
    ];

    const filterableColumns = [
        {
            key: "reference_no",
            value: t("purchase_medicine.reference_no"),
        },
        {
            key: "payment_type",
            value: t("purchase_medicine.payment_type"),
        },
        {
            key: "payment_status",
            value: t("purchase_medicine.payment_status"),
        },
    ];

    const getPrefetchData = () => {
        const medicinesPromise = axiosAdmin.get("medicines?fields=id,xid,salt_composition,side_effects,item{xid,name,cost_price}&limit=1000");
        return Promise.all([medicinesPromise]).then(([medicinesResponse]) => {
            medicines.value = medicinesResponse.data.map(medicine => ({
                xid: medicine.xid,
                name: medicine.item?.name || '',
                buying_price: medicine.item?.cost_price || 0,
                sku: medicine.item?.sku || '',
            }));
        });
    }

    return {
        url,
        addEditUrl,
        initData,
        columns,
        medicineColumns,
        filterableColumns,
        hashableColumns,
        paymentTypes,
        paymentStatuses,
        medicines,
        getPrefetchData,
    };
};

export default fields;