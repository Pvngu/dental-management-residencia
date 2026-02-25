import DentalChart from "../views/patient-management/dental-chart/index.vue";
import ToothDetail from "../views/patient-management/dental-chart/tooth-detail/index.vue";

export default [
    {
        path: "/",
        component: () => import("../../common/layouts/Admin.vue"),
        children: [
            {
                path: "/admin/patients/:id/dental-chart/tooth/:toothId/:section?/:position?",
                component: ToothDetail,
                name: "admin.patients.tooth_detail",
                props: (route) => ({ 
                    id: route.params.id, 
                    toothId: route.params.toothId, 
                    section: route.params.section,
                    position: route.params.position 
                }),
                meta: {
                    requireAuth: true,
                    // menuParent: "patients",
                    menuKey: "patients",
                    permission: "patients_view",
                    parentRoute: {
                        name: 'admin.patients.detail',
                        params: { tab: 'dental-chart' }
                    }
                },
            },
        ],
    },
];
