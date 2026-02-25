<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\Seller;
use App\Classes\Files;
use App\Scopes\CompanyScope;
use App\Traits\CompanyTraits;
use App\Traits\UserTraits;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\Seller\IndexRequest;
use App\Http\Requests\Api\Seller\StoreRequest;
use App\Http\Requests\Api\Seller\DeleteRequest;
use App\Http\Requests\Api\Seller\UpdateRequest;
use Illuminate\Http\Request;

class SellerController extends ApiBaseController
{
    use CompanyTraits, UserTraits {
        CompanyTraits::storing insteadof UserTraits;
        UserTraits::storing as userStoring;
        UserTraits::updated as userUpdated;
        UserTraits::stored as userStored;
        UserTraits::destroyed as userDestroyed;
    }
    
    protected $model = Seller::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            if (count($dates) >= 2) {
                $startDate = $dates[0];
                $endDate = $dates[1];

                $query = $query->whereRaw('sellers.created_at >= ?', [$startDate])
                    ->whereRaw('sellers.created_at <= ?', [$endDate]);
            }
        }

        return $query->with('user');
    }

    public function storing(Seller $seller)
    {
        try {
            DB::beginTransaction();
            $request = request();
            $company = company();

            $sellerRole = Role::withoutGlobalScope(CompanyScope::class)
                ->where('name', 'seller')
                ->where('company_id', $company->id)
                ->first();

            if (!$sellerRole) {
                throw new ApiException('Seller role not found');
            }

            // Create user first
            $user = new User();
            $user->name = $request->name ?? '';
            $user->last_name = $request->last_name ?? '';
            $user->email = $request->email ?? '';
            $user->phone = $request->phone ?? '';
            $user->gender = $request->gender ?? null;
            $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : null;
            $user->status = $request->status ?? 'enabled';
            $user->user_type = 'sellers';
            $user->address = $request->address ?? '';
            
            if ($request->has('password') && $request->password != '') {
                $user->password = $request->password;
            }

            if ($request->hasFile('profile_image')) {
                $user->profile_image = Files::upload($request->file('profile_image'), 'sellers/profile-images');
            }
            
            $user->company_id = $company->id;
            $user->role_id = $sellerRole->id;
            $user->role_type = 'seller';
            $user->save();
            $user->assignRole($sellerRole->name, '');

            // Create seller
            $seller->company_id = $company->id;
            $seller->user_id = $user->id;
            $seller->commission_rate = $request->commission_rate ?? null;
            $seller->status = $request->status ?? 'active';
            
            DB::commit();

            return $seller;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    public function updating(Seller $seller)
    {
        try {
            DB::beginTransaction();
            $request = request();

            // Update user
            $user = User::find($seller->user_id);
            if ($user) {
                $user->name = $request->name ?? $user->name;
                $user->last_name = $request->last_name ?? $user->last_name;
                $user->email = $request->email ?? $user->email;
                $user->phone = $request->phone ?? $user->phone;
                $user->gender = $request->gender ?? $user->gender;
                $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : $user->date_of_birth;
                $user->status = $request->status ?? $user->status;
                $user->address = $request->address ?? $user->address;
                
                if ($request->has('password') && $request->password != '') {
                    $user->password = $request->password;
                }

                if ($request->hasFile('profile_image')) {
                    $user->profile_image = Files::upload($request->file('profile_image'), 'sellers/profile-images');
                }
                
                $user->save();
            }

            // Update seller
            $seller->commission_rate = $request->commission_rate ?? $seller->commission_rate;
            $seller->status = $request->status ?? $seller->status;
            
            DB::commit();

            return $seller;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    public function destroying(Seller $seller)
    {
        try {
            DB::beginTransaction();

            // Delete user
            $user = User::find($seller->user_id);
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return $seller;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }
}
