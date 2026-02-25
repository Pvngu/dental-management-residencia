<?php

namespace App\Http\Controllers\Api;

use App\Models\Postal;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Postal\StoreRequest;
use App\Http\Requests\Api\Postal\UpdateRequest;
use App\Http\Requests\Api\Postal\DeleteRequest;
use App\Http\Requests\Api\Postal\IndexRequest;

class PostalController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Postal::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('postals.date >= ?', [$startDate])
                ->whereRaw('postals.date <= ?', [$endDate]);
        }

        // Status Filter
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('postals.status', $request->status);
        }

        return $query;
    }
}
