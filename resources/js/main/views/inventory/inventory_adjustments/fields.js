import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "inventory-adjustments?fields=id,xid,reference_number,date,description,x_adjustments_reason_id,adjustmentReason,created_at,adjustmentReason{xid,name},adjustmentItems{xid,id,quantity_adjusted,x_item_id},adjustmentItems:item{xid,available_quantity}";
    const addEditUrl = "inventory-adjustments";
    const { t } = useI18n();
    const hashableColumns = ["adjustments_reason_id"];
    const adjustmentReasons = ref([]);

    const initData = {
        reference_number: "",
        date: new Date().toISOString(),
        adjustments_reason_id: undefined,
        description: "",
        adjustment_items: [],
    };

    const columns = [
        {
            title: t("inventory_adjustment.reference_number"),
            dataIndex: "reference_number",
        },
        {
            title: t("inventory_adjustment.date"),
            dataIndex: "date",
        },
        {
            title: t("inventory_adjustment.reason"),
            dataIndex: "reason",
        },
        {
            title: t("common.description"),
            dataIndex: "description",
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

    const itemColumns = [
        {
            title: t("inventory_adjustment.items"),
            dataIndex: "item_id",
            width: "35%",
        },
        {
            title: t("inventory_adjustment.current_quantity"),
            dataIndex: "current_quantity",
            width: "20%",
        },
        {
            title: t("inventory_adjustment.new_quantity_on_hand"),
            dataIndex: "new_quantity",
            width: "20%",
        },
        {
            title: t("inventory_adjustment.quantity_adjusted"),
            dataIndex: "adjustment_quantity",
            width: "20%",
        },
        {
            title: "Action",
            dataIndex: "action",
            width: "5%",
        },
    ];

    const filterableColumns = [
        {
            key: "reference_number",
            value: t("inventory_adjustment.reference_number"),
        }
    ];

    const getPrefetchData = () => {
        const reasonsPromise = axiosAdmin.get("adjustments-reason/all");
        
        return Promise.all([reasonsPromise]).then(([reasonsResponse]) => {
            adjustmentReasons.value = reasonsResponse.data;
        });
    }

    return {
        url,
        addEditUrl,
        initData,
        columns,
        itemColumns,
        filterableColumns,
        hashableColumns,
        adjustmentReasons,
        getPrefetchData,
    };
};

export default fields;