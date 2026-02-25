<?php

namespace App\Http\Controllers\Api;

use App\Models\ClinicLocation;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\ClinicLocation\StoreRequest;
use App\Http\Requests\Api\ClinicLocation\UpdateRequest;
use App\Http\Requests\Api\ClinicLocation\DeleteRequest;
use App\Http\Requests\Api\ClinicLocation\IndexRequest;
use Examyou\RestAPI\ApiResponse;

class ClinicLocationController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = ClinicLocation::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        return $query;
    }

    /**
     * Override store method to handle addresses relationship properly
     */
    public function store()
    {
        $request = request();
        
        // Validate the request using the store request class
        $validatedData = app($this->storeRequest)->validated();
        
        // Extract addresses from request
        $addresses = $request->input('addresses', []);
        
        // Remove addresses from clinic location data
        $clinicData = $validatedData;
        unset($clinicData['addresses']);
        
        // Create the clinic location
        $clinicLocation = new ClinicLocation();
        $clinicLocation->fill($clinicData);
        $clinicLocation->company_id = company()->id;
        $clinicLocation->save();
        
        // Create addresses if provided
        if (!empty($addresses) && is_array($addresses)) {
            foreach ($addresses as $addressData) {
                if (is_array($addressData) && !empty($addressData)) {
                    $clinicAddress = new \App\Models\ClinicAddress();
                    $clinicAddress->fill($addressData);
                    $clinicAddress->clinic_location_id = $clinicLocation->id;
                    $clinicAddress->company_id = company()->id;
                    $clinicAddress->save();
                }
            }
        }
        
        return response()->json([
            'message' => 'Resource created successfully',
            'data' => ['xid' => $clinicLocation->xid]
        ]);
    }

    /**
     * Override update method to handle addresses relationship properly
     */
    public function updating(ClinicLocation $clinicLocation)
    {
        $request = request();
        
        // Extract addresses from request
        $addresses = $request->input('addresses', []);
        
        // Handle addresses - for now, we'll just replace them all
        if (isset($addresses)) {
            // Delete existing addresses
            \App\Models\ClinicAddress::where('clinic_location_id', $clinicLocation->id)->delete();
            
            // Create new addresses if provided
            if (!empty($addresses) && is_array($addresses)) {
                foreach ($addresses as $addressData) {
                    if (is_array($addressData) && !empty($addressData)) {
                        $clinicAddress = new \App\Models\ClinicAddress();
                        $clinicAddress->fill($addressData);
                        $clinicAddress->clinic_location_id = $clinicLocation->id;
                        $clinicAddress->company_id = company()->id;
                        $clinicAddress->save();
                    }
                }
            }
        }
        
        return response()->json([
            'message' => 'Resource updated successfully',
            'data' => ['xid' => $clinicLocation->xid]
        ]);
    }

    /**
     * Get clinic locations with limit information for the management interface
     */
    public function indexWithLimits()
    {
        $request = request();
        
        // Get company
        $company = company();
        
        // Build query with pagination and ordering support
        $query = ClinicLocation::with('addresses');
        
        // Handle ordering
        $order = $request->get('order', 'id desc');
        $orderParts = explode(' ', $order);
        $orderColumn = $orderParts[0] ?? 'id';
        $orderDirection = $orderParts[1] ?? 'desc';
        $query->orderBy($orderColumn, $orderDirection);
        
        // Get total count for pagination info
        $total = $query->count();
        
        // Handle pagination
        $limit = (int) $request->get('limit', 10);
        $offset = (int) $request->get('offset', 0);
        
        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($offset);
        }
        
        // Get the data
        $clinicLocations = $query->get();
        
        // Prepare pagination info
        $pagination = [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total
        ];
        
        // Prepare response data
        $responseData = [
            'data' => $clinicLocations,
            'pagination' => $pagination,
            'clinic_limit_info' => [
                'current_count' => $company ? $company->clinic_locations_count : 0,
                'max_allowed' => $company ? $company->max_clinic_locations : 1,
                'can_add_more' => $company ? $company->canAddClinicLocation() : false,
                'remaining_slots' => $company ? $company->remaining_clinic_slots : 0
            ]
        ];

        return ApiResponse::make('Clinic locations fetched successfully', $responseData);
    }
}
