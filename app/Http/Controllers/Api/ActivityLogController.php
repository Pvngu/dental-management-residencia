<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\ActivityLogTrait;
use App\Http\Requests\Api\ActivityLog\StoreRequest;
use App\Http\Requests\Api\ActivityLog\UpdateRequest;
use App\Http\Requests\Api\ActivityLog\DeleteRequest;
use App\Http\Requests\Api\ActivityLog\IndexRequest;

class ActivityLogController extends ApiBaseController
{
    use CompanyTraits, ActivityLogTrait;
    
    protected $model = ActivityLog::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) 
    {
        $request = request();

        // Filter by patient ID if provided
        if ($request->has('patient_id') && $request->patient_id != "") {
            $query = $query->where('patient_id', $request->patient_id);
        }

        // Filter by action
        if ($request->has('action') && $request->action != "") {
            $query = $query->where('action', $request->action);
        }

        // Filter by entity
        if ($request->has('entity') && $request->entity != "") {
            $query = $query->where('entity', $request->entity);
        }

        // Date range filters (using start_date and end_date)
        if ($request->has('start_date') && $request->start_date != "") {
            $query = $query->whereDate('datetime', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != "") {
            $query = $query->whereDate('datetime', '<=', $request->end_date);
        }

        // Dates Filters (legacy support for comma-separated dates)
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('activity_logs.datetime >= ?', [$startDate])
                ->whereRaw('activity_logs.datetime <= ?', [$endDate]);
        }

        return $query->orderBy('datetime', 'desc');
    }

    /**
     * Get activity statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $stats = [
            'total_activities' => ActivityLog::count(),
            'today_activities' => ActivityLog::whereDate('datetime', today())->count(),
            'this_week_activities' => ActivityLog::whereBetween('datetime', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_activities' => ActivityLog::whereMonth('datetime', now()->month)
                ->whereYear('datetime', now()->year)
                ->count(),
            'activities_by_action' => ActivityLog::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'activities_by_entity' => ActivityLog::selectRaw('entity, COUNT(*) as count')
                ->whereNotNull('entity')
                ->groupBy('entity')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return $this->respondWithSuccess("Activity statistics retrieved successfully", $stats);
    }
}
