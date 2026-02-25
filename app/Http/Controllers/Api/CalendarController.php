<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Models\CalendarEvent;
use App\Models\Doctor;
use App\Models\DoctorBreak;
use App\Models\DoctorHoliday;
use App\Models\TreatmentType;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Appointment\IndexRequest;
use App\Http\Requests\Api\Appointment\StoreRequest;
use App\Http\Requests\Api\Appointment\UpdateRequest;
use App\Http\Requests\Api\Appointment\DeleteRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Appointment::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    /**
     * Get calendar data with appointments and doctors for a specific date range
     */
    public function getCalendarData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $doctorId = $request->input('doctor_id');
        $doctorIds = $request->input('doctor_ids');
        $realDoctorIds = [];
        if ($doctorIds) {
            $hashIds = explode(',', $doctorIds);
            foreach ($hashIds as $hashId) {
                $id = \App\Classes\Common::getIdFromHash($hashId);
                if ($id) $realDoctorIds[] = $id;
            }
        } elseif ($doctorId) {
            $id = \App\Classes\Common::getIdFromHash($doctorId);
            if ($id) $realDoctorIds[] = $id;
        }
        $clinicId = $request->has('clinic_id') ? \App\Classes\Common::getIdFromHash($request->clinic_id) : null;

        // Get doctors with their user information, breaks, and holidays
        $doctorsQuery = Doctor::with(['user', 'doctorDepartment', 'doctorSchedules'])
            ->whereHas('user', function($query) {
                $query->where('status', 'enabled');
            });



        if ($clinicId) {
            $doctorsQuery->whereHas('user.clinics', function($q) use ($clinicId) {
                $q->where('clinic_locations.id', $clinicId);
            });
        }

        $allDoctorsCount = $doctorsQuery->count();
        \Illuminate\Support\Facades\Log::info("Calendar API Fetched Doctors Count: " . $allDoctorsCount . " | doctorIds filter present: " . ($request->has('doctor_ids') ? 'yes' : 'no') . " | clinicId: " . $clinicId);

        $doctors = $doctorsQuery->get()->map(function($doctor) use ($startDate, $endDate) {
            // Check if doctor is on holiday for the date range
            $isOnHoliday = DoctorHoliday::where('doctor_id', $doctor->id)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            // Get doctor breaks for the date range
            $breaks = DoctorBreak::where('doctor_id', $doctor->id)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->where('every_day', 1)
                        ->orWhereBetween('date', [$startDate, $endDate]);
                })
                ->get()
                ->map(function($break) {
                    return [
                        'xid' => $break->xid,
                        'break_from' => $break->break_from,
                        'break_to' => $break->break_to,
                        'every_day' => $break->every_day,
                        'date' => $break->date,
                    ];
                });

            return [
                'xid' => $doctor->xid,
                'name' => $doctor->user->name . ' ' . $doctor->user->last_name,
                'email' => $doctor->user->email,
                'phone' => $doctor->user->phone,
                'image' => $doctor->user->profile_image_url,
                'available' => !$isOnHoliday,
                'is_on_holiday' => $isOnHoliday,
                'specialist' => $doctor->specialist,
                'qualification' => $doctor->qualification,
                'department' => $doctor->doctorDepartment ? $doctor->doctorDepartment->name : null,
                'appointment_charge' => $doctor->appointment_charge,
                'breaks' => $breaks,
            ];
        });

        // Get appointments for the date range
        $appointmentsQuery = Appointment::with(['patient.user', 'doctor.user', 'treatmentType'])
            ->whereBetween('appointment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if (!empty($realDoctorIds)) {
            $appointmentsQuery->whereHas('doctor', function($query) use ($realDoctorIds) {
                $query->whereIn('id', $realDoctorIds);
            });
        }

        if ($clinicId) {
            $appointmentsQuery->where('clinic_id', $clinicId);
        }

        // Order appointments consistently
        $appointments = $appointmentsQuery
            ->orderBy('appointment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get()->map(function($appointment) {
            return [
                'id' => $appointment->xid,
                'dentist_id' => $appointment->doctor->xid,
                'patient_id' => $appointment->patient->xid,
                'patient_name' => $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name,
                'patient_phone' => $appointment->patient->user->phone,
                'doctor_name' => $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'start_time' => $appointment->appointment_date->format('h:i A'),
                'end_time' => $appointment->appointment_date->addMinutes($appointment->duration)->format('h:i A'),
                'duration' => $appointment->duration,
                'treatment_details' => $appointment->treatment_details,
                'treatment_type' => $appointment->treatmentType ? $appointment->treatmentType->name : 'General',
                'status' => $appointment->status,
                'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
                'clinic_id' => $appointment->x_clinic_id,
            ];
        });

        // Get doctor holidays for the date range
        $holidays = DoctorHoliday::with('doctor.user')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->get()
            ->map(function($holiday) {
                return [
                    'xid' => $holiday->xid,
                    'doctor_id' => $holiday->doctor->xid,
                    'doctor_name' => $holiday->doctor->user->name . ' ' . $holiday->doctor->user->last_name,
                    'start_date' => $holiday->start_date,
                    'end_date' => $holiday->end_date,
                    'reason' => $holiday->reason ?? 'Holiday',
                    'holiday_type' => $holiday->holiday_type ?? 'vacation',
                ];
            });

        $eventsQuery = CalendarEvent::with('patient.user')->whereBetween('event_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        if (!empty($realDoctorIds)) {
            $eventsQuery->whereHas('doctor', function($query) use ($realDoctorIds) {
                $query->whereIn('id', $realDoctorIds);
            });
        }
        
        if ($clinicId) {
            $eventsQuery->where('clinic_id', $clinicId);
        }
        
        $calendarEvents = $eventsQuery->orderBy('event_date', 'asc')->get()->map(function($ev) {
            // Need doctor relationship to get XID
            return [
                'id' => $ev->xid,
                'dentist_id' => $ev->doctor ? $ev->doctor->xid : null,
                'patient_id' => $ev->patient ? $ev->patient->xid : null,
                'patient_name' => $ev->patient && $ev->patient->user ? trim($ev->patient->user->name . ' ' . $ev->patient->user->last_name) : null,
                'title' => $ev->title,
                'appointment_date' => $ev->event_date->format('Y-m-d'),
                'start_time' => $ev->event_date->format('h:i A'),
                'end_time' => $ev->event_date->copy()->addMinutes($ev->duration)->format('h:i A'),
                'duration' => $ev->duration,
                'color' => $ev->color,
                'description' => $ev->description,
                'type' => 'event',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'doctors' => $doctors,
                'appointments' => $appointments,
                'events' => $calendarEvents,
                'holidays' => $holidays,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]
        ]);
    }

    /**
     * Get treatment types for dropdown
     */
    public function getTreatmentTypes()
    {
        $treatmentTypes = TreatmentType::select('name', 'description', 'duration_minutes', 'price')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $treatmentTypes
        ]);
    }

    /**
     * Get calendar statistics
     */
    public function getCalendarStats(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        $totalAppointments = Appointment::whereDate('appointment_date', $date)->count();
        $completedAppointments = Appointment::whereDate('appointment_date', $date)
            ->where('status', 'completed')->count();
        $pendingAppointments = Appointment::whereDate('appointment_date', $date)
            ->where('status', 'pending')->count();
        $cancelledAppointments = Appointment::whereDate('appointment_date', $date)
            ->where('status', 'cancelled')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'pending_appointments' => $pendingAppointments,
                'cancelled_appointments' => $cancelledAppointments,
                'date' => $date
            ]
        ]);
    }

    /**
     * Get doctor breaks for a specific doctor and date range
     */
    public function getDoctorBreaks(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $breaksQuery = DoctorBreak::with('doctor.user');

        if ($doctorId) {
            $breaksQuery->whereHas('doctor', function($query) use ($doctorId) {
                $query->where('xid', $doctorId);
            });
        }

        $breaksQuery->where(function($query) use ($startDate, $endDate) {
            $query->where('every_day', 1)
                ->orWhereBetween('date', [$startDate, $endDate]);
        });

        $breaks = $breaksQuery->get()->map(function($break) {
            return [
                'xid' => $break->xid,
                'doctor_id' => $break->doctor->xid,
                'doctor_name' => $break->doctor->user->name . ' ' . $break->doctor->user->last_name,
                'break_from' => $break->break_from,
                'break_to' => $break->break_to,
                'every_day' => $break->every_day,
                'date' => $break->date,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $breaks
        ]);
    }

    /**
     * Get doctor holidays for a specific doctor and date range
     */
    public function getDoctorHolidays(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $holidaysQuery = DoctorHoliday::with('doctor.user')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            });

        if ($doctorId) {
            $holidaysQuery->whereHas('doctor', function($query) use ($doctorId) {
                $query->where('xid', $doctorId);
            });
        }

        $holidays = $holidaysQuery->get()->map(function($holiday) {
            return [
                'xid' => $holiday->xid,
                'doctor_id' => $holiday->doctor->xid,
                'doctor_name' => $holiday->doctor->user->name . ' ' . $holiday->doctor->user->last_name,
                'start_date' => $holiday->start_date,
                'end_date' => $holiday->end_date,
                'reason' => $holiday->reason ?? 'Holiday',
                'holiday_type' => $holiday->holiday_type ?? 'vacation',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $holidays
        ]);
    }

    /**
     * Check if a time slot is available for a doctor
     */
    public function checkSlotAvailability(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $date = $request->input('date');
        $time = $request->input('time'); // Format: HH:MM

        // Check if doctor is on holiday
        $isOnHoliday = DoctorHoliday::whereHas('doctor', function($query) use ($doctorId) {
            $query->where('xid', $doctorId);
        })
        ->where('start_date', '<=', $date)
        ->where('end_date', '>=', $date)
        ->exists();

        if ($isOnHoliday) {
            return response()->json([
                'success' => true,
                'data' => [
                    'available' => false,
                    'reason' => 'Doctor is on holiday'
                ]
            ]);
        }

        // Check if time slot is during a break
        $isBreakTime = DoctorBreak::whereHas('doctor', function($query) use ($doctorId) {
            $query->where('xid', $doctorId);
        })
        ->where(function($query) use ($date, $time) {
            $query->where('every_day', 1)
                ->orWhere('date', $date);
        })
        ->where('break_from', '<=', $time)
        ->where('break_to', '>', $time)
        ->exists();

        if ($isBreakTime) {
            return response()->json([
                'success' => true,
                'data' => [
                    'available' => false,
                    'reason' => 'Doctor is on break'
                ]
            ]);
        }

        // Check if there's already an appointment at this time
        $hasAppointment = Appointment::whereHas('doctor', function($query) use ($doctorId) {
            $query->where('xid', $doctorId);
        })
        ->whereDate('appointment_date', $date)
        ->whereTime('appointment_date', $time)
        ->exists();

        if ($hasAppointment) {
            return response()->json([
                'success' => true,
                'data' => [
                    'available' => false,
                    'reason' => 'Time slot already booked'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'available' => true,
                'reason' => null
            ]
        ]);
    }

    // Override the modifyIndex method to customize the query for the index request
    /**
     * Modify the query for the index request.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function modifyIndex($query) {
        $request = request();

        // Date range filtering
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $query->whereBetween('appointment_date', [$startDate, $endDate]);
        } elseif ($request->has('appointment_date')) {
            $date = $request->input('appointment_date');
            $query->whereDate('appointment_date', $date);
        }

        // Doctor filtering
        if ($request->has('doctor_id')) {
            $query->whereHas('doctor', function($q) use ($request) {
                $q->where('xid', $request->input('doctor_id'));
            });
        }

        // Status filtering
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Include related data
        $query->with(['patient.user', 'doctor.user', 'treatmentType']);

        return $query;
    }
}
