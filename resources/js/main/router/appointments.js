import AppointmentsWrapper from "../views/appointments/AppointmentsWrapper.vue";
import AppointmentsTable from "../views/appointments/AppointmentsTable.vue";
import AppointmentsCalendar from "../views/appointments/AppointmentsCalendar.vue";
import TodaysAppointments from "../views/appointments/TodaysAppointments.vue";

export default [
    {
        path: "/",
        component: () => import("../../common/layouts/Admin.vue"),
        children: [
            {
                path: "/admin/appointments",
                component: AppointmentsWrapper,
                children: [
                    {
                        path: "",
                        component: TodaysAppointments,
                        name: "admin.appointments.today",
                        meta: {
                            requireAuth: true,
                            menuParent: "appointments",
                            menuKey: "appointments",
                            permission: "appointments_view",
                        },
                    },
                    {
                        path: "table",
                        component: AppointmentsTable,
                        name: "admin.appointments.table",
                        meta: {
                            requireAuth: true,
                            menuParent: "appointments",
                            menuKey: "appointments",
                            permission: "appointments_view",
                        },
                    },
                    {
                        path: "calendar",
                        component: AppointmentsCalendar,
                        name: "admin.appointments.calendar",
                        meta: {
                            requireAuth: true,
                            menuParent: "appointments",
                            menuKey: "appointments",
                            permission: "appointments_view",
                        },
                    },
                ],
            },
        ],
    },
];
