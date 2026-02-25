<?php

namespace App\SuperAdmin\Observers;

use App\Classes\Common;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\SuperAdmin\Classes\SuperAdminCommon;

class CompanyObserver
{

    public function created(Company $company)
    {
        // $company = Common::addCurrencies($company);

        if (!$company->is_global) {
            $company = $this->addAdminRole($company);
            Common::insertInitSettings($company);

            // Adding Default Subscription Plan
            $company =  SuperAdminCommon::addInitialSubscriptionPlan($company);
        }

        // Log activity
        $this->logActivity('CREATED', $company, 'Compañía creada: ID ' . $company->id);
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        $changes = [];
        $original = $company->getOriginal();
        $oldData = [];
        $newData = [];
        
        foreach ($company->getDirty() as $key => $value) {
            // Skip certain fields if needed
            if (in_array($key, ['updated_at'])) {
                continue;
            }
            
            // Save old and new values for the JSON log
            $oldData[$key] = array_key_exists($key, $original) ? $original[$key] : null;
            $newData[$key] = $value;
            
            // Format the changes to be more readable (keeping for compatibility)
            $changes[$key] = [
                'anterior' => $oldData[$key],
                'nuevo' => $value
            ];
        }
        
        if (!empty($changes)) {
            $this->logActivity('UPDATED', $company, 'Compañía actualizada: ID ' . $company->id, $changes, $oldData, $newData);
        }
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        $this->logActivity('DELETED', $company, 'Eliminación de Compañía: ID ' . $company->id);
    }

    /**
     * Handle the Company "restored" event for soft deletes.
     */
    public function restored(Company $company): void
    {
        $this->logActivity('RESTORED', $company, 'Restauración de Compañía: ID ' . $company->id);
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        $this->logActivity('FORCE_DELETED', $company, 'Eliminación permanente de Compañía: ID ' . $company->id);
    }

    public function addAdminRole($company)
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        
        // Seeding Data - Check if roles already exist before creating
        if (!Role::where('name', 'admin')->where('company_id', $company->id)->exists()) {
            Role::create([
                'name' => 'admin',
                'company_id' => $company->id,
                'display_name' => 'Admin',
                'description' => 'Admin is allowed to manage everything of the app.',
                'is_system' => true,
            ]);
        }

        if (!Role::where('name', 'doctor')->where('company_id', $company->id)->exists()) {
            Role::create([
                'name' => 'doctor',
                'company_id' => $company->id,
                'display_name' => 'Doctor',
                'description' => 'Primary role for doctors.',
                'is_system' => true,
            ]);
        }

        if (!Role::where('name', 'patient')->where('company_id', $company->id)->exists()) {
            Role::create([
                'name' => 'patient',
                'company_id' => $company->id,
                'display_name' => 'Patient',
                'description' => 'Primary role for patients.',
                'is_system' => true,
            ]);
        }

        if (!Role::where('name', 'receptionist')->where('company_id', $company->id)->exists()) {
            Role::create([
                'name' => 'receptionist',
                'company_id' => $company->id,
                'display_name' => 'Receptionist',
                'description' => 'Primary role for receptionists and front desk staff.',
                'is_system' => true,
            ]);
        }

        return $company;
    }

    /**
     * Log the activity in the activity_logs table
     */
    private function logActivity(string $action, Company $company, string $description, array $changes = [], array $oldData = [], array $newData = []): void
    {
        $user = user();
        
        $userData = null;
        if ($user) {
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'addresses' => [
                    'primary' => null,
                    'shipping' => null
                ],
                'work_info' => [
                    'prefijo' => null,
                    'role_id' => $user->role ? $user->role->id : null,
                    'position' => null,
                    'user_type' => $user->user_type ?? 'staff_members',
                    'company_id' => $user->company_id ?? 1,
                    'employee_id' => null,
                    'position_id' => null,
                    'department_id' => null
                ],
                'timestamps' => [
                    'created_at' => null,
                    'updated_at' => null
                ],
                'preferences' => [
                    'timezone' => 'America/Los_Angeles',
                    'date_format' => 'DD/MM/YYYY',
                    'time_format' => 'h:i a',
                    'date_picker_format' => 'dd-mm-yyyy'
                ],
                'personal_info' => [
                    'phone' => null,
                    'email2' => null,
                    'full_name' => $user->name,
                    'profile_image' => null
                ],
                'account_status' => [
                    'status' => $user->status ? 'enabled' : 'disabled',
                    'status_id' => null,
                    'reset_code' => null,
                    'is_superadmin' => 0,
                    'login_enabled' => 1,
                    'email_verification_code' => null
                ]
            ];
        }
        
        // If oldData and newData are empty, generate them from the model
        if (empty($newData)) {
            $newData = [
                'id' => $company->id,
                'name' => $company->name,
                'short_name' => $company->short_name,
                'email' => $company->email,
                'phone' => $company->phone,
                'website' => $company->website,
                'light_logo' => $company->light_logo,
                'dark_logo' => $company->dark_logo,
                'small_dark_logo' => $company->small_dark_logo,
                'small_light_logo' => $company->small_light_logo,
                'address' => $company->address,
                'app_layout' => $company->app_layout,
                'currency_id' => $company->currency_id,
                'lang_id' => $company->lang_id,
                'primary_color' => $company->primary_color,
                'date_format' => $company->date_format,
                'time_format' => $company->time_format,
                'auto_detect_timezone' => $company->auto_detect_timezone,
                'timezone' => $company->timezone,
                'session_driver' => $company->session_driver,
                'app_debug' => $company->app_debug,
                'update_app_notification' => $company->update_app_notification,
                'login_image' => $company->login_image,
                'rtl' => $company->rtl,
                'shortcut_menus' => $company->shortcut_menus,
                'stripe_id' => $company->stripe_id,
                'card_brand' => $company->card_brand,
                'card_last_four' => $company->card_last_four,
                'trial_ends_at' => $company->trial_ends_at,
                'subscription_plan_id' => $company->subscription_plan_id,
                'package_type' => $company->package_type,
                'licence_expire_on' => $company->licence_expire_on,
                'is_global' => $company->is_global,
                'admin_id' => $company->admin_id,
                'status' => $company->status,
                'total_users' => $company->total_users,
                'email_verification_code' => $company->email_verification_code,
                'verified' => $company->verified,
                'enable_landing_page' => $company->enable_landing_page,
                'company_slug' => $company->company_slug,
                'created_at' => $company->created_at,
                'updated_at' => $company->updated_at
            ];
        }
        
        // For Company model, the company_id is the company's own ID
        $companyId = $company->id;
        
        // Registrar en tabla de activity_logs primero para obtener el ID
        $id = DB::table('activity_logs')->insertGetId([
            'action' => $action,
            'entity' => 'Companies',
            'datetime' => now(),
            'description' => $description,
            'user' => json_encode($userData),
            'company_id' => $companyId,
            'json_log' => '{}', // Placeholder que actualizaremos después
            'created_at' => now()
        ]);
        
        // Build json_log with the new format including the record ID
        $jsonLog = [
            'data' => [
                'new' => $newData,
                'old' => $action === 'CREATED' ? null : ($oldData ?: null)
            ],
            'action' => $action,
            'entity' => 'companies',
            'metadata' => [
                'server' => gethostname(),
                'database' => config('database.connections.mysql.database')
            ],
            'timestamp' => now()->format('Y-m-d H:i:s.u'),
            'description' => $description,
            'id' => $id
        ];
        
        // Actualizar el registro con el JSON completo
        DB::table('activity_logs')
            ->where('id', $id)
            ->update(['json_log' => json_encode($jsonLog)]);
    }
}
