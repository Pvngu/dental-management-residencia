import SuperAdmin from '../layouts/SuperAdmin.vue';
import SellerIndex from '../views/sellers/index.vue';

export default [
    {
        path: '/',
        component: SuperAdmin,
        children: [
            {
                path: '/superadmin/sellers',
                component: SellerIndex,
                name: 'superadmin.sellers.index',
                meta: {
                    requireAuth: true,
                    menuParent: "sellers",
                    menuKey: "sellers",
                }
            },
        ]
    }
]
