import InventoryManagement from '../views/inventory/index.vue';
import Item from '../views/inventory/items/index.vue';
import InventoryAdjustments from '../views/inventory/inventory_adjustments/index.vue';
import Promotions from '../views/inventory/promotions/index.vue';
import Sales from '../views/inventory/sales/index.vue';
import SalesDashboard from '../views/inventory/sales/Dashboard.vue';
import POS from '../views/inventory/sales/pos.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/inventory',
                component: InventoryManagement,
                children: [
                    {
                        path: '',
                        component: Item,
                        name: 'admin.items.index',
                        meta: {
                            requireAuth: true,
                            menuParent: "inventory",
                            menuKey: "inventory",
                            permission: "items_view"
                        }
                    },
                    {
                        path: 'inventory-adjustments',
                        component: InventoryAdjustments,
                        name: 'admin.inventory_adjustments.index',
                        meta: {
                            requireAuth: true,
                            menuParent: "inventory",
                            menuKey: "inventory",
                            permission: "inventory_adjustments_view"
                        }
                    },
                    {
                        path: 'promotions',
                        component: Promotions,
                        name: 'admin.promotions.index',
                        meta: {
                            requireAuth: true,
                            menuParent: "inventory",
                            menuKey: "inventory",
                            permission: "promotions_view"
                        }
                    },
                ]
            },
            {
                path: '/admin/sales',
                component: Sales,
                name: 'admin.sales.index',
                meta: {
                    requireAuth: true,
                    menuKey: 'sales',
                    permission: 'sales_view'
                }
            },
            {
                path: '/admin/sales/dashboard',
                component: SalesDashboard,
                name: 'admin.sales.dashboard',
                meta: {
                    requireAuth: true,
                    menuKey: 'sales',
                    permission: 'sales_view'
                }
            },
            {
                path: '/admin/sales/pos',
                component: POS,
                name: 'admin.sales.pos',
                meta: {
                    requireAuth: true,
                    menuKey: "sales",
                    permission: "sales_view"
                }
            },
            {
                path: '/admin/sales/pos/:patientId',
                component: POS,
                name: 'admin.sales.pos.patient',
                meta: {
                    requireAuth: true,
                    menuKey: "inventory",
                    permission: "sales_view"
                }
            }
        ]
    }
]
