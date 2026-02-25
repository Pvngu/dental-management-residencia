<?php

namespace App\Http\Controllers\Api;

use App\Models\UserAddress;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\UserAddress\IndexRequest;
use App\Http\Requests\Api\UserAddress\StoreRequest;
use App\Http\Requests\Api\UserAddress\UpdateRequest;
use App\Http\Requests\Api\UserAddress\DeleteRequest;
use App\Scopes\CompanyScope;

class AddressController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = UserAddress::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function setDefault($xid)
    {
        try {
            $addressId = $this->getIdFromHash($xid);
            $address = UserAddress::withoutGlobalScope(CompanyScope::class)->find($addressId);

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }

            // Get user id
            $userId = $address->user_id;

            // Count addresses for this user
            $addressCount = UserAddress::where('user_id', $userId)->count();

            if ($addressCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set default: only one address exists for this user.'
                ], 400);
            }

            // Reset all addresses of this user to non-default
            UserAddress::where('user_id', $userId)
                  ->update(['is_default' => false]);

            // Set this address as default
            $address->is_default = true;
            $address->save();

            return response()->json([
                'success' => true,
                'message' => 'Address set as default successfully'
            ]);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error setting address as default'
            ], 500);
        }
    }
    
    public function modifyIndex($query)
    {
        $request = request();
        
        // Filter by user_id if provided
        if ($request->has('user_id') && $request->user_id) {
            $userId = $this->getIdFromHash($request->user_id);
            $query = $query->where('user_id', $userId);
        }
        
        return $query;
    }
    
    /**
     * Store a new address
     */
    public function store()
    {
        try {
            $request = request();
            $company = company();
            
            // Validate request using StoreRequest
            $storeRequest = new \App\Http\Requests\Api\UserAddress\StoreRequest();
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $storeRequest->rules());
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }
            
            // Get user
            $userId = $this->getIdFromHash($request->user_id);
            $user = \App\Models\User::withoutGlobalScope(\App\Scopes\CompanyScope::class)->find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Create new address
            $address = new UserAddress();
            $address->user_id = $userId;
            $address->company_id = $company->id;
            
            // Fill address data from request
            $this->fillAddressData($address, $request->all());
            $address->save();
            
            // If this is set as default, make other addresses non-default
            if ($address->is_default) {
                UserAddress::where('user_id', $userId)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Address created successfully',
                'data' => $address
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating address: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update an existing address
     */
    public function update(...$args)
    {
        $xid = $args[0] ?? null;
        
        try {
            $request = request();
            
            // Find address
            $addressId = $this->getIdFromHash($xid);
            $address = UserAddress::withoutGlobalScope(\App\Scopes\CompanyScope::class)->find($addressId);
            
            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address not found'
                ], 404);
            }
            
            // Validate request using UpdateRequest
            $updateRequest = new \App\Http\Requests\Api\UserAddress\UpdateRequest();
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $updateRequest->rules());
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }
            
            // Fill address data from request
            $this->fillAddressData($address, $request->all());
            $address->save();
            
            // If this is set as default, make other addresses non-default
            if ($address->is_default) {
                UserAddress::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $address
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating address: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper to fill address data
     */
    private function fillAddressData($address, $data)
    {
        $address->address_line_1 = $data['address_line_1'] ?? null;
        $address->address_line_2 = $data['address_line_2'] ?? null;
        $address->neighborhood = $data['neighborhood'] ?? null;
        $address->postal_code = $data['postal_code'] ?? null;
        $address->city = $data['city'] ?? null;
        $address->state = $data['state'] ?? null;
        $address->country_code = $data['country_code'] ?? 'MX';
        $address->country_name = $data['country_name'] ?? null;
        $address->reference = $data['reference'] ?? null;
        $address->latitude = $data['latitude'] ?? null;
        $address->longitude = $data['longitude'] ?? null;
        $address->contact_name = $data['contact_name'] ?? null;
        $address->contact_phone = $data['contact_phone'] ?? null;
        $address->notes = $data['notes'] ?? null;
        $address->address_type = $data['address_type'] ?? 'home';
        $address->is_default = $data['is_default'] ?? false;
        $address->status = $data['status'] ?? true;
    }
}