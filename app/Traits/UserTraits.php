<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\User;
use App\Classes\Common;
use App\Classes\Notify;
use App\Imports\UserImport;
use App\Scopes\CompanyScope;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\User\ImportRequest;

use function Laravel\Prompts\error;

trait UserTraits
{
    public $userType = "";

    public function modifyIndex($query)
    {
        $request = request();

        $query = $query->where('users.user_type', $this->userType);

        if ($request->has('role_type') && $request->role_type != "") {
            $query = $query->where('users.role_type', $request->role_type);
        }

        return $query;
    }

    public function storing($user)
    {
        $loggedUser = user();
        $request = request();

        if (!isset($user->user_type) || !$user->user_type) {
            $user->user_type = 'staff_members';
        }

        if ($user->user_type != $this->userType && $this->userType != '') {
             // If controller defines specific type (like staff_members in UsersController), ensure it matches or is explicitly allowed
             // But for Patients/Doctors, this check might need adjustment or be bypassed if they use different controllers.
             // Actually, PatientController and DoctorController don't use this trait's userType check the same way or might not use UserTraits for the same logic.
             // Let's look at UsersController vs PatientController.
             // UsersController has $this->userType = "staff_members".
             // PatientController does NOT use UserTraits? Let's check.
             // Wait, I need to be careful not to break the permission check.
        }
        
        // Re-evaluating the replacement based on the file content I saw earlier.
        // The original code was:
        // if ($user->user_type != $this->userType) {
        //    throw new ApiException("Don't have valid permission");
        // }
        // 
        // If I just set the default before this check, it should work for UsersController.
        // For PatientController, it creates a NEW User(), sets user_type='patients', then saves.
        // Does PatientController use UserTraits? No, it uses ApiBaseController but does it use UserTraits?
        // Let's check PatientController imports.
        
        if (!isset($user->user_type) || !$user->user_type) {
            $user->user_type = 'staff_members';
        }

        if ($this->userType != '' && $user->user_type != $this->userType) {
            throw new ApiException("Don't have valid permission");
        }

        if ($user->user_type == 'staff_members') {
            $user->role_id = $loggedUser->hasRole('admin') && $request->has('role_id') && $request->role_id ? $this->getIdFromHash($request->role_id) : $loggedUser->role_id;
            
            // Allow setting role_type to receptionist if requested
            if ($request->has('role_type') && $request->role_type == 'receptionist') {
                $user->role_type = 'receptionist';
            } else {
                $user->role_type = 'staff';
            }
        }

        return $user;
    }

    public function stored($user)
    {
        $this->saveAndUpdateRole($user);

        // Save address
        if (method_exists($this, 'addAddressToEntity')) {
            $this->addAddressToEntity($user, request()->all());
        }

        // Notifying to Company
        Notify::send('staff_member_create', $user);

        // Updating Company Total Users
        Common::calculateTotalUsers($user->company_id, true);
    }

    public function updating($user)
    {
        $loggedUser = user();
        $request = request();
        $company = company();

        // Can not change role because only one
        // Admin exists for whole app
        if ($user->user_type == "staff_members") {
            $adminCount = User::role('admin')
                ->where('company_id', $company->id)
                ->count();

            if ($adminCount <= 1 && $user->isDirty('role_id') && $user->hasRole('admin')) {
                throw new ApiException("Can not change role because you are only admin of app");
            }
        }

        if ($user->user_type != $this->userType) {
            throw new ApiException("Don't have valid permission");
        }

        if ($user->user_type == 'staff_members') {
            $user->role_id = $loggedUser->hasRole('admin') && $request->has('role_id') && $request->role_id ? $this->getIdFromHash($request->role_id) : $loggedUser->role_id;
        }

        return $user;
    }

    public function updated($user)
    {
        $this->saveAndUpdateRole($user);

        // Save address
        if (method_exists($this, 'addAddressToEntity')) {
            $this->addAddressToEntity($user, request()->all());
        }

        // Notifying to Company
        Notify::send('staff_member_update', $user);
    }

    public function saveAndUpdateRole($user)
    {
        $request = request();

        // Only For Staff Members
        if ($user->user_type == 'staff_members') {
            $role = Role::where('id', $user->role_id)->where('company_id', company()->id)->first();

            if (!$role) {
                throw new ApiException('Role not found');
            }

            $user->roles()->sync([$role->id => ['company_id' => company()->id]]);
        }

        return $user;
    }

    public function destroying($user)
    {
        if ($user->user_type != $this->userType) {
            throw new ApiException("Don't have valid permission");
        }

        $loggedUser = user();
        $loggedUserCompany = company();

        if ($loggedUserCompany->admin_id == $user->id) {
            throw new ApiException('Can not delete company root admin');
        }

        // If application have only one admin
        // Then staff member cannot be deleted
        if ($user->user_type == "staff_members") {
            if ($user->role_id) {

                // if ($user->hasRole('admin')) {
                //     $adminRoleUserCount = Role::join('role_user', 'roles.id', '=', 'role_user.role_id')
                //         ->where('roles.name', '=', 'admin')
                //         ->count('role_user.user_id');

                //     if ($adminRoleUserCount <= 1) {
                //         throw new ApiException('You are the only admin of app. So not able to delete.');
                //     }
                // }
            }
        }

        if ($loggedUser->id == $user->id) {
            throw new ApiException('Can not delete yourself.');
        }

        return $user;
    }

    public function destroyed($user)
    {
        // Updating Company Total Users
        Common::calculateTotalUsers($user->company_id, true);

        // Notifying to Company
        Notify::send('staff_member_delete', $user);
    }

    public function import(ImportRequest $request)
    {
        if ($request->hasFile('file')) {
            Excel::import(new UserImport($this->userType), request()->file('file'));
        }

        return ApiResponse::make('Imported Successfully', []);
    }

    protected function syncClinics(User $user)
    {
        if (request()->has('clinics')) {
            $clinics = request()->get('clinics');

            if (is_string($clinics)) {
                $clinics = json_decode($clinics, true);
            }
            
            $syncData = [];
            
            foreach ($clinics as $clinic) {
                // Support both 'id' (from frontend selection) and 'clinic_id'
                $clinicXid = $clinic['clinic_id'] ?? ($clinic['id'] ?? null);
                // Use getIdFromHash which is available in the classes that use this trait (Controllers extend ApiBaseController which has getIdFromHash, OR we can use Common::getIdFromHash)
                // Since this trait is used by UsersController which has getIdFromHash, it's fine.
                // But generally safe to use Common::
                $clinicId = $clinicXid ? Common::getIdFromHash($clinicXid) : null;
                
                if ($clinicId) {
                    $roleXid = $clinic['role_id'] ?? null;
                    $roleId = $roleXid ? Common::getIdFromHash($roleXid) : null;
                    
                    // Validation: If role_id is null, default to user's role
                    if (!$roleId && $user->role_id) {
                        $roleId = $user->role_id;
                    }
                    
                    // Ensure role_id is not null before syncing
                    if ($roleId) {
                        $syncData[$clinicId] = ['role_id' => $roleId];
                    }
                }
            }
            
            $user->clinics()->sync($syncData);
        }
    }
};