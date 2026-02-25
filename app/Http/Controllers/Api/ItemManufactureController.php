<?php

namespace App\Http\Controllers\Api;

use App\Models\ItemManufacture;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\ItemManufacture\IndexRequest;
use App\Http\Requests\Api\ItemManufacture\StoreRequest;
use App\Http\Requests\Api\ItemManufacture\UpdateRequest;
use App\Http\Requests\Api\ItemManufacture\DeleteRequest;

class ItemManufactureController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = ItemManufacture::class;
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
