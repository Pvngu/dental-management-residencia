<template>
    <a-select
        :value="value"
        @change="handleChange"
        show-search
        :placeholder="placeholder"
        :filter-option="false"
        @search="handleSearch"
        style="width: 100%"
        optionFilterProp="title"
        :dropdownMatchSelectWidth="dropdownMatchSelectWidth"
        :optionLabelProp="optionLabelProp"
    >
        <a-select-option
            v-for="item in filteredItems"
            :key="item.xid"
            :value="item.xid"
            :title="`${item.name || item.item?.name || ''} - ${item.sku || item.item?.sku || ''}`"
            :label="item.name || item.item?.name || ''"
        >
            <slot name="option" :item="item">
                <div>
                    <div class="flex justify-between w-full min-w-[400px]">
                        <div>{{ item.name || item.item?.name || '' }}</div>
                        <div v-if="item.available_quantity !== undefined">Stock on Hand:</div>
                    </div>
                    <div class="flex justify-between">
                        <span>SKU: {{ item.sku || item.item?.sku || '' }}</span>
                        <span v-if="item.available_quantity !== undefined" :class="{'text-red-500': item.available_quantity === 0}">{{ item.available_quantity || 0 }} {{ item.unit || '' }}</span>
                    </div>
                </div>
            </slot>
        </a-select-option>
    </a-select>
</template>

<script>
import { defineComponent, ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";

export default defineComponent({
    props: {
        value: {
            type: String,
            default: undefined,
        },
        placeholder: {
            type: String,
            default: "",
        },
        apiUrl: {
            type: String,
            required: true,
        },
        apiParams: {
            type: String,
            default: "fields=id,xid,name,sku,available_quantity,unit,buying_price&limit=1000",
        },
        dropdownMatchSelectWidth: {
            type: Boolean,
            default: true,
        },
        optionLabelProp: {
            type: String,
            default: "label",
        },
    },
    emits: ["onChange"],
    setup(props, { emit }) {
        const { t } = useI18n();
        const items = ref([]);
        const searchQuery = ref("");
        const loading = ref(false);

        onMounted(() => {
            fetchItems();
        });

        const fetchItems = async () => {
            loading.value = true;
            try {
                const response = await axiosAdmin.get(
                    `${props.apiUrl}?${props.apiParams}`
                );
                items.value = response.data;
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                loading.value = false;
            }
        };

        const handleSearch = (value) => {
            searchQuery.value = value.toLowerCase();
        };

        const filteredItems = computed(() => {
            if (!searchQuery.value) {
                return items.value;
            }

            return items.value.filter((item) => {
                const itemName = item.name || item.item?.name || '';
                const itemSku = item.sku || item.item?.sku || '';
                
                return (
                    itemName.toLowerCase().includes(searchQuery.value) ||
                    itemSku.toLowerCase().includes(searchQuery.value)
                );
            });
        });

        const handleChange = (value) => {
            const selectedItem = items.value.find((item) => item.xid === value);
            emit("onChange", value, selectedItem);
        };

        return {
            items,
            filteredItems,
            handleSearch,
            handleChange,
            loading,
        };
    },
});
</script>
