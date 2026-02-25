<?php

namespace App\Http\Controllers\Api;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleDay;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\DoctorSchedule\IndexRequest;
use App\Http\Requests\Api\DoctorSchedule\StoreRequest;
use App\Http\Requests\Api\DoctorSchedule\UpdateRequest;
use App\Http\Requests\Api\DoctorSchedule\DeleteRequest;
use Illuminate\Http\Request;
use App\Traits\CompanyTraits;
use Examyou\RestAPI\Exceptions\ApiException;

class DoctorScheduleController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = DoctorSchedule::class;
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

            $query = $query->whereRaw('doctor_schedules.created_at >= ?', [$startDate])
                ->whereRaw('doctor_schedules.created_at <= ?', [$endDate]);
        }

        return $query;
    }

    /**
     * Get the schedule for a specific doctor in the current clinic.
     */
    public function getSchedule(Request $request)
    {
        // Require doctor_id
        if (!$request->has('doctor_id')) {
            throw new ApiException('Doctor ID is required', null, 400);
        }

        $doctorId = $this->getIdFromHash($request->doctor_id);

        // Fetch the schedule for this doctor. 
        // Thanks to BelongsToClinic trait, this is automatically scoped to the current clinic.
        $schedule = DoctorSchedule::where('doctor_id', $doctorId)
            ->with(['schedule'])
            ->first();

        if (!$schedule) {
            // Return a default structure if no schedule exists
            return response()->json([
                'data' => [
                    'doctor_id' => $request->doctor_id,
                    'per_patient_time' => '00:30:00', // Default 30 mins
                    'schedule' => []
                ]
            ]);
        }

        return response()->json(['data' => $schedule]);
    }

    public function getAllSchedules(Request $request)
    {
        // Require doctor_id
        if (!$request->has('doctor_id')) {
            throw new ApiException('Doctor ID is required', null, 400);
        }

        $doctorId = $this->getIdFromHash($request->doctor_id);

        // Fetch schedules for ALL clinics (disable ALL global scopes including 'company' and 'clinic')
        $query = DoctorSchedule::withoutGlobalScopes()
            ->with(['schedule' => function($query) {
                $query->withoutGlobalScopes();
            }]);

        // Try to handle case where doctor_id might be a User ID (because user insisted)
        $potentialDoctorId = $this->getIdFromHash($request->doctor_id);
        
        // Check if a doctor exists with this ID
        $doctorExists = Doctor::withoutGlobalScopes()->where('id', $potentialDoctorId)->exists();
        
        if ($doctorExists) {
             $query->where('doctor_id', $potentialDoctorId);
        } else {
             // If not, check if it's a User ID that HAS a doctor profile
             $doctorByUserId = Doctor::withoutGlobalScopes()->where('user_id', $potentialDoctorId)->first();
             if ($doctorByUserId) {
                 $query->where('doctor_id', $doctorByUserId->id);
             } else {
                 // Fallback to original behavior (will return empty)
                 $query->where('doctor_id', $potentialDoctorId);
             }
        }
           
        $schedules = $query->get();

        return response()->json([
            'data' => $schedules
        ]);
    }

    public function storeSchedule(StoreRequest $request)
    {
        $company = company();
        $doctorIds = is_array($request->doctor_id) ? $request->doctor_id : [$request->doctor_id];
        $createdSchedules = [];

        foreach ($doctorIds as $doctorHashId) {
            $doctorId = $this->getIdFromHash($doctorHashId);
            // Pass company_id from request if available (for multi-clinic edit), otherwise null (uses current)
            $targetCompanyId = $request->has('company_id') ? $this->getIdFromHash($request->company_id) : null;
            
            $createdSchedules[] = $this->saveSingleSchedule($doctorId, $request->per_patient_time, $request->schedule, $targetCompanyId);
        }

        return count($createdSchedules) === 1 ? $createdSchedules[0] : $createdSchedules;
    }

    public function updateSchedule(UpdateRequest $request)
    {
        $doctorId = $this->getIdFromHash($request->doctor_id);
        $targetCompanyId = $request->has('company_id') ? $this->getIdFromHash($request->company_id) : null;
        
        // Save schedule (update) for the doctor
        $schedule = $this->saveSingleSchedule($doctorId, $request->per_patient_time, $request->schedule, $targetCompanyId);

        return $schedule;
    }

    /**
     * Helper to save (create/update) a schedule for a single doctor.
     */
    private function saveSingleSchedule($doctorId, $perPatientTime, $scheduleData, $targetCompanyId = null)
    {
        $companyId = $targetCompanyId ?? company()->id;

        // 1. Find existing schedule for this doctor at SPECIFIC CLINIC
        // We must use withoutGlobalScope if we are targeting a specific clinic that might not be the current one (though logic implies we usually edit for current, but for 'All Clinics' mode we might send specific ID)
        $schedule = DoctorSchedule::withoutGlobalScope('company')
            ->firstOrNew([
                'doctor_id' => $doctorId,
                'company_id' => $companyId
            ]);

        $schedule->doctor_id = $doctorId;
        $schedule->per_patient_time = $perPatientTime;
        $schedule->company_id = $companyId;
        
        $schedule->save();

        // 2. Handle Schedule Days
        if (isset($scheduleData) && is_array($scheduleData)) {
            // Delete existing days for THIS schedule ID (disable global scope to ensure we delete regardless of current session clinic)
            DoctorScheduleDay::withoutGlobalScopes()->where('schedule_id', $schedule->id)->delete();

            // Create new days
            $this->saveScheduleDays($schedule, $scheduleData, $companyId);
        }
        
        return $schedule->load('schedule');
    }

    private function saveScheduleDays($schedule, $scheduleData, $companyId)
    {
        // Map day names to integers (1=Monday, 7=Sunday)
        $dayMapping = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
            '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7,
        ];
        
        foreach ($scheduleData as $day => $timeData) {
            if (!isset($timeData['status']) || $timeData['status'] != 1) {
                continue;
            }

            $dayOfWeek = $dayMapping[strtolower($day)] ?? (is_numeric($day) ? (int)$day : null);
            
            if (isset($timeData['from']) && isset($timeData['to']) && $dayOfWeek !== null && $dayOfWeek >= 1 && $dayOfWeek <= 7) {
                $scheduleDay = new DoctorScheduleDay();
                $scheduleDay->doctor_id = $schedule->doctor_id;
                $scheduleDay->schedule_id = $schedule->id;
                $scheduleDay->day_of_week = $dayOfWeek;
                $scheduleDay->available_from = $timeData['from'];
                $scheduleDay->available_to = $timeData['to'];
                $scheduleDay->status = 1;
                $scheduleDay->company_id = $companyId; // Explicitly set company_id
                $scheduleDay->save();
            }
        }
    }
}
