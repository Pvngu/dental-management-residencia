import PatientManagement from "../views/patient-management/index.vue";
import Patients from "../views/patient-management/patients/index.vue";
import PatientDetail from "../views/patient-management/patients/Details.vue";
import DoctorAssignment from "../views/patient-management/patients/DoctorAssignment.vue";
import PatientFiles from "../views/patient-management/patient-files/index.vue";

export default [
    {
        path: "/",
        component: () => import("../../common/layouts/Admin.vue"),
        children: [
            {
                path: "/admin/patients",
                component: PatientManagement,
                children: [
                    {
                        path: "",
                        component: Patients,
                        name: "admin.patients.index",
                        meta: {
                            requireAuth: true,
                            menuKey: "patients",
                            permission: "patients_view",
                        },
                    },
                    {
                        path: "patient-files",
                        component: PatientFiles,
                        name: "admin.patient_files.index",
                        meta: {
                            requireAuth: true,
                            menuKey: "patients",
                            permission: "patient_files_view",
                        },
                    },
                ],
            },
            {
                path: "/admin/patients/:id/:tab?/:childtab?",
                component: PatientDetail,
                name: "admin.patients.detail",
                props: (route) => ({
                    id: route.params.id,
                    activeTab: route.params.tab || "overview",
                    activeChildTab: route.params.childtab || "",
                }),
                meta: {
                    requireAuth: true,
                    menuKey: "patients",
                    permission: "patients_view",
                },
            },
            {
                path: "/admin/patients/:patientId/assign-doctor",
                component: DoctorAssignment,
                name: "admin.patients.assign_doctor",
                props: (route) => ({ patientId: route.params.patientId }),
                meta: {
                    requireAuth: true,
                    menuKey: "patients",
                    permission: "patients_edit",
                },
            },
        ],
    },
];
