import Calendar from '../views/calendarvue/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/calendar',
                component: Calendar,
                name: 'admin.calendar.index',
                meta: {
                    requireAuth: true,
                    menuParent: "calendar",
                    menuKey: "calendar",
                    permission: "calendar_view"
                }
            },
        ]
    }
]
