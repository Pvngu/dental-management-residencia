<?php

namespace App\Http\Controllers\Api;

use App\Models\InsuranceProvider;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\AddressTraits;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;

class InsuranceProviderController extends ApiBaseController
{
    use CompanyTraits, AddressTraits;
    
    protected $model = InsuranceProvider::class;

    protected $indexRequest = \App\Http\Requests\Api\InsuranceProvider\IndexRequest::class;
    protected $storeRequest = \App\Http\Requests\Api\InsuranceProvider\StoreRequest::class;
    protected $updateRequest = \App\Http\Requests\Api\InsuranceProvider\UpdateRequest::class;
    protected $deleteRequest = \App\Http\Requests\Api\InsuranceProvider\DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        if ($request->has('searchString') && $request->searchString != "") {
            $query = $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->searchString . '%')
                  ->orWhere('payor_id', 'like', '%' . $request->searchString . '%')
                  ->orWhere('phone_support', 'like', '%' . $request->searchString . '%');
            });
        }

        return $query->with('addresses');
    }

    public function storing($insuranceProvider)
    {
        $company = company();
        $insuranceProvider->company_id = $company->id;

        return $insuranceProvider;
    }

    public function stored($insuranceProvider)
    {
        $request = request();
        
        // Handle addresses after the model is saved
        if ($request->has('addresses') && is_array($request->addresses) && count($request->addresses) > 0) {
            $this->handleInsuranceProviderAddresses($insuranceProvider, $request->addresses);
        }

        return $insuranceProvider;
    }

    public function updating($insuranceProvider)
    {
        $request = request();
        
        // Handle addresses
        if ($request->has('addresses') && is_array($request->addresses)) {
            $this->handleInsuranceProviderAddresses($insuranceProvider, $request->addresses);
        }

        return $insuranceProvider;
    }
}
