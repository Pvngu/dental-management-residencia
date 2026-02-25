<?php

namespace App\SuperAdmin\Http\Controllers\Api;

use App\SuperAdmin\Models\QuestionOption;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\SuperAdmin\Http\Requests\Api\QuestionOption\IndexRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionOption\StoreRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionOption\UpdateRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionOption\DeleteRequest;

class QuestionOptionController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = QuestionOption::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Include question and section relationships
        $query = $query->with(['question.section']);

        return $query;
    }
}
