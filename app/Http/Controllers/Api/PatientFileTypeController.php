<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientFileType;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\PatientFileType\IndexRequest;
use App\Http\Requests\Api\PatientFileType\StoreRequest;
use App\Http\Requests\Api\PatientFileType\UpdateRequest;
use App\Http\Requests\Api\PatientFileType\DeleteRequest;

class PatientFileTypeController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = PatientFileType::class;
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

            $query = $query->whereRaw('document_types.created_at >= ?', [$startDate])
                ->whereRaw('document_types.created_at <= ?', [$endDate]);
        }

        return $query;
    }
}
