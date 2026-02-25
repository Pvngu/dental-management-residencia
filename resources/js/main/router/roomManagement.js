import RoomLayout from '../views/room-management/rooms/layout.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/room-layout',
                component: RoomLayout,
                name: 'admin.room_layout.index',
                meta: {
                    requireAuth: true,
                    // menuParent: "room_management",
                    menuKey: "room_layout",
                    permission: "rooms_view"
                }
            },
        ]
    }
]
