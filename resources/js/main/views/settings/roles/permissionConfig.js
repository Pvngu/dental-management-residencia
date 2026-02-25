/**
 * Permission Configuration
 * 
 * This file defines all permission groups based on PermsSeed.php
 * Each group represents a resource with its available actions
 * 
 * Format:
 * {
 *   label: 'menu.resource_name',  // Translation key for resource display name
 *   resource: 'resource_name',     // Base name for permission (e.g., 'users' for 'users_view')
 *   actions: ['view', 'create']    // Available actions for this resource
 * }
 */

export const getPermissionGroups = () => {
    const permissionGroups = [
        // Adjustment Reasons
        {
            label: 'menu.adjustments_reason',
            resource: 'adjustments_reason',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Appointments
        {
            label: 'menu.appointments',
            resource: 'appointments',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Clinic Locations
        {
            label: 'menu.clinic_locations',
            resource: 'clinic_locations',
            actions: ['view', 'view_all', 'create', 'edit', 'delete']
        },
        // Clinic Schedules
        {
            label: 'menu.clinic_schedules',
            resource: 'clinic_schedules',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Company Settings
        {
            label: 'menu.company',
            resource: 'companies',
            actions: ['edit']
        },
        // Currencies
        {
            label: 'menu.currencies',
            resource: 'currencies',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Dental Treatment Monitors
        {
            label: 'menu.dental_treat_monitors',
            resource: 'dental_treat_monitors',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Doctors
        {
            label: 'menu.doctors',
            resource: 'doctors',
            actions: ['view', 'available_view', 'create', 'edit', 'delete', 'status_edit']
        },
        // Document Types
        {
            label: 'menu.document_types',
            resource: 'document_types',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Email Settings
        {
            label: 'menu.email_settings',
            resource: 'email',
            actions: ['edit']
        },
        // Email Templates
        {
            label: 'menu.email_templates',
            resource: 'email_templates',
            actions: ['view', 'view_all', 'create', 'edit', 'delete']
        },
        // Emails
        {
            label: 'menu.emails',
            resource: 'emails',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Expense Categories
        {
            label: 'menu.expense_categories',
            resource: 'expense_categories',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Expenses
        {
            label: 'menu.expenses',
            resource: 'expenses',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Faxes
        {
            label: 'menu.faxes',
            resource: 'faxes',
            actions: ['view', 'create', 'edit', 'delete', 'resend']
        },
        // Form Field Names
        {
            label: 'menu.form_field_names',
            resource: 'form_field_names',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Forms
        {
            label: 'menu.forms',
            resource: 'forms',
            actions: ['view', 'view_all', 'create', 'edit', 'delete']
        },
        // Insurance Providers
        {
            label: 'menu.insurance_providers',
            resource: 'insurance_providers',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Inventory
        {
            label: 'menu.inventory',
            resource: 'inventory',
            actions: ['view']
        },
        // Inventory Adjustments
        {
            label: 'menu.inventory_adjustments',
            resource: 'inventory_adjustments',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Invoices
        {
            label: 'menu.invoices',
            resource: 'invoices',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Item Brands
        {
            label: 'menu.item_brands',
            resource: 'item_brands',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Item Categories
        {
            label: 'menu.item_categories',
            resource: 'item_categories',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Item Manufactures
        {
            label: 'menu.item_manufactures',
            resource: 'item_manufactures',
            actions: ['create', 'manage']
        },
        // Items (Products)
        {
            label: 'menu.items',
            resource: 'items',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Medicine (singular resource)
        {
            label: 'menu.medicine',
            resource: 'medicine',
            actions: ['view']
        },
        // Medicine Brands
        {
            label: 'menu.medicine_brand',
            resource: 'medicine_brand',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Medicine Categories
        {
            label: 'menu.medicine_category',
            resource: 'medicine_category',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Medicines (plural)
        {
            label: 'menu.medicines',
            resource: 'medicines',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Notifications
        {
            label: 'menu.notifications',
            resource: 'notifications',
            actions: ['view', 'edit']
        },
        // Open Cases
        {
            label: 'menu.open_cases',
            resource: 'open_cases',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Patient Credit Cards
        {
            label: 'menu.patient_credit_cards',
            resource: 'patient_credit_cards',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Patient Files
        {
            label: 'menu.patient_files',
            resource: 'patient_files',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Patient Messages
        {
            label: 'menu.patient_messages',
            resource: 'patient_messages',
            actions: ['view', 'create', 'delete']
        },
        // Patient Notes
        {
            label: 'menu.patient_notes',
            resource: 'patient_notes',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Patients
        {
            label: 'menu.patients',
            resource: 'patients',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Postal Dispatch
        {
            label: 'menu.postal_dispatch',
            resource: 'postal_dispatch',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Postal Receive
        {
            label: 'menu.postal_receive',
            resource: 'postal_receive',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Prescriptions
        {
            label: 'menu.prescriptions',
            resource: 'prescriptions',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Promotion Targets
        {
            label: 'menu.promotion_targets',
            resource: 'promotion_targets',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Promotions
        {
            label: 'menu.promotions',
            resource: 'promotions',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Purchase Medicine
        {
            label: 'menu.purchase_medicine',
            resource: 'purchase_medicine',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Questionnaire Instances
        {
            label: 'menu.questionnaire_instances',
            resource: 'questionnaire_instances',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Questionnaire Sections
        {
            label: 'menu.questionnaire_sections',
            resource: 'questionnaire_sections',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Questionnaire Templates
        {
            label: 'menu.questionnaire_templates',
            resource: 'questionnaire_templates',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Questions
        {
            label: 'menu.questions',
            resource: 'questions',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Receptionists
        {
            label: 'menu.receptionists',
            resource: 'receptionists',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Roles
        {
            label: 'menu.roles',
            resource: 'roles',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Room Types
        {
            label: 'menu.room_types',
            resource: 'room_types',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Rooms
        {
            label: 'menu.rooms',
            resource: 'rooms',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Sales
        {
            label: 'menu.sales',
            resource: 'sales',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Sellers
        {
            label: 'menu.sellers',
            resource: 'sellers',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Storage Settings
        {
            label: 'menu.storage_settings',
            resource: 'storage',
            actions: ['edit']
        },
        // Treatment Types
        {
            label: 'menu.treatment_types',
            resource: 'treatment_types',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // User Statuses
        {
            label: 'menu.user_statuses',
            resource: 'user_statuses',
            actions: ['view', 'create', 'edit', 'delete']
        },
        // Users (Staff Members)
        {
            label: 'menu.staff_members',
            resource: 'users',
            actions: ['view', 'create', 'edit', 'delete']
        },
    ];

    return permissionGroups;
};

/**
 * Helper function to get the translation key for an action
 * Maps permission action names to common.* translation keys
 */
export const getActionTranslationKey = (action) => {
    const actionMap = {
        'view': 'common.view',
        'view_all': 'common.view_all',
        'available_view': 'common.available_view',
        'create': 'common.add',
        'edit': 'common.edit',
        'delete': 'common.delete',
        'resend': 'common.resend',
        'manage': 'common.manage',
        'status_edit': 'common.status_edit',
    };

    return actionMap[action] || `common.${action}`;
};
