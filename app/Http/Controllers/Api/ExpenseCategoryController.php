<?php

namespace App\Http\Controllers\Api;

use App\Models\ExpenseCategory;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\ExpenseCategory\IndexRequest;
use App\Http\Requests\Api\ExpenseCategory\StoreRequest;
use App\Http\Requests\Api\ExpenseCategory\UpdateRequest;
use App\Http\Requests\Api\ExpenseCategory\DeleteRequest;

class ExpenseCategoryController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = ExpenseCategory::class;
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

            $query = $query->whereRaw('expense_categories.created_at >= ?', [$startDate])
                ->whereRaw('expense_categories.created_at <= ?', [$endDate]);
        }

        return $query;
    }
}
