import ActivityLogIndex from '../views/activity-log/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/activity-log',
                component: ActivityLogIndex,
                name: 'admin.activity_log.index',
                meta: {
                    requireAuth: true,
                    menuParent: "activity_log",
                    menuKey: "activity_log",
                    permission: "activity_log_view"
                }
            },
        ]
    }
]
