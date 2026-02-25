<?php

namespace App\Http\Controllers\Api;

use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Traits\CompanyTraits;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\DoctorSpecialty\IndexRequest;
use App\Http\Requests\Api\DoctorSpecialty\StoreRequest;
use App\Http\Requests\Api\DoctorSpecialty\DeleteRequest;
use App\Http\Requests\Api\DoctorSpecialty\UpdateRequest;
use App\Http\Requests\Api\DoctorSpecialty\AssignSpecialtiesRequest;
use App\Traits\SelectOptionsTraits;

class DoctorSpecialtyController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;
    
    protected $model = DoctorSpecialty::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Status filter
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('status', $request->status);
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('doctor_specialties.created_at >= ?', [$startDate])
                ->whereRaw('doctor_specialties.created_at <= ?', [$endDate]);
        }

        return $query;
    }

    public function assignSpecialties(AssignSpecialtiesRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $doctorId = $this->getIdFromHash($request->doctor_id);
            $doctor = Doctor::find($doctorId);
            
            if (!$doctor) {
                throw new ApiException('Doctor not found');
            }
            
            // Get specialty_ids and ensure it's an array
            $specialtyIdsInput = $request->specialty_ids;
            
            // Handle JSON string (if sent from form-data)
            if (is_string($specialtyIdsInput) && $this->isJson($specialtyIdsInput)) {
                $specialtyIdsInput = json_decode($specialtyIdsInput, true);
            }
            
            // Final validation check
            if (empty($specialtyIdsInput) || !is_array($specialtyIdsInput)) {
                throw new ApiException('Invalid specialty IDs provided. Must be an array.');
            }

            // Convert all specialty_ids from hash to regular ids
            $specialtyIds = [];
            foreach ($specialtyIdsInput as $specialtyXid) {
                // Convert XID to ID
                $specialtyIds[] = $this->getIdFromHash($specialtyXid);
            }

            // Sync specialties for the doctor
            $doctor->specialties()->sync($specialtyIds);
            
            DB::commit();

            return ApiResponse::make('Specialties assigned successfully', [
                'doctor_id' => $doctor->xid,
                'specialty_count' => count($specialtyIds)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if a string is valid JSON
     * 
     * @param string $string
     * @return bool
     */
    private function isJson($string) {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function getAllSpecialties()
    {
        $specialties = DoctorSpecialty::where('status', 1)->get();
        
        // Check if specialties are empty
        if ($specialties->isEmpty()) {
            return ApiResponse::make('No specialties found', []);
        }
        
        return ApiResponse::make('All Specialties', $specialties->toArray());
    }

    public function getDoctorSpecialties($id)
    {
        try {
            $doctorId = $this->getIdFromHash($id);
            $doctor = Doctor::find($doctorId);
            
            if (!$doctor) {
                throw new ApiException('Doctor not found');
            }
            
            $specialties = $doctor->specialties;
            
            return ApiResponse::make('Doctor Specialties', $specialties->toArray());
            
        } catch (\Exception $e) {
            throw new ApiException('Failed: ' . $e->getMessage());
        }
    }
}
