<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="pageHeaderTitle" class="p-0!" />
        </template>
        <template #actions>
            <a-space>
                <!-- Add button based on active tab -->
                <template v-if="activeTab === 'medicines'">
                    <a-button
                        v-if="permsArray.includes('medicines_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addMedicineItem"
                    >
                        <PlusOutlined />
                        {{ $t("medicine.add") }}
                    </a-button>
                </template>
                
                <template v-if="activeTab === 'purchase'">
                    <a-button
                        v-if="permsArray.includes('purchase_medicine_create') || permsArray.includes('admin')"
                        type="primary"
                        @click="addPurchaseItem"
                    >
                        <PlusOutlined />
                        {{ $t("purchase_medicine.add") }}
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
                    <a-tab-pane key="medicines">
                        <template #tab>
                            <span>
                                <MedicineBoxOutlined />
                                {{ $t("menu.medicines") }}
                            </span>
                        </template>
                    </a-tab-pane>
                    <a-tab-pane key="purchase">
                        <template #tab>
                            <span>
                                <ShoppingCartOutlined />
                                {{ $t("menu.purchase_medicine") }}
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
import { PlusOutlined, MedicineBoxOutlined, ShoppingCartOutlined } from "@ant-design/icons-vue";
import common from "../../../common/composable/common";
import { useI18n } from 'vue-i18n'

export default {
    components: {
        PlusOutlined,
        MedicineBoxOutlined,
        ShoppingCartOutlined,
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
            switch(routeName) {
                case 'admin.medicines.purchase':
                    return 'purchase';
                default:
                    return 'medicines';
            }
        });
        
        // Computed property for page header title
        const pageHeaderTitle = computed(() => {
            const titleMap = {
                'medicines': 'menu.medicines',
                'purchase': 'menu.purchase_medicine',
            };
            return t(titleMap[activeTab.value] || 'menu.medicines');
        });

        // Handle tab change - navigate to actual routes
        const onTabChange = (key) => {
            const routeMap = {
                'medicines': 'admin.medicines.index',
                'purchase': 'admin.medicines.purchase',
            };
            router.push({ name: routeMap[key] });
        };
        
        // Add item handlers for child components
        const addMedicineItem = () => {
            // Trigger add action in medicines component
            window.dispatchEvent(new CustomEvent('add-medicine'));
        };
        
        const addPurchaseItem = () => {
            // Trigger add action in purchase component
            window.dispatchEvent(new CustomEvent('add-purchase'));
        };

        return {
            permsArray,
            activeTab,
            onTabChange,
            pageHeaderTitle,
            addMedicineItem,
            addPurchaseItem,
        };
    },
}
</script>
