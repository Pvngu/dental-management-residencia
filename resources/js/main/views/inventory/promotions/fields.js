import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "promotions?fields=id,xid,name,discount_type,discount_value,start_date,end_date,is_active,targets";
    const addEditUrl = "promotions";
    const { t } = useI18n();
    const hashableColumns = [];

    const items = ref([]);
    const itemCategories = ref([]);
    const itemBrands = ref([]);

    const initData = {
        name: "",
        discount_type: "percentage",
        discount_value: 0,
        start_date: "",
        end_date: "",
        is_active: true,
        promotion_targets: []
    };

    const columns = [
        {
            title: t("promotions.name"),
            dataIndex: "name",
        },
        {
            title: t("promotions.discount_type"),
            dataIndex: "discount_type",
        },
        {
            title: t("promotions.discount_value"),
            dataIndex: "discount_value",
        },
        {
            title: t("common.start_date"),
            dataIndex: "start_date",
        },
        {
            title: t("common.end_date"),
            dataIndex: "end_date",
        },
        {
            title: t("common.status"),
            dataIndex: "is_active",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        }
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("promotions.name"),
        },
        {
            key: "discount_type",
            value: t("promotions.discount_type"),
        },
        {
            key: "discount_value", 
            value: t("promotions.discount_value"),
        }
    ];

    const getPrefetchData = () => {
        const itemsPromise = axiosAdmin.get("items?limit=10000&fields=id,xid,name");
        const categoriesPromise = axiosAdmin.get("item-categories?limit=10000&fields=id,xid,name");
        const brandsPromise = axiosAdmin.get("item-brands?limit=10000&fields=id,xid,name");
        
        return Promise.all([itemsPromise, categoriesPromise, brandsPromise]).then(([itemsResponse, categoriesResponse, brandsResponse]) => {
            items.value = itemsResponse.data;
            itemCategories.value = categoriesResponse.data;
            itemBrands.value = brandsResponse.data;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        items,
        itemCategories,
        itemBrands,
        getPrefetchData
    };
};

export default fields;
