<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryCategory;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\InventoryCategory\IndexRequest;
use App\Http\Requests\Api\InventoryCategory\StoreRequest;
use App\Http\Requests\Api\InventoryCategory\UpdateRequest;
use App\Http\Requests\Api\InventoryCategory\DeleteRequest;

class InventoryCategoryController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = InventoryCategory::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        return $query;
    }
}
