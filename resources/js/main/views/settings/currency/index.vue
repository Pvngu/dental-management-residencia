<template>
    <div class="currency-container">
        <div class="mb-4 flex justify-end gap-2">
            <a-button
                v-if="
                    permsArray.includes('currencies_create') ||
                    permsArray.includes('admin')
                "
                type="primary"
                @click="addItem"
            >
                <PlusOutlined />
                {{ $t("currency.add") }}
            </a-button>
            <a-button
                v-if="
                    currencySettingRef &&
                    currencySettingRef.table &&
                    currencySettingRef.table.selectedRowKeys.length > 0 &&
                    (permsArray.includes('currencies_delete') ||
                        permsArray.includes('admin'))
                "
                type="primary"
                @click="showSelectedDeleteConfirm"
                danger
            >
                <DeleteOutlined />
                {{ $t("common.delete") }}
            </a-button>
        </div>
        <CurrencySettings ref="currencySettingRef"> </CurrencySettings>
    </div>
</template>

<script>
import { ref } from "vue";
import { PlusOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import CurrencySettings from "../../common/settings/currency/index.vue";

export default {
    components: {
        PlusOutlined,
        DeleteOutlined,
        CurrencySettings,
    },
    setup() {
        const { permsArray } = common();
        const currencySettingRef = ref(null);

        const addItem = () => {
            currencySettingRef.value.addItem();
        };

        const showSelectedDeleteConfirm = () => {
            currencySettingRef.value.showSelectedDeleteConfirm();
        };

        return {
            permsArray,
            currencySettingRef,
            addItem,
            showSelectedDeleteConfirm,
        };
    },
};
</script>
