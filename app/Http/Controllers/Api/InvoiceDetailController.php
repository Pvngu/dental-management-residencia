<?php

namespace App\Http\Controllers\Api;

use App\Models\InvoiceDetail;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\InvoiceDetail\IndexRequest;
use App\Http\Requests\Api\InvoiceDetail\StoreRequest;
use App\Http\Requests\Api\InvoiceDetail\UpdateRequest;
use App\Http\Requests\Api\InvoiceDetail\DeleteRequest;

class InvoiceDetailController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = InvoiceDetail::class;
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

            $query = $query->whereRaw('invoice_details.created_at >= ?', [$startDate])
                ->whereRaw('invoice_details.created_at <= ?', [$endDate]);
        }

        return $query;
    }
}
