import FrontOffice from '../views/front_office/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/postals',
                component: FrontOffice,
                name: 'admin.postals.index',
                meta: {
                    requireAuth: true,
                    menuParent: "postals",
                    menuKey: "postals",
                    permission: "postal_receive_view"
                }
            },
        ]
    }
]
