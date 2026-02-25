<?php
namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Appointment\IndexRequest;
use App\Http\Requests\Api\Appointment\StoreRequest;
use App\Http\Requests\Api\Appointment\UpdateRequest;
use App\Http\Requests\Api\Appointment\DeleteRequest;
use App\Services\NotificationService;
use App\Events\AppointmentUpdated;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class AppointmentController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Appointment::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Override store to add notification and broadcast
     */
    public function store(...$args)
    {
        $response = parent::store(...$args);
        
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getContent(), true);
            $appointmentXid = $data['xid'] ?? null;
            
            if ($appointmentXid) {
                $appointmentId = $this->getIdFromHash($appointmentXid);
                $appointment = Appointment::with(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription'])
                    ->where('id', $appointmentId)
                    ->first();
                    
                if ($appointment) {
                    // Notify receptionists about new appointment
                    NotificationService::appointmentCreated($appointment);
                    
                    // Note: Broadcasting is handled in the storing() hook to avoid duplicates
                }
            }
        }
        
        return $response;
    }

    /**
     * Override update to add notification and broadcast
     */
    public function update(...$args)
    {
        // Get the appointment XID from args (first parameter)
        $xid = $args[0] ?? null;
        $id = $xid ? $this->getIdFromHash($xid) : null;
        
        // Get the appointment before update to check for reschedule
        $appointment = $id ? Appointment::where('id', $id)->first() : null;
        $originalDate = $appointment ? $appointment->appointment_date : null;
        
        // Remove send_notification from the request so it isn't saved to the DB
        $sendNotification = request()->input('send_notification');
        request()->request->remove('send_notification');
        
        $response = parent::update(...$args);
        
        if ($response->getStatusCode() === 200 && $id) {
            $appointment = Appointment::with(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription'])
                ->where('id', $id)
                ->first();
                
            if ($appointment) {
                $request = request();
                $newDate = $appointment->appointment_date;
                
                // Check if date has changed (rescheduled)
                if ($originalDate && $originalDate != $newDate) {
                    if ($sendNotification !== false) {
                        NotificationService::appointmentRescheduled($appointment, $originalDate, $newDate);
                    }
                }
                
                // Note: Broadcasting is handled in the updating() hook to avoid duplicates
            }
        }
        
        return $response;
    }

    /**
     * Override destroy to add notification and broadcast
     */
    public function destroy(...$args)
    {
        // Get the appointment XID from args (first parameter)
        $xid = $args[0] ?? null;
        $id = $xid ? $this->getIdFromHash($xid) : null;
        
        // Get the appointment before deletion
        $appointment = $id ? Appointment::with(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription'])
            ->where('id', $id)
            ->first() : null;
        
        $response = parent::destroy(...$args);
        
        if ($response->getStatusCode() === 200 && $appointment) {
            // Notify receptionists about appointment cancellation
            NotificationService::appointmentCancelled($appointment);
            
            // Note: Broadcasting is handled in the deleting() hook to avoid duplicates
        }
        
        return $response;
    }
    
    /**
     * Override storing to add notification (legacy hook support)
     */
    public function storing($appointment)
    {
        // Load the patient.user relationship
        $appointment->load('patient.user');

        // Notify receptionists about new appointment
        NotificationService::appointmentCreated($appointment);
        
        // Broadcast event for real-time updates
        try {
            broadcast(new AppointmentUpdated($appointment, 'created'))->toOthers();
        } catch (\Exception $e) {
            // Log the broadcast error but don't fail the request
            \Log::warning('Failed to broadcast appointment created event', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $appointment;
    }

    /**
     * Override updating to detect rescheduling (legacy hook support)
     */
    public function updating($appointment)
    {
        // Load the patient.user relationship
        $appointment->load('patient.user');
        
        $request = request();
        $originalDate = $appointment->getOriginal('appointment_date');
        $newDate = $request->appointment_date ?? $appointment->appointment_date;
        
        // Check if date has changed (rescheduled)
        if ($originalDate != $newDate) {
            if ($request->input('send_notification') !== false && $request->input('send_notification') !== 'false') {
                NotificationService::appointmentRescheduled($appointment, $originalDate, $newDate);
            }
        }
        
        // Broadcast event for real-time updates
        try {
            broadcast(new AppointmentUpdated($appointment, 'updated'))->toOthers();
        } catch (\Exception $e) {
            // Log the broadcast error but don't fail the request
            \Log::warning('Failed to broadcast appointment updated event', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $appointment;
    }

    /**
     * Override deleting to add notification for cancellation (legacy hook support)
     */
    public function deleting($appointment)
    {
        // Load the patient.user relationship
        $appointment->load('patient.user');
        
        // Notify receptionists about appointment cancellation
        NotificationService::appointmentCancelled($appointment);
        
        // Broadcast event for real-time updates
        try {
            broadcast(new AppointmentUpdated($appointment, 'deleted'))->toOthers();
        } catch (\Exception $e) {
            // Log the broadcast error but don't fail the request
            \Log::warning('Failed to broadcast appointment deleted event', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $appointment;
    }

    public function modifyIndex($query)
    {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('appointments.appointment_date >= ?', [$startDate])
                ->whereRaw('appointments.appointment_date <= ?', [$endDate]);
        }

        return $query;
    }
    
    public function getStats(Request $request)
    {
        $dates = $request->dates;
        
        if ($dates || true /* For testing purposes, always true */) {
            // $dates = explode(',', $dates);
            // $startDate = trim($dates[0]);
            // $endDate = trim($dates[1]);
            
            $query = Appointment::where('company_id', company()->id);
            
            // $query->whereRaw('appointments.appointment_date >= ?', [$startDate])
            //       ->whereRaw('appointments.appointment_date <= ?', [$endDate]);
                  
            $totalAppointments = (clone $query)->count();
            $pendingAppointments = (clone $query)->where('status', 'pending')->count();
            $completedAppointments = (clone $query)->where('status', 'completed')->count();
            $cancelledAppointments = (clone $query)->where('status', 'cancelled')->count();
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalAppointmentsToday' => $totalAppointments,
                    'pendingAppointments' => $pendingAppointments,
                    'completedAppointments' => $completedAppointments,
                    'cancellations' => $cancelledAppointments
                ]
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Date range is required'
        ], 422);
    }

    /**
     * Get appointments filtered by status with optional doctor and date filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAppointmentsByStatus(Request $request)
    {
        $clinicContext = app('App\Services\ClinicContext');
        $currentClinicId = $clinicContext->getClinic();
        $isGlobalMode = $clinicContext->isGlobal();
        $today = now()->format('Y-m-d');

        $query = Appointment::with([
            'patient.user',
            'doctor.user',
            'doctor.doctorDepartment',
            'doctor.attendances' => function($query) use ($currentClinicId, $isGlobalMode) {
                $query->withoutGlobalScope('clinic')
                      ->latest('clock_time')
                      ->limit(10);
                
                if (!$isGlobalMode && $currentClinicId) {
                    $query->where('clinic_id', $currentClinicId);
                }
            },
            'doctor.doctorHolidays' => function($query) use ($today) {
                $query->where('start_date', '<=', $today)
                      ->where('end_date', '>=', $today)
                      ->where('status', 'approved');
            },
            'room',
            'treatmentType',
            'prescription.prescriptionItems'
        ]);
        
        $this->applyCompanyFilter($query);
        $this->applyDoctorFilter($query, $request->doctor_id);
        $this->applyDateFilter($query, $request->dates);
        
        $appointments = $query->whereNot('status', 'pending')
            ->orderBy('appointment_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Get all unique doctor user IDs
        $doctorUserIds = $appointments->pluck('doctor.user_id')->filter()->unique()->toArray();
        
        // Fetch current statuses for all doctors
        $doctorStatuses = \App\Models\UserStatus::whereIn('user_id', $doctorUserIds)
            ->select('user_id', 'status', 'notes', 'updated_at')
            ->get()
            ->groupBy('user_id')
            ->map(function($statusGroup) {
                return $statusGroup->sortByDesc('updated_at')->first();
            });

        // Enhance each appointment's doctor with current status
        $appointments->each(function($appointment) use ($doctorStatuses) {
            if ($appointment->doctor && $appointment->doctor->user_id) {
                $userStatus = $doctorStatuses->get($appointment->doctor->user_id);
                $statusFromTable = $userStatus ? $userStatus->status : 'available';
                
                // Check clock-in status from attendance records
                $isClockedIn = false;
                if ($appointment->doctor->attendances && $appointment->doctor->attendances->count() > 0) {
                    $lastClockIn = null;
                    $lastClockOut = null;
                    
                    foreach ($appointment->doctor->attendances as $attendance) {
                        if ($attendance->status === 'clock_in' && !$lastClockIn) {
                            $lastClockIn = $attendance;
                        } elseif ($attendance->status === 'clock_out' && !$lastClockOut) {
                            $lastClockOut = $attendance;
                        }
                        
                        if ($lastClockIn && $lastClockOut) {
                            break;
                        }
                    }
                    
                    if ($lastClockIn && (!$lastClockOut || $lastClockIn->clock_time > $lastClockOut->clock_time)) {
                        $isClockedIn = true;
                    }
                }
                
                // Check if on holiday
                $isOnHoliday = $appointment->doctor->doctorHolidays && $appointment->doctor->doctorHolidays->count() > 0;
                
                // Determine final status
                $isAvailable = $isClockedIn && !$isOnHoliday && $appointment->doctor->user->status === 'enabled';
                $currentStatus = $isAvailable ? $statusFromTable : 'off_duty';
                
                // Add status to doctor object
                $appointment->doctor->current_status = $currentStatus;
                $appointment->doctor->is_clocked_in = $isClockedIn;
                $appointment->doctor->is_available = $isAvailable;
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $appointments
        ]);
    }

    /**
     * Apply company filter to query if in authenticated context
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    private function applyCompanyFilter($query)
    {
        if (function_exists('company') && company()) {
            $query->where('company_id', company()->id);
        }
    }

    /**
     * Apply doctor filter to query if doctor_id is provided
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $doctorId
     * @return void
     */
    private function applyDoctorFilter($query, $doctorId)
    {
        if (!$doctorId) {
            return;
        }

        $decodedDoctorId = Hashids::decode($doctorId);
        
        if (!empty($decodedDoctorId)) {
            $query->where('doctor_id', $decodedDoctorId[0]);
        }
    }

    /**
     * Apply date filter to query. If no dates provided, defaults to today in company timezone
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates
     * @return void
     */
    private function applyDateFilter($query, $dates)
    {
        if ($this->hasValidDateRange($dates)) {
            $this->applyDateRangeFilter($query, $dates);
        } else {
            $this->applyTodayFilter($query);
        }
    }

    /**
     * Check if the provided dates string contains a valid date range
     *
     * @param string|null $dates
     * @return bool
     */
    private function hasValidDateRange($dates)
    {
        if (!$dates || $dates === "") {
            return false;
        }

        $datesArray = explode(',', $dates);
        return count($datesArray) >= 2;
    }

    /**
     * Apply date range filter to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $dates
     * @return void
     */
    private function applyDateRangeFilter($query, $dates)
    {
        $datesArray = explode(',', $dates);
        $startDate = trim($datesArray[0]);
        $endDate = trim($datesArray[1]);
        
        $query->whereRaw('appointments.appointment_date >= ?', [$startDate])
              ->whereRaw('appointments.appointment_date <= ?', [$endDate]);
    }

    /**
     * Apply filter for today's date in company timezone
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    private function applyTodayFilter($query)
    {
        $companyTimezone = $this->getCompanyTimezone();
        $today = now($companyTimezone)->format('Y-m-d');
        
        $query->whereDate('appointment_date', $today);
    }

    /**
     * Get the company's timezone or default application timezone
     *
     * @return string|null
     */
    private function getCompanyTimezone()
    {
        if (function_exists('company') && company() && isset(company()->timezone)) {
            return company()->timezone;
        }

        return null;
    }

    /**
     * Format a collection of appointments
     * 
     * @param \Illuminate\Support\Collection $appointments
     * @return array
     */
    private function formatAppointments($appointments)
    {
        return $appointments->map(function ($appointment) {
            return [
                'xid' => $appointment->xid,
                'patient_name' => $appointment->patient ? $appointment->patient->name : null,
                'doctor_name' => $appointment->doctor ? $appointment->doctor->name : null,
                'room_name' => $appointment->room ? $appointment->room->name : null,
                'treatment_name' => $appointment->treatmentType ? $appointment->treatmentType->name : null,
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'status' => $appointment->status,
                'flow_status' => $appointment->flow_status,
                'appointment_notes' => $appointment->appointment_notes,
                'reason_visit' => $appointment->reason_visit,
                'arrive_datetime' => $appointment->arrive_datetime,
                'checkin_datetime' => $appointment->checkin_datetime,
                'in_progress_datetime' => $appointment->in_progress_datetime,
                'completed_datetime' => $appointment->completed_datetime,
                'checkout_datetime' => $appointment->checkout_datetime,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at
            ];
        })->toArray();
    }

    public function updateStatus(Request $request, $xid)
    {
        $request->validate([
            'flow_status' => 'required|string|in:checked_in,in_progress,completed,checked_out',
            'appointment_notes' => 'nullable|string',
            'reason_visit' => 'nullable|string'
        ]);

        // Usar el método para obtener datos desde hash
        $appointmentId = $this->getIdFromHash($xid);
        
        $query = Appointment::where('id', $appointmentId);
        
        // Solo aplicar filtro de empresa si estamos en contexto autenticado
        if (function_exists('company') && company()) {
            $query->where('company_id', company()->id);
        }
        
        $appointment = $query->firstOrFail();

        $now = now();
        
        // Initialize update data (no more status_appointment field)
        $updateData = [];

        // Agregar notas si se proporcionan
        if ($request->has('appointment_notes')) {
            $updateData['appointment_notes'] = $request->appointment_notes;
        }

        if ($request->has('reason_visit')) {
            $updateData['reason_visit'] = $request->reason_visit;
        }

        // Validate status flow transitions
        // Flow: checked_in -> in_progress -> completed -> checked_out
        $this->validateStatusTransition($appointment, $request->flow_status);

        // Manejar timestamps automáticos según el status
        switch ($request->flow_status) {
            case 'checked_in':
                if (!$appointment->arrive_datetime) {
                    $updateData['arrive_datetime'] = $now;
                }
                $updateData['checkin_datetime'] = $now;
                break;
                
            case 'in_progress':
                // Validate that patient is checked in first
                if (!$appointment->checkin_datetime && !$appointment->arrive_datetime) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Patient must be checked in before starting treatment'
                    ], 422);
                }
                $updateData['in_progress_datetime'] = $now;
                break;
                
            case 'completed':
                // Validate that treatment is in progress first
                if (!$appointment->in_progress_datetime) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Treatment must be in progress before completing'
                    ], 422);
                }
                $updateData['completed_datetime'] = $now;
                // Update the booking status to completed as well
                $updateData['status'] = 'completed';
                break;
                
            case 'checked_out':
                // Validate that treatment is completed first
                if (!$appointment->completed_datetime) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Treatment must be completed before checking out'
                    ], 422);
                }
                $updateData['checkout_datetime'] = $now;
                // Ensure status is completed
                $updateData['status'] = 'completed';
                break;
        }

        $appointment->update($updateData);
        
        // Reload appointment with relationships including patient.user
        $appointment = $appointment->fresh(['patient.user', 'doctor.user']);

        // Send notifications based on status change
        switch ($request->flow_status) {
            case 'checked_in':
                // Notify doctor that patient has arrived
                NotificationService::patientCheckedIn($appointment);
                break;
                
            case 'completed':
                // Notify all receptionists
                NotificationService::appointmentCompleted($appointment);
                break;
        }

        // Broadcast event for real-time updates
        $freshAppointment = $appointment->fresh(['patient.user', 'doctor.user', 'room', 'treatmentType', 'prescription']);
        try {
            broadcast(new AppointmentUpdated($freshAppointment, 'status_changed'))->toOthers();
        } catch (\Exception $e) {
            // Log the broadcast error but don't fail the request
            \Log::warning('Failed to broadcast appointment status changed event', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment status updated successfully',
            'data' => $freshAppointment
        ]);
    }

    /**
     * Validate status transition follows the proper flow
     * 
     * @param Appointment $appointment
     * @param string $newStatus
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateStatusTransition($appointment, $newStatus)
    {
        // Determine current status based on datetime fields
        $currentStatus = $this->getCurrentStatus($appointment);
        
        // Define valid transitions
        $validTransitions = [
            'scheduled' => ['checked_in'],
            'checked_in' => ['in_progress'],
            'in_progress' => ['completed'],
            'completed' => ['checked_out'],
            'checked_out' => [] // No transitions allowed after checkout
        ];
        
        // Check if transition is valid
        if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            $errorMessages = [
                'scheduled' => 'Patient must be checked in first',
                'checked_in' => 'Patient must be checked in before starting treatment',
                'in_progress' => 'Treatment must be in progress before completing',
                'completed' => 'Treatment must be completed before checking out',
                'checked_out' => 'Appointment is already checked out'
            ];
            
            abort(422, $errorMessages[$currentStatus] ?? 'Invalid status transition');
        }
    }

    /**
     * Get the current status of an appointment based on datetime fields
     * 
     * @param Appointment $appointment
     * @return string
     */
    private function getCurrentStatus($appointment)
    {
        if ($appointment->checkout_datetime) {
            return 'checked_out';
        }
        if ($appointment->completed_datetime) {
            return 'completed';
        }
        if ($appointment->in_progress_datetime) {
            return 'in_progress';
        }
        if ($appointment->checkin_datetime || $appointment->arrive_datetime) {
            return 'checked_in';
        }
        return 'scheduled';
    }
}
