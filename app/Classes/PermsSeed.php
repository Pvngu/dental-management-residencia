<?php

namespace App\Classes;

use Spatie\Permission\Models\Permission;

class PermsSeed
{
    public static $mainPermissionsArray = [
        'patient_notes_view',
        'patient_notes_create',
        'patient_notes_edit',
        'patient_notes_delete',

        'prescriptions_view',
        'prescriptions_create',
        'prescriptions_edit',
        'prescriptions_delete',

        'emails_view',
        'emails_create',
        'emails_edit',
        'emails_delete',

        'faxes_view',
        'faxes_create',
        'faxes_edit',
        'faxes_delete',
        'faxes_resend',

        'patient_messages_view',
        'patient_messages_create',
        'patient_messages_delete',

        'open_cases_view',
        'open_cases_create',
        'open_cases_edit',
        'open_cases_delete',

        'sales_view',
        'sales_create',
        'sales_edit',
        'sales_delete',
        'users_view',
        'users_create',
        'users_edit',
        'users_delete',
        
        'user_statuses_view',
        'user_statuses_create',
        'user_statuses_edit',
        'user_statuses_delete',
        
        'email_templates_view',
        'email_templates_view_all',
        'email_templates_create',
        'email_templates_edit',
        'email_templates_delete',
        'forms_view',
        'forms_view_all',
        'forms_create',
        'forms_edit',
        'forms_delete',
        'form_field_names_view',
        'form_field_names_create',
        'form_field_names_edit',
        'form_field_names_delete',
        
        'notifications_view',
        'notifications_edit',
        
        'currencies_view',
        'currencies_create',
        'currencies_edit',
        'currencies_delete',
        'roles_view',
        'roles_create',
        'roles_edit',
        'roles_delete',
        'companies_edit',
        'storage_edit',
        'email_edit',
        'patients_view',
        'patients_create',
        'patients_edit',
        'patients_delete',
        'insurance_providers_view',
        'insurance_providers_create',
        'insurance_providers_edit',
        'insurance_providers_delete',
        'patient_credit_cards_view',
        'patient_credit_cards_create',
        'patient_credit_cards_edit',
        'patient_credit_cards_delete',
        'dental_treat_monitors_view',
        'dental_treat_monitors_create',
        'dental_treat_monitors_edit',
        'dental_treat_monitors_delete',
        'appointments_view',
        'appointments_create',
        'appointments_edit',
        'appointments_delete',
        'clinic_schedules_view',
        'clinic_schedules_create',
        'clinic_schedules_edit',
        'clinic_schedules_delete',
        'clinic_locations_view',
        'clinic_locations_view_all',
        'clinic_locations_create',
        'clinic_locations_edit',
        'clinic_locations_delete',
        'document_types_view',
        'document_types_create',
        'document_types_edit',
        'document_types_delete',
        'patient_files_view',
        'patient_files_create',
        'patient_files_edit',
        'patient_files_delete',
        'medicine_brand_view',
        'medicine_brand_create',
        'medicine_brand_edit',
        'medicine_brand_delete',
        'medicine_category_view',
        'medicine_category_create',
        'medicine_category_edit',
        'medicine_category_delete',
        'medicines_view',
        'medicines_create',
        'medicines_edit',
        'medicines_delete',
        'medicine_view',
        'purchase_medicine_view',
        'purchase_medicine_create',
        'purchase_medicine_edit',
        'purchase_medicine_delete',
        'item_categories_view',
        'item_categories_create',
        'item_categories_edit',
        'item_categories_delete',
        'item_brands_view',
        'item_brands_create',
        'item_brands_edit',
        'item_brands_delete',
        'item_manufactures_create',
        'item_manufactures_manage',
        'items_view',
        'items_create',
        'items_edit',
        'items_delete',
        'inventory_adjustments_view',
        'inventory_adjustments_create',
        'inventory_adjustments_edit',
        'inventory_adjustments_delete',
        'inventory_view',
        'invoices_view',
        'invoices_create',
        'invoices_edit',
        'invoices_delete',
        'adjustments_reason_view',
        'adjustments_reason_create',
        'adjustments_reason_edit',
        'adjustments_reason_delete',
        'postal_receive_view',
        'postal_receive_create',
        'postal_receive_edit',
        'postal_receive_delete',
        'postal_dispatch_view',
        'postal_dispatch_create',
        'postal_dispatch_edit',
        'postal_dispatch_delete',
        'expenses_view',
        'expenses_create',
        'expenses_edit',
        'expenses_delete',
        'promotions_view',
        'promotions_create',
        'promotions_edit',
        'promotions_delete',
        'promotion_targets_view',
        'promotion_targets_create',
        'promotion_targets_edit',
        'promotion_targets_delete',
        'expense_categories_view',
        'expense_categories_create',
        'expense_categories_edit',
        'expense_categories_delete',
        'receptionists_view',
        'receptionists_create',
        'receptionists_edit',
        'receptionists_delete',
        'room_types_view',
        'room_types_create',
        'room_types_edit',
        'room_types_delete',
        'rooms_view',
        'rooms_create',
        'rooms_edit',
        'rooms_delete', 
        'treatment_types_view',
        'treatment_types_create',
        'treatment_types_edit',
        'treatment_types_delete',
        'questionnaire_templates_view',
        'questionnaire_templates_create',
        'questionnaire_templates_edit',
        'questionnaire_templates_delete',
        'questionnaire_sections_view',
        'questionnaire_sections_create',
        'questionnaire_sections_edit',
        'questionnaire_sections_delete',
        'questionnaire_instances_view',
        'questionnaire_instances_create',
        'questionnaire_instances_edit',
        'questionnaire_instances_delete',
        'questions_view',
        'questions_create',
        'questions_edit',
        'questions_delete',
        
        'doctors_view',
        'doctors_available_view',
        'doctors_create',
        'doctors_edit',
        'doctors_delete',
        'doctors_status_edit',
        
        // Sellers
        'sellers_view',
        'sellers_create',
        'sellers_edit',
        'sellers_delete',
    ];

    public static $doctorPermissionsArray = [
        'patient_notes_view',
        'patient_notes_create',
        'patient_notes_edit',
        'patient_notes_delete',
        'prescriptions_view',
        'prescriptions_create',
        'prescriptions_edit',
        'prescriptions_delete',
        'emails_view',
        'emails_create',
        'patient_messages_view',
        'patient_messages_create',
        'open_cases_view',
        'open_cases_create',
        'open_cases_edit',
        'notifications_view',
        'patients_view',
        'patients_create',
        'patients_edit',
        'dental_treat_monitors_view',
        'dental_treat_monitors_create',
        'dental_treat_monitors_edit',
        'appointments_view',
        'appointments_create',
        'appointments_edit',
        'clinic_schedules_view',
        'document_types_view',
        'patient_files_view',
        'patient_files_create',
        'patient_files_edit',
        'medicine_brand_view',
        'medicine_category_view',
        'medicines_view',
        'treatment_types_view',
        'questionnaire_templates_view',
        'questionnaire_sections_view',
        'questionnaire_instances_view',
        'questionnaire_instances_create',
        'questionnaire_instances_edit',
        'questions_view',
        'doctors_view',
    ];

    public static $receptionistPermissionsArray = [
        'patients_view',
        'patients_create',
        'patients_edit',
        'appointments_view',
        'appointments_create',
        'appointments_edit',
        'appointments_delete',
        'clinic_schedules_view',
        'patient_messages_view',
        'patient_messages_create',
        'emails_view',
        'emails_create',
        'notifications_view',
        'patient_files_view',
        'patient_files_create',
        'patient_files_edit',
        'document_types_view',
        'insurance_providers_view',
        'patient_credit_cards_view',
        'patient_credit_cards_create',
        'patient_credit_cards_edit',
        'forms_view',
        'questionnaire_templates_view',
        'questionnaire_sections_view',
        'questionnaire_instances_view',
        'questionnaire_instances_create',
        'questionnaire_instances_edit',
        'questions_view',
        'treatment_types_view',
        'doctors_view',
        'invoices_view',
        'invoices_create',
        'invoices_edit',
        'sales_view',
        'sales_create',
        'receptionists_view',
    ];

    public static function seedPermissions()
    {
        $permissions = self::$mainPermissionsArray;;

        foreach ($permissions as $permission) {
            $permissionName = is_array($permission) ? $permission['name'] : $permission;
            
            Permission::create([
                'name' => $permissionName
            ]);
        }
    }

    public static function seedMainPermissions()
    {
        // Main Module
        self::seedPermissions();
    }

    public static function seedDoctorRolePermissions()
    {
        $doctorRoles = \App\Models\Role::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->where('name', 'doctor')
            ->get();

        foreach ($doctorRoles as $doctorRole) {
            $doctorRole->givePermissionTo(self::$doctorPermissionsArray);
        }
    }

    public static function seedReceptionistRolePermissions()
    {
        $receptionistRoles = \App\Models\Role::withoutGlobalScope(\App\Scopes\CompanyScope::class)
            ->where('name', 'receptionist')
            ->get();

        foreach ($receptionistRoles as $receptionistRole) {
            $receptionistRole->givePermissionTo(self::$receptionistPermissionsArray);
        }
    }
}
