import FaxCenter from '../views/fax-center/faxes/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/fax-center',
                component: FaxCenter,
                name: 'admin.fax_center.index',
                meta: {
                    requireAuth: true,
                    menuParent: "fax_center",
                    menuKey: "fax_center",
                    permission: "faxes_view"
                }
            },
        ]
    }
]
