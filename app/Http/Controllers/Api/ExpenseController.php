<?php

namespace App\Http\Controllers\Api;

use App\Models\Expense;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Expense\IndexRequest;
use App\Http\Requests\Api\Expense\StoreRequest;
use App\Http\Requests\Api\Expense\UpdateRequest;
use App\Http\Requests\Api\Expense\DeleteRequest;

class ExpenseController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Expense::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('expenses.date >= ?', [$startDate])
                ->whereRaw('expenses.date <= ?', [$endDate]);
        }

        return $query;
    }
}
