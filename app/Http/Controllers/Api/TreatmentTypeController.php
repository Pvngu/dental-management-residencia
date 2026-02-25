<?php

namespace App\Http\Controllers\Api;

use App\Models\TreatmentType;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\TreatmentType\IndexRequest;
use App\Http\Requests\Api\TreatmentType\StoreRequest;
use App\Http\Requests\Api\TreatmentType\UpdateRequest;
use App\Http\Requests\Api\TreatmentType\DeleteRequest;

class TreatmentTypeController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = TreatmentType::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        return $query;
    }
}
