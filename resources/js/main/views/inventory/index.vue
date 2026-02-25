<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="pageHeaderTitle" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <!-- Add button based on active tab -->
                <template v-if="activeTab === 'items'">
                    <a-button
                        v-if="permsArray.includes('items_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addItemsItem"
                    >
                        <PlusOutlined />
                        {{ $t("items.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'inventory_adjustments'">
                    <a-button
                        v-if="permsArray.includes('inventory_adjustments_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addInventoryAdjustmentItem"
                    >
                        <PlusOutlined />
                        {{ $t("inventory_adjustment.add") }}
                    </a-button>
                </template>

                <template v-if="activeTab === 'promotions'">
                    <a-button
                        v-if="permsArray.includes('promotions_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addPromotionItem"
                    >
                        <PlusOutlined />
                        {{ $t("promotions.add") }}
                    </a-button>
                </template>
            </a-space>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ pageHeaderTitle }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <admin-page-table-content>
        <!-- View Tabs -->
        <a-row class="mt-5">
            <a-col :span="24">
                <a-tabs
                    v-model:activeKey="activeTab"
                    @change="onTabChange"
                    centered
                    type="card"
                    class="table-tab-filters"
                >
                    <a-tab-pane key="items">
                        <template #tab>
                            <span>
                                <AppstoreOutlined />
                                {{ $t("menu.items") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="inventory_adjustments">
                        <template #tab>
                            <span>
                                <HistoryOutlined />
                                {{ $t("menu.inventory_adjustments") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="promotions">
                        <template #tab>
                            <span>
                                <GiftOutlined />
                                {{ $t("menu.promotions") }}
                            </span>
                        </template>
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>
        <!-- Content rendered via router -->
        <router-view></router-view>
    </admin-page-table-content>
</template>

<script>
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import AdminPageHeader from "../../../common/layouts/AdminPageHeader.vue";
import { PlusOutlined, AppstoreOutlined, HistoryOutlined, GiftOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { useI18n } from 'vue-i18n'

export default {
    components: {
        PlusOutlined,
        AppstoreOutlined,
        HistoryOutlined,
        GiftOutlined,
        AdminPageHeader,
    },
    setup() {
        const { permsArray } = common();
        const router = useRouter();
        const route = useRoute();
        
        const { t } = useI18n();
        
        // Determine active tab based on current route
        const activeTab = computed(() => {
            const routeName = route.name;
            if (routeName.includes('inventory_adjustments')) {
                return 'inventory_adjustments';
            } else if (routeName.includes('promotions')) {
                return 'promotions';
            }
            return 'items';
        });
        
        // Computed property for page header title
        const pageHeaderTitle = computed(() => {
            const titleMap = {
                'items': 'menu.items',
                'inventory_adjustments': 'menu.inventory_adjustments',
                'promotions': 'menu.promotions',
            };
            return t(titleMap[activeTab.value] || 'menu.inventory');
        });

        // Handle tab change - navigate to actual routes
        const onTabChange = (key) => {
            const routeMap = {
                'items': 'admin.items.index',
                'inventory_adjustments': 'admin.inventory_adjustments.index',
                'promotions': 'admin.promotions.index',
            };
            router.push({ name: routeMap[key] });
        };
        
        // Add item handlers for child components
        const addItemsItem = () => {
            // Trigger add action in items component
            window.dispatchEvent(new CustomEvent('add-item'));
        };
        
        const addInventoryAdjustmentItem = () => {
            // Trigger add action in inventory adjustment component
            window.dispatchEvent(new CustomEvent('add-inventory-adjustment'));
        };

        const addPromotionItem = () => {
            // Trigger add action in promotions component
            window.dispatchEvent(new CustomEvent('add-promotion'));
        };

        return {
            permsArray,
            activeTab,
            onTabChange,
            pageHeaderTitle,
            addItemsItem,
            addInventoryAdjustmentItem,
            addPromotionItem,
        };
    },
}
</script>
