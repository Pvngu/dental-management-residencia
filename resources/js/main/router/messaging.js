

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [

            {
                path: '/admin/message-center',
                component: () => import('../views/messaging/MessageCenter.vue'),
                name: 'admin.message_center.index',
                meta: {
                    requireAuth: true,
                    menuKey: "message_center",
                    permission: "message_center_view"
                }
            },
        ]

    }
]
