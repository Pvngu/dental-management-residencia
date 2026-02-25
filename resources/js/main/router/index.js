import { createRouter, createWebHistory } from "vue-router";
import store from "../store";

import AuthRoutes from "./auth";
import DashboardRoutes from "./dashboard";
import UserRoutes from "./users";
import MessagingRoutes from "./messaging";
import superAdminRoutes from "../../superadmin/router/index";
import subscriptionRoutes from "../../superadmin/router/admin/index";
import DoctorRoutes from "./doctors";
import PatientManagementRoutes from "./patient-management";
import AppointmentsRoutes from "./appointments";
import MedicinesRoutes from './medicines';
import InventoryRoutes from "./inventory";
import FrontOfficeRoutes from './frontOffice';
import ExpenseManagementRoutes from './expenseManagement';
import CalendarRoutes from './calendar';
import RoomManagementRoutes from './roomManagement';
import InvoicesRoutes from './invoices';
import DentalChartRoutes from './dental-chart';
import ActivityLog from './activityLog';
import OpenCasesRoutes from './openCases';
import FaxCenterRoutes from './faxCenter';
import { checkUserPermission } from "../../common/scripts/functions";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: "",
            redirect: "/admin/login",
        },
        ...AuthRoutes,
        ...superAdminRoutes,
        ...subscriptionRoutes,
        ...DashboardRoutes,
        ...UserRoutes,
        ...MessagingRoutes,
        ...DoctorRoutes,
        ...PatientManagementRoutes,
        ...AppointmentsRoutes,
        ...MedicinesRoutes,
        ...InventoryRoutes,
        ...FrontOfficeRoutes,
        ...ExpenseManagementRoutes,
        ...CalendarRoutes,
        ...RoomManagementRoutes,
        ...InvoicesRoutes,
        ...DentalChartRoutes,
        ...ActivityLog,
        ...OpenCasesRoutes,
        ...FaxCenterRoutes,
    ],
    scrollBehavior: () => ({ left: 0, top: 0 }),
});

router.beforeEach((to, from, next) => {
    const { user } = store.state.auth;
    const isLoggedIn = store.getters["auth/isLoggedIn"];
    const isSuperAdmin = user?.is_superadmin;
    store.commit("auth/updateAppChecking", false);

    const redirectToLogin = () => {
        store.dispatch("auth/logout");
        next({ name: "admin.login" });
    };

    if (to.meta.requireAuth && !isLoggedIn) {
        return redirectToLogin();
    }

    if (to.meta.requireUnauth && isLoggedIn) {
        if (isSuperAdmin) {
            return next({ name: "superadmin.dashboard.index" });
        }
        return next({ name: "admin.dashboard.index" });
    }

    if (to.name.startsWith("superadmin")) {
        if (to.meta.requireAuth && isLoggedIn && !isSuperAdmin) {
            return redirectToLogin();
        }
    } else if (to.name.startsWith("admin")) {
        if (to.name === 'admin.login') {
            return next();
        }

        if (isSuperAdmin) {
            return next({ name: "superadmin.dashboard.index" });
        }

        if (to.name === 'saas.settings.modules.index') {
            return next();
        }

        const { permission } = to.meta;
        if (permission && !checkUserPermission(permission, user)) {
            return next({ name: "admin.dashboard.index" });
        }
    }

    next();
});

export default router;