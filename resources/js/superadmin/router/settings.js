import SuperAdmin from '../layouts/SuperAdmin.vue';
import CompanyEdit from '../views/settings/company/Edit.vue';
import ProfileEdit from '../views/settings/profile/Edit.vue';
import PreferencesEdit from '../views/settings/preferences/Edit.vue';
import Currencies from '../views/settings/currency/index.vue';
import StorageEdit from '../views/settings/storage/Edit.vue';
import EmailEdit from '../views/settings/email/Edit.vue';
import DatabaseBackup from '../views/settings/database-backup/index.vue';
import WhiteLabelSetting from '../views/settings/white-label/Edit.vue';

export default [
    {
        path: '/superadmin/settings/',
        component: SuperAdmin,
        children: [
            {
                path: 'company',
                component: CompanyEdit,
                name: 'superadmin.settings.company.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "company",
                    permission: "superadmin"
                }
            },
            {
                path: 'profile',
                component: ProfileEdit,
                name: 'superadmin.settings.profile.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "profile",
                    permission: "superadmin"
                }
            },
            {
                path: 'preferences',
                component: PreferencesEdit,
                name: 'superadmin.settings.preferences.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "preferences",
                    permission: "superadmin"
                }
            },
            {
                path: 'currencies',
                component: Currencies,
                name: 'superadmin.settings.currencies.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "currencies",
                    permission: "superadmin"
                }
            },
            {
                path: 'storage',
                component: StorageEdit,
                name: 'superadmin.settings.storage.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "storage_settings",
                    permission: "superadmin"
                }
            },
            {
                path: 'email',
                component: EmailEdit,
                name: 'superadmin.settings.email.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "email_settings",
                    permission: "superadmin"
                }
            },
            {
                path: 'white-label',
                component: WhiteLabelSetting,
                name: 'superadmin.settings.white_label.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "white_label",
                    permission: "superadmin"
                }
            },
            {
                path: 'database-backup',
                component: DatabaseBackup,
                name: 'superadmin.settings.database_backup.index',
                meta: {
                    requireAuth: true,
                    menuParent: "settings",
                    menuKey: route => "database_backup",
                    permission: "superadmin"
                }
            },
        ]

    }
];
