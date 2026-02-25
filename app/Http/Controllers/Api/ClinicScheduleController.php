<?php

namespace App\Http\Controllers\Api;

use App\Models\ClinicSchedule;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use Illuminate\Http\Request;

use App\Http\Requests\Api\ClinicSchedule\IndexRequest;
use App\Http\Requests\Api\ClinicSchedule\StoreRequest;
use App\Http\Requests\Api\ClinicSchedule\UpdateRequest;
use App\Http\Requests\Api\ClinicSchedule\DeleteRequest;

class ClinicScheduleController extends ApiBaseController
{
    use CompanyTraits;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected $model = ClinicSchedule::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Add custom query modifications here if needed
        return $query;
    }
    
    /**
     * Update clinic schedules
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSchedules(UpdateRequest $request)
    {
        $company = company();
        $schedules = $request->schedules;
        
        // First, delete all existing schedules for this company
        ClinicSchedule::where('company_id', $company->id)->delete();
        
        // Create new schedules
        foreach ($schedules as $schedule) {
            ClinicSchedule::create([
                'company_id' => $company->id,
                'day_of_week' => $schedule['day_of_week'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time']
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Clinic schedules updated successfully'
        ]);
    }
}
