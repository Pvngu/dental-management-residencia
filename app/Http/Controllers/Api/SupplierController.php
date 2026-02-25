<?php
namespace App\Http\Controllers\Api;

use App\Models\Supplier;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Supplier\IndexRequest;
use App\Http\Requests\Api\Supplier\StoreRequest;
use App\Http\Requests\Api\Supplier\UpdateRequest;
use App\Http\Requests\Api\Supplier\DeleteRequest;
use App\Traits\SelectOptionsTraits;

class SupplierController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = Supplier::class;
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
