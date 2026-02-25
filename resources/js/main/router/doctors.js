import DoctorManagement from '../views/doctor-management/index.vue';
import DoctorsTable from '../views/doctor-management/doctors/index.vue';
import DoctorDepartments from '../views/doctor-management/departments/index.vue';
import DoctorSchedules from '../views/doctor-management/schedules/index.vue';
import DoctorHolidays from '../views/doctor-management/holidays/index.vue';
import DoctorBreaks from '../views/doctor-management/breaks/index.vue';

export default [
    {
        path: '/',
        component: () => import('../../common/layouts/Admin.vue'),
        children: [
            {
                path: '/admin/doctors',
                component: DoctorManagement,
                children: [
                    {
                        path: '',
                        component: DoctorsTable,
                        name: 'admin.doctors.index',
                        meta: {
                            requireAuth: true,
                            menuParent: "doctors",
                            menuKey: "doctors",
                            permission: "doctors_view"
                        }
                    },
                    {
                        path: 'departments',
                        component: DoctorDepartments,
                        name: 'admin.doctors.departments',
                        meta: {
                            requireAuth: true,
                            menuParent: "doctors",
                            menuKey: "doctors",
                            permission: "doctor_departments_view"
                        }
                    },
                    {
                        path: 'schedules',
                        component: DoctorSchedules,
                        name: 'admin.doctors.schedules',
                        meta: {
                            requireAuth: true,
                            menuParent: "doctors",
                            menuKey: "doctors",
                            permission: "doctors_view"
                        }
                    },
                    {
                        path: 'holidays',
                        component: DoctorHolidays,
                        name: 'admin.doctors.holidays',
                        meta: {
                            requireAuth: true,
                            menuParent: "doctors",
                            menuKey: "doctors",
                            permission: "doctor_holidays_view"
                        }
                    },
                    {
                        path: 'breaks',
                        component: DoctorBreaks,
                        name: 'admin.doctors.breaks',
                        meta: {
                            requireAuth: true,
                            menuParent: "doctors",
                            menuKey: "doctors",
                            permission: "doctor_breaks_view"
                        }
                    },
                ]
            },
        ]
    }
]
