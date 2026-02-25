<?php

namespace App\Http\Controllers\Api;

use App\Models\PromotionTarget;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\PromotionTarget\IndexRequest;
use App\Http\Requests\Api\PromotionTarget\StoreRequest;
use App\Http\Requests\Api\PromotionTarget\UpdateRequest;
use App\Http\Requests\Api\PromotionTarget\DeleteRequest;

class PromotionTargetController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = PromotionTarget::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();
        return $query;
    }
}
