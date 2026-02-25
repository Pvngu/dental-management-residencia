import OpenCases from '../views/open-cases/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/open-cases',
                component: OpenCases,
                name: 'admin.open_cases.index',
                meta: {
                    requireAuth: true,
                    menuParent: "open_cases",
                    menuKey: "open_cases",
                    permission: "open_cases_view"
                }
            },
        ]
    }
]
