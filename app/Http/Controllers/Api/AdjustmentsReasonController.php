<?php

namespace App\Http\Controllers\Api;

use App\Models\AdjustmentsReason;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\AdjustmentsReason\StoreRequest;
use App\Http\Requests\Api\AdjustmentsReason\UpdateRequest;
use App\Http\Requests\Api\AdjustmentsReason\DeleteRequest;
use App\Http\Requests\Api\AdjustmentsReason\IndexRequest;

class AdjustmentsReasonController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = AdjustmentsReason::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        return $query;
    }
}
