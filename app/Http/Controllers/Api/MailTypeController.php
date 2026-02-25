<?php

namespace App\Http\Controllers\Api;

use App\Models\MailType;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\MailType\IndexRequest;
use App\Http\Requests\Api\MailType\StoreRequest;
use App\Http\Requests\Api\MailType\UpdateRequest;
use App\Http\Requests\Api\MailType\DeleteRequest;

class MailTypeController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = MailType::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;
}
