<?php

namespace App\Http\Controllers\Api;

use App\Models\DoctorHoliday;
use App\Models\Doctor;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\DoctorHoliday\IndexRequest;
use App\Http\Requests\Api\DoctorHoliday\StoreRequest;
use App\Http\Requests\Api\DoctorHoliday\UpdateRequest;
use App\Http\Requests\Api\DoctorHoliday\DeleteRequest;
use Illuminate\Support\Facades\DB;

class DoctorHolidayController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = DoctorHoliday::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Filter by doctor_id (supports multiple doctors)
        if ($request->has('doctor_id') && $request->doctor_id != '') {
            $doctorIds = is_array($request->doctor_id) 
                ? $request->doctor_id 
                : explode(',', $request->doctor_id);
            
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

        return $query;
    }

    public function getDoctorsWithHolidays()
    {
        $company = company();

        // Get all doctors with their holidays count and upcoming holidays
        $doctors = Doctor::with(['user'])
            ->where('company_id', $company->id)
            ->withCount([
                'holidays as total_holidays_count',
                'holidays as upcoming_holidays_count' => function ($query) {
                    $query->where('start_date', '>=', now()->toDateString());
                },
                'holidays as pending_holidays_count' => function ($query) {
                    $query->where('status', 'pending');
                },
                'holidays as approved_holidays_count' => function ($query) {
                    $query->where('status', 'approved');
                }
            ])
            ->get();

        return response()->json($doctors);
    }

    public function getCalendarEvents()
    {
        $request = request();
        $company = company();

        $query = DoctorHoliday::with(['doctor.user'])
            ->where('company_id', $company->id);

        // Filter by doctor_id if provided
        if ($request->has('doctor_id') && $request->doctor_id != '') {
            $doctorIds = is_array($request->doctor_id) 
                ? $request->doctor_id 
                : explode(',', $request->doctor_id);
            
            if (!empty($doctorIds)) {
                // Decode hashids to real IDs
                $decodedDoctorIds = [];
                foreach ($doctorIds as $hashId) {
                    $decoded = \Vinkla\Hashids\Facades\Hashids::decode($hashId);
                    if (!empty($decoded)) {
                        $decodedDoctorIds[] = $decoded[0];
                    }
                }
                
                if (!empty($decodedDoctorIds)) {
                    $query = $query->whereIn('doctor_id', $decodedDoctorIds);
                }
            }
        }

        // Filter by date range if provided
        if ($request->has('start') && $request->has('end')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start, $request->end])
                  ->orWhereBetween('end_date', [$request->start, $request->end])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('start_date', '<=', $request->start)
                         ->where('end_date', '>=', $request->end);
                  });
            });
        }

        $holidays = $query->get();

        // Format for FullCalendar
        $events = $holidays->map(function ($holiday) {
            $statusColors = [
                'pending' => '#faad14',
                'approved' => '#52c41a',
                'rejected' => '#ff4d4f',
            ];

            return [
                'id' => $holiday->xid,
                'title' => $holiday->doctor->user->name . ' ' . $holiday->doctor->user->last_name . ' - ' . ($holiday->reason ?: 'Holiday'),
                'start' => $holiday->start_date,
                'end' => date('Y-m-d', strtotime($holiday->end_date . ' +1 day')), // FullCalendar end is exclusive
                'backgroundColor' => $statusColors[$holiday->status] ?? '#1890ff',
                'borderColor' => $statusColors[$holiday->status] ?? '#1890ff',
                'extendedProps' => [
                    'holiday_type' => $holiday->holiday_type,
                    'status' => $holiday->status,
                    'reason' => $holiday->reason,
                    'doctor_id' => $holiday->x_doctor_id,
                    'doctor_name' => trim($holiday->doctor->user->name . ' ' . ($holiday->doctor->user->last_name ?? '')),
                    'holiday_xid' => $holiday->xid,
                ]
            ];
        });

        return response()->json($events);
    }

    public function store()
    {
        $request = $this->storeRequest ? app($this->storeRequest) : request();
        $data = $request->validated();

        // Extract doctor_id array
        $doctorIds = $data['doctor_id'];
        unset($data['doctor_id']);

        $company = company();
        $createdHolidays = [];

        // Create a holiday for each doctor
        foreach ($doctorIds as $doctorId) {
            $holidayData = array_merge($data, [
                'doctor_id' => $doctorId,
                'company_id' => $company->id,
            ]);

            $holiday = DoctorHoliday::create($holidayData);
            $createdHolidays[] = $holiday;
        }

        // Return the first created holiday (for consistency with the frontend)
        $firstHoliday = $createdHolidays[0];
        return response()->json([
            'data' => [
                'xid' => $firstHoliday->xid,
                'message' => count($createdHolidays) > 1 
                    ? 'Holidays created successfully for ' . count($createdHolidays) . ' doctors'
                    : 'Holiday created successfully'
            ]
        ]);
    }

    public function update(...$args)
    {
        $id = $args[0];
        $request = $this->updateRequest ? app($this->updateRequest) : request();
        $data = $request->validated();

        // Decode hashid to get real ID
        $decodedId = \Vinkla\Hashids\Facades\Hashids::decode($id);
        $realId = $decodedId[0];

        $holiday = DoctorHoliday::where('id', $realId)->firstOrFail();
        $holiday->update($data);

        return response()->json([
            'data' => [
                'xid' => $holiday->xid,
                'message' => 'Holiday updated successfully'
            ]
        ]);
    }
}
