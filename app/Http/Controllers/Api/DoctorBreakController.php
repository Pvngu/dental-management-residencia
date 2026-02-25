<?php

namespace App\Http\Controllers\Api;

use App\Models\DoctorBreak;
use App\Models\Doctor;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\DoctorBreak\IndexRequest;
use App\Http\Requests\Api\DoctorBreak\StoreRequest;
use App\Http\Requests\Api\DoctorBreak\UpdateRequest;
use App\Http\Requests\Api\DoctorBreak\DeleteRequest;

class DoctorBreakController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = DoctorBreak::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Filter by doctor_id or doctor_ids (supports multiple doctors)
        $doctorIdsParam = $request->has('doctor_ids') ? 'doctor_ids' : 'doctor_id';
        
        if ($request->has($doctorIdsParam) && $request->$doctorIdsParam != '') {
            $doctorIds = is_array($request->$doctorIdsParam) 
                ? $request->$doctorIdsParam 
                : explode(',', $request->$doctorIdsParam);
            
            if (!empty($doctorIds)) {
                // Decode hashids to real IDs
                $decodedIds = [];
                foreach ($doctorIds as $hashId) {
                    $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hashId);
                    if (!empty($decoded)) {
                        $decodedIds[] = $decoded[0];
                    }
                }
                
                if (!empty($decodedIds)) {
                    $query = $query->whereIn('doctor_id', $decodedIds);
                }
            }
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('doctor_breaks.date >= ?', [$startDate])
                ->whereRaw('doctor_breaks.date <= ?', [$endDate]);
        }

        return $query;
    }

    public function getDoctorsWithBreaks()
    {
        $company = company();

        // Get all doctors with their breaks count
        $doctors = Doctor::with(['user'])
            ->where('company_id', $company->id)
            ->withCount([
                'breaks as total_breaks_count',
                'breaks as everyday_breaks_count' => function ($query) {
                    $query->where('every_day', 1);
                },
                'breaks as single_day_breaks_count' => function ($query) {
                    $query->where('every_day', 0);
                }
            ])
            ->get();

        return response()->json($doctors);
    }

    public function store()
    {
        $request = $this->storeRequest ? app($this->storeRequest) : request();
        $data = $request->validated();

        // Extract doctor_id array
        $doctorIds = is_array($data['doctor_id']) ? $data['doctor_id'] : [$data['doctor_id']];
        unset($data['doctor_id']);

        $company = company();
        $createdBreaks = [];

        // Create a break for each doctor
        foreach ($doctorIds as $doctorId) {
            $breakData = array_merge($data, [
                'doctor_id' => $doctorId,
                'company_id' => $company->id,
            ]);

            $break = DoctorBreak::create($breakData);
            $createdBreaks[] = $break;
        }

        // Return the first created break (for consistency with the frontend)
        $firstBreak = $createdBreaks[0];
        return response()->json([
            'data' => [
                'xid' => $firstBreak->xid,
                'message' => count($createdBreaks) > 1 
                    ? 'Breaks created successfully for ' . count($createdBreaks) . ' doctors'
                    : 'Break created successfully'
            ]
        ]);
    }
}
