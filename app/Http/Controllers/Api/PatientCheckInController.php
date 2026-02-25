<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientCheckIn;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\PatientCheckIn\StoreRequest;
use App\Http\Requests\Api\PatientCheckIn\UpdateRequest;
use App\Http\Requests\Api\PatientCheckIn\DeleteRequest;
use App\Http\Requests\Api\PatientCheckIn\IndexRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PatientCheckInController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = PatientCheckIn::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Filter by patient if provided
        if ($request->has('patient_id') && $request->patient_id != '') {
            $patientId = $this->getIdFromHash($request->patient_id);
            if ($patientId) {
                $query = $query->where('patient_id', $patientId);
            }
        }

        // Filter by doctor if provided
        if ($request->has('doctor_id') && $request->doctor_id != '') {
            $doctorId = $this->getIdFromHash($request->doctor_id);
            if ($doctorId) {
                $query = $query->where('doctor_id', $doctorId);
            }
        }

        // Filter by room if provided
        if ($request->has('room_id') && $request->room_id != '') {
            $roomId = $this->getIdFromHash($request->room_id);
            if ($roomId) {
                $query = $query->where('room_id', $roomId);
            }
        }

        // Filter by status (checked_in/checked_out)
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'checked_in') {
                $query = $query->whereNull('check_out_datetime');
            } elseif ($request->status === 'checked_out') {
                $query = $query->whereNotNull('check_out_datetime');
            }
        }

        // Filter by date range
        if ($request->has('dates') && $request->dates != '') {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereDate('check_in_datetime', '>=', $startDate)
                           ->whereDate('check_in_datetime', '<=', $endDate);
        }

        return $query;
    }

    public function storing($data)
    {
        $company = company();
        
        // Add company_id and convert hash IDs
        $data['company_id'] = $company->id;
        
        if (isset($data['patient_id'])) {
            $patientId = $this->getIdFromHash($data['patient_id']);
            if (!$patientId) {
                throw new ApiException('Invalid patient ID provided.');
            }
            $data['patient_id'] = $patientId;
        }

        if (isset($data['doctor_id']) && $data['doctor_id']) {
            $doctorId = $this->getIdFromHash($data['doctor_id']);
            $data['doctor_id'] = $doctorId;
        }

        if (isset($data['room_id']) && $data['room_id']) {
            $roomId = $this->getIdFromHash($data['room_id']);
            $data['room_id'] = $roomId;
        }

        // Set check_in_datetime to now if not provided
        if (!isset($data['check_in_datetime'])) {
            $data['check_in_datetime'] = Carbon::now();
        }

        return $data;
    }

    public function checkOut(Request $request, $id)
    {
        $checkInId = $this->getIdFromHash($id);
        
        if (!$checkInId) {
            throw new ApiException('Invalid check-in ID provided.');
        }

        $checkIn = PatientCheckIn::findOrFail($checkInId);

        if ($checkIn->check_out_datetime) {
            throw new ApiException('Patient is already checked out.');
        }

        $checkOutTime = $request->check_out_datetime ? 
            Carbon::parse($request->check_out_datetime) : 
            Carbon::now();

        $checkIn->check_out_datetime = $checkOutTime;
        $checkIn->comments = $request->comments ?? $checkIn->comments;
        $checkIn->save();

        return ApiResponse::make('Patient checked out successfully', $checkIn);
    }

    public function getActiveCheckIns()
    {
        $activeCheckIns = PatientCheckIn::with(['patient', 'doctor', 'room'])
            ->whereNull('check_out_datetime')
            ->orderBy('check_in_datetime', 'desc')
            ->get();

        return ApiResponse::make('Active check-ins retrieved successfully', $activeCheckIns);
    }
}
