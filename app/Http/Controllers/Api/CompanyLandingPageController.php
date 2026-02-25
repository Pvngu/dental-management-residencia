<?php

namespace App\Http\Controllers\Api;

use App\Models\CompanyLandingPage;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\CompanyLandingPage\StoreRequest;
use App\Http\Requests\Api\CompanyLandingPage\UpdateRequest;
use App\Http\Requests\Api\CompanyLandingPage\DeleteRequest;
use App\Http\Requests\Api\CompanyLandingPage\IndexRequest;

class CompanyLandingPageController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = CompanyLandingPage::class;
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
