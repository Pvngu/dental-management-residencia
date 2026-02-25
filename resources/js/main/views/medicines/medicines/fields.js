import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "medicines?fields=id,xid,item_id,x_item_id,salt_composition,side_effects,item{id,xid,name,description,sale_price,cost_price,sku,available_quantity,alert_quantity,x_category_id,x_brand_id},item:brand{id,xid,name},item:category{id,xid,name}";
    const addEditUrl = "medicines";
    const { t } = useI18n();
    const hashableColumns = ["item_id"];
    
    const categories = ref([]);
    const brands = ref([]);

    const initData = {
        name: "",
        category_id: undefined,
        brand_id: undefined,
        selling_price: 0,
        buying_price: 0,
        salt_composition: "",
        description: "",
        side_effects: "",
        alert_quantity: 5,
        item: null,
        category: null,
        brand: null,
    };

    const columns = [
        {
            title: t("medicine.item"),
            dataIndex: "item",
            customRender: ({ record }) => record.item?.name || '-'
        },
        {
            title: t("medicine.category"),
            dataIndex: "category",
            customRender: ({ record }) => record.item?.category?.name || '-'
        },
        {
            title: t("medicine.brand"),
            dataIndex: "brand",
            customRender: ({ record }) => record.item?.brand?.name || '-'
        },
        {
            title: t("medicine.available_quantity"),
            dataIndex: "available_quantity",
            customRender: ({ record }) => record.item?.available_quantity || 0
        },
        {
            title: t("medicine.selling_price"),
            dataIndex: "selling_price",
            customRender: ({ record }) => record.item?.sale_price || 0
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "salt_composition",
            value: t("medicine.salt_composition"),
        },
    ];

    const getPrefetchData = () => {
        const categoriesPromise = axiosAdmin.get("item-categories?fields=id,xid,name", {
            params: {
                filters: "type eq \"medicine\""
            }
        });
        const brandsPromise = axiosAdmin.get("item-brands?fields=id,xid,name", {
            params: {
                filters: "type eq \"medicine\""
            }
        });

        
        return Promise.all([categoriesPromise, brandsPromise]).then(([categoriesResponse, brandsResponse]) => {
            categories.value = categoriesResponse.data;
            brands.value = brandsResponse.data;
        });
    };

    return {
        url,
        addEditUrl,
        initData,
        columns,
        filterableColumns,
        hashableColumns,
        categories,
        brands,
        getPrefetchData,
    };
};

export default fields;