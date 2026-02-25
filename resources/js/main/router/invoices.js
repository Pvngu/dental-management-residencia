import Invoice from '../views/invoices/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/invoices',
                component: Invoice,
                name: 'admin.invoices.index',
                meta: {
                    requireAuth: true,
                    menuParent: "invoices",
                    menuKey: "invoices",
                    permission: "invoices_view"
                }
            },
        ]
    }
]
