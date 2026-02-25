import { useI18n } from "vue-i18n";
import { ref } from "vue";

const fields = () => {
    const url = "items?fields=id,xid,name,category_id,x_category_id,unit,description,available_quantity,sku,type,item_length,item_width,item_height,weight,dimension_unit,manufacturer_id,x_manufacturer_id,brand_id,x_brand_id,is_sellable,sale_price,alert_quantity,returnable,sale_description,is_purchasable,cost_price,purchase_description,supplier_id,x_supplier_id,category{xid,name},manufacturer{xid,name},brand{xid,name},supplier{xid,name}";
    const addEditUrl = "items";
    const { t } = useI18n();
    const hashableColumns = ["category_id", 'manufacturer_id', 'brand_id', 'supplier_id'];
    const categories = ref([]);
    const manufacturers = ref([]);
    const brands = ref([]);
    const suppliers = ref([]);

    const units = ref([
        { value: 'box', label: 'Box' },
        { value: 'pcs', label: 'Pieces' },
        { value: 'dozen', label: 'Dozen' },
        { value: 'kg', label: 'kg' },
        { value: 'g', label: 'g' },
        { value: 'mg', label: 'mg' },
        { value: 'lb', label: 'lb' },
        { value: 'oz', label: 'oz' },
        { value: 'm', label: 'm' },
        { value: 'cm', label: 'cm' },
        { value: 'mm', label: 'mm' },
        { value: 'in', label: 'in' },
        { value: 'ft', label: 'ft' },
        { value: 'km', label: 'km' },
        { value: 'ml', label: 'ml' },
    ]);

    const dimensionUnits = ref(['in', 'cm']);
    const weightUnits = ref(['kg', 'g', 'lb', 'oz']);

    const initData = {
        name: "",
        category_id: undefined,
        unit: undefined,
        type: 'goods',
        description: "",
        available_quantity: 0,
        sku: "",
        item_length: undefined,
        item_width: undefined,
        item_height: undefined,
        dimension_unit: "in",
        weight: 0,
        weight_unit: "lb",
        manufacturer_id: undefined,
        brand_id: undefined,
        alert_quantity: 0,
        returnable: 0,
        // Sales information
        is_sellable: true,
        sale_price: null,
        sale_description: "",
        // Purchase information
        is_purchasable: true,
        cost_price: null,
        purchase_description: "",
        supplier_id: undefined,
    };

    const getPrefetchData = () => {
        const itemCategoryPromise = axiosAdmin.get("item-categories/all");
        const manufacturerPromise = axiosAdmin.get("item-manufactures/all");
        const brandPromise = axiosAdmin.get("item-brands/all");
        const supplierPromise = axiosAdmin.get("suppliers/all");
        return Promise.all([itemCategoryPromise, manufacturerPromise, brandPromise, supplierPromise]).then(([itemCategoryResponse, manufacturerResponse, brandResponse, supplierResponse]) => {
            categories.value = itemCategoryResponse.data;
            manufacturers.value = manufacturerResponse.data;
            brands.value = brandResponse.data;
            suppliers.value = supplierResponse.data;
        });
    };

    const columns = [
        {
            title: t("common.name"),
            dataIndex: "name",
        },
        {
            title: t("items.item_category"),
            dataIndex: "category",
        },
        {
            title: t("items.unit"),
            dataIndex: "unit",
        },
        {
            title: t("items.sale_price"),
            dataIndex: "sale_price",
        },
        {
            title: t("items.cost_price"),
            dataIndex: "cost_price",
        },
        {
            title: t("items.available_quantity"),
            dataIndex: "available_quantity",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
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
        getPrefetchData,
        categories,
        manufacturers,
        brands,
        suppliers,
        units,
        dimensionUnits,
        weightUnits,
    };
};

export default fields;
