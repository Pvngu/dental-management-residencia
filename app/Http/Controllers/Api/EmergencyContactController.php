<?php

namespace App\Http\Controllers\Api;

use App\Models\EmergencyContact;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\EmergencyContact\StoreRequest;
use App\Http\Requests\Api\EmergencyContact\UpdateRequest;
use App\Http\Requests\Api\EmergencyContact\DeleteRequest;
use App\Http\Requests\Api\EmergencyContact\IndexRequest;

class EmergencyContactController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = EmergencyContact::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Filter by patient if provided
        if ($request->has('patient_id') && $request->patient_id != "") {
            $query = $query->where('patient_id', $this->getIdFromHash($request->patient_id));
        }

        return $query;
    }
}
