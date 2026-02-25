<?php

namespace App\Http\Controllers\Api;

use App\Models\UserStatus;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\UserStatus\IndexRequest;
use App\Http\Requests\Api\UserStatus\StoreRequest;
use App\Http\Requests\Api\UserStatus\UpdateRequest;
use App\Http\Requests\Api\UserStatus\DeleteRequest;

class UserStatusController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = UserStatus::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) 
    {
        $request = request();

        // Filter by user_id if provided
        if ($request->has('user_id') && $request->user_id != "") {
            $userId = $this->getIdFromHash($request->user_id);
            $query = $query->where('user_statuses.user_id', $userId);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('user_statuses.status', $request->status);
        }

        // Include user and company relationships
        $query = $query->with(['user:id,name,email', 'company:id,name']);

        return $query;
    }
}