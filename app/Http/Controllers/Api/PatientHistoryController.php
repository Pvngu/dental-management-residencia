<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientHistory;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\PatientHistory\IndexRequest;
use App\Http\Requests\Api\PatientHistory\StoreRequest;
use App\Http\Requests\Api\PatientHistory\UpdateRequest;
use App\Http\Requests\Api\PatientHistory\DeleteRequest;

class PatientHistoryController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = PatientHistory::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Filter by patient_id
        if ($request->has('patient_id') && $request->patient_id != "") {
            $query = $query->where('patient_histories.patient_id', $this->getIdFromHash($request->patient_id));
        }

        // Filter by event_type
        if ($request->has('event_type') && $request->event_type != "") {
            $query = $query->where('patient_histories.event_type', $request->event_type);
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('patient_histories.created_at >= ?', [$startDate])
                ->whereRaw('patient_histories.created_at <= ?', [$endDate]);
        }

        // Order by most recent first
        $query = $query->orderBy('patient_histories.created_at', 'desc');

        return $query;
    }
}
