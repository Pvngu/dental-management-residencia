<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Invoice\IndexRequest;
use App\Http\Requests\Api\Invoice\StoreRequest;
use App\Http\Requests\Api\Invoice\UpdateRequest;
use App\Http\Requests\Api\Invoice\DeleteRequest;

class InvoiceController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Invoice::class;
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

            $query = $query->whereRaw('invoices.date_of_issue >= ?', [$startDate])
                ->whereRaw('invoices.date_of_issue <= ?', [$endDate]);
        }

        return $query;
    }
}
