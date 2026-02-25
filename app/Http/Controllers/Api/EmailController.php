<?php

namespace App\Http\Controllers\Api;

use App\Models\Email;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Email\StoreRequest;
use App\Http\Requests\Api\Email\UpdateRequest;
use App\Http\Requests\Api\Email\DeleteRequest;
use App\Http\Requests\Api\Email\IndexRequest;

class EmailController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Email::class;
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
