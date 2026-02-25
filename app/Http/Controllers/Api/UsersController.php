<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\User\IndexRequest;
use App\Http\Requests\Api\User\StoreRequest;
use App\Http\Requests\Api\User\UpdateRequest;
use App\Http\Requests\Api\User\DeleteRequest;
use App\Models\User;
use App\Traits\UserTraits;
use App\Traits\AddressTraits;
use Illuminate\Http\Request;

class UsersController extends ApiBaseController
{
    use UserTraits, AddressTraits;
    
    protected $model = User::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function __construct()
    {
        parent::__construct();

        $this->userType = "staff_members";
    }

    public function stored(User $user)
    {
        $this->syncClinics($user);
    }

    public function updated(User $user)
    {
        $this->syncClinics($user);
    }



    public function clinicMatrix($id)
    {
        $user = $id == 'new' ? new User() : User::find($this->getIdFromHash($id));

        if (!$user && $id != 'new') {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        // 1. Fetch ALL clinics for the company
        $allClinics = \App\Models\ClinicLocation::where('company_id', company()->id)->get();
        
        // 2. Fetch User's existing access (if not new)
        $userClinicsMap = [];
        if ($user && $user->id) {
            foreach ($user->clinics as $clinic) {
                // Map clinic_id => pivot data
                $userClinicsMap[$clinic->id] = $clinic->pivot;
            }
        }
        
        // 3. Merge Data
        $matrix = $allClinics->map(function ($clinic) use ($user, $userClinicsMap) {
            $hasAccess = isset($userClinicsMap[$clinic->id]);
            $pivot = $hasAccess ? $userClinicsMap[$clinic->id] : null;

            return [
                'id' => $clinic->id,
                'xid' => $clinic->xid,
                'name' => $clinic->name,
                'city' => $clinic->city,
                'logo_url' => $clinic->logo_url, // Assuming accessor exists or attribute
                'has_access' => $hasAccess,
                'assigned_role_id' => $pivot ? $pivot->role_id : null,
                'x_assigned_role_id' => $pivot ? $pivot->x_role_id : null,
                'is_default' => $user->default_clinic_id == $clinic->id
            ];
        });

        return response()->json([
            'data' => $matrix
        ]);
    }

    public function updateClinics(Request $request, $id)
    {
        $user = User::find($this->getIdFromHash($id));

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validate request inputs if necessary, but syncClinics handles the logic
        
        // Update default clinic if provided
        if ($request->has('default_clinic_id') && $request->default_clinic_id) {
            $defaultClinicId = $this->getIdFromHash($request->default_clinic_id);
            $user->default_clinic_id = $defaultClinicId;
            $user->save();
        }

        $this->syncClinics($user);

        return response()->json([
            'message' => 'Clinic access updated successfully',
            'user' => $user
        ]);
    }
}

