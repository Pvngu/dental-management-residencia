import MedicineManagement from '../views/medicines/index.vue';
import Medicines from '../views/medicines/medicines/index.vue';
import PurchaseMedicine from '../views/medicines/purchase-medicine/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/medicines',
                component: MedicineManagement,
                children: [
                    {
                        path: '',
                        component: Medicines,
                        name: 'admin.medicines.index',
                        meta: {
                            requireAuth: true,
                            menuParent: "medicine",
                            menuKey: "medicines",
                            permission: "medicines_view"
                        }
                    },
                    {
                        path: 'purchase',
                        component: PurchaseMedicine,
                        name: 'admin.medicines.purchase',
                        meta: {
                            requireAuth: true,
                            menuParent: "medicine",
                            menuKey: "medicines",
                            permission: "purchase_medicine_view"
                        }
                    },
                ]
            },
        ]
    }
]
