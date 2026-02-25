<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Models\Role;
use App\Models\User;
use App\Models\Doctor;
use App\Models\UserStatus;
use App\Models\DoctorSpecialty;
use App\Models\DoctorScheduleDay;
use App\Models\DoctorHoliday;
use App\Scopes\CompanyScope;
use App\Traits\CompanyTraits;
use App\Traits\AddressTraits;
use App\Traits\UserTraits;
use App\Classes\Files;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Http\Requests\Api\Doctor\IndexRequest;
use App\Http\Requests\Api\Doctor\StoreRequest;
use App\Http\Requests\Api\Doctor\DeleteRequest;
use App\Http\Requests\Api\Doctor\UpdateRequest;
use App\Traits\SelectOptionsTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorController extends ApiBaseController
{
    use CompanyTraits, AddressTraits, UserTraits {
        CompanyTraits::storing insteadof UserTraits;
    }
    
    protected $model = Doctor::class;
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
            if (count($dates) >= 2) {
                $startDate = $dates[0];
                $endDate = $dates[1];

                $query = $query->whereRaw('doctors.created_at >= ?', [$startDate])
                    ->whereRaw('doctors.created_at <= ?', [$endDate]);
            }
        }

        // Search functionality - search across doctor and user fields
        if ($request->has('searchString') && $request->searchString != "") {
            $searchString = $request->searchString;
            
            $query = $query->where(function($q) use ($searchString, $request) {
                // Search in doctor fields
                $q->where('doctors.qualification', 'LIKE', '%' . $searchString . '%')
                  ->orWhere('doctors.designation', 'LIKE', '%' . $searchString . '%')
                  ->orWhere('doctors.specialist', 'LIKE', '%' . $searchString . '%')
                  // Search in user fields (name, email, phone)
                  ->orWhereHas('user', function($userQuery) use ($searchString) {
                      $userQuery->where('name', 'LIKE', '%' . $searchString . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $searchString . '%')
                                ->orWhere('email', 'LIKE', '%' . $searchString . '%')
                                ->orWhere('phone', 'LIKE', '%' . $searchString . '%')
                                ->orWhere(DB::raw("CONCAT(name, ' ', last_name)"), 'LIKE', '%' . $searchString . '%');
                  });
            });
        }

        // Filter by status (from user table)
        if ($request->has('status') && $request->status != "") {
            $query = $query->whereHas('user', function($userQuery) use ($request) {
                $userQuery->where('status', $request->status);
            });
        }

        // $query->profile_image = Files::getFileS3Url($query->profile_image);

        return $query;
    }

    public function getStatus(Request $request)
    {
        $user = user();
        
        // Get the doctor record from the authenticated user
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return ApiResponse::make('Doctor not found', [], 404);
        }

        // Get the current status from user_statuses table
        $userStatus = UserStatus::where('user_id', $user->id)
            ->latest()
            ->first();

        $status = $userStatus ? $userStatus->status : 'available';

        return ApiResponse::make('Success', [
            'doctor_id' => $doctor->xid,
            'status' => $status,
            'updated_at' => $userStatus ? $userStatus->updated_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get detailed doctor information with all relationships for displaying in modal
     * 
     * @param string $xid Doctor's hashed ID
     * @return mixed
     */
    public function getDoctorInfo($xid)
    {
        try {
            $clinicContext = app('App\Services\ClinicContext');
            $currentClinicId = $clinicContext->getClinic();
            $isGlobalMode = $clinicContext->isGlobal();
            
            // Get company timezone for accurate date/time calculations
            $company = company();
            $timezone = $company && $company->timezone ? $company->timezone : config('app.timezone');
            $today = now($timezone)->format('Y-m-d');

            // Get the doctor by xid
            $doctorId = $this->getIdFromHash($xid);
            $doctor = Doctor::with([
                'user',
                'doctorDepartment',
                'specialties',
                'attendances' => function($query) use ($currentClinicId, $isGlobalMode) {
                    $query->withoutGlobalScope('clinic')
                          ->latest('clock_time')
                          ->limit(10);
                    
                    if (!$isGlobalMode && $currentClinicId) {
                        $query->where('clinic_id', $currentClinicId);
                    }
                },
                'doctorHolidays' => function($query) use ($today) {
                    $query->where('start_date', '<=', $today)
                          ->where('end_date', '>=', $today)
                          ->where('status', 'approved');
                }
            ])->find($doctorId);
            
            if (!$doctor) {
                return ApiResponse::make('Doctor not found', [], 404);
            }

            // Get current status from user_statuses table
            $userStatus = UserStatus::where('user_id', $doctor->user_id)
                ->latest()
                ->first();

            $statusFromTable = $userStatus ? $userStatus->status : 'available';

            // Check attendance status (clocked in?)
            $isClockedIn = false;
            $lastAttendance = null;
            
            if ($doctor->attendances && $doctor->attendances->count() > 0) {
                $lastClockIn = null;
                $lastClockOut = null;
                
                foreach ($doctor->attendances as $attendance) {
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
                    $lastAttendance = $lastClockIn;
                }
            }

            // Determine actual availability
            $isAvailable = true;
            $unavailabilityReasons = [];

            if ($doctor->user && $doctor->user->status !== 'enabled') {
                $isAvailable = false;
                $unavailabilityReasons[] = 'User account disabled';
            }

            if (!$isClockedIn) {
                $isAvailable = false;
                $unavailabilityReasons[] = 'Not clocked in';
            }

            if ($doctor->doctorHolidays && $doctor->doctorHolidays->count() > 0) {
                $isAvailable = false;
                $unavailabilityReasons[] = 'On holiday today';
            }

            // Final status determination
            // If not available (not clocked in or on holiday), show as 'off_duty'
            // Otherwise use the status from user_statuses table
            $currentStatus = $isAvailable ? $statusFromTable : 'off_duty';

            // Get today's schedule (using company timezone)
            // day_of_week is stored as integer: 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, etc.
            $currentDayOfWeek = now($timezone)->dayOfWeekIso; // Returns 1-7
            $todaySchedule = null;
            
            $doctorSchedule = \App\Models\DoctorSchedule::where('doctor_id', $doctor->id)
                ->with(['schedule' => function($query) use ($currentDayOfWeek) {
                    $query->where('day_of_week', $currentDayOfWeek)
                          ->where('status', 1); // Only active schedules
                }])
                ->first();
            
            if ($doctorSchedule && $doctorSchedule->schedule && $doctorSchedule->schedule->count() > 0) {
                $scheduleDay = $doctorSchedule->schedule->first();
                $todaySchedule = [
                    'start_time' => $scheduleDay->available_from,
                    'end_time' => $scheduleDay->available_to,
                ];
            }

            // Get today's confirmed appointments count
            $todayAppointmentsCount = \App\Models\Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $today)
                ->where('status', 'confirmed')
                ->count();

            // Check for current appointment in progress (only today's appointments)
            $currentAppointment = \App\Models\Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $today)
                ->whereNotNull('in_progress_datetime')
                ->whereNull('completed_datetime')
                ->with(['patient.user', 'room', 'treatmentType'])
                ->first();

            $currentAppointmentData = null;
            if ($currentAppointment) {
                // Get patient name from user relationship (patients table only has user_id)
                $patientName = 'Unknown Patient';
                if ($currentAppointment->patient && $currentAppointment->patient->user) {
                    $firstName = trim($currentAppointment->patient->user->name ?? '');
                    $lastName = trim($currentAppointment->patient->user->last_name ?? '');
                    if ($firstName || $lastName) {
                        $patientName = trim($firstName . ' ' . $lastName);
                    }
                }

                // Use raw database values to avoid timezone conversion issues
                $rawAppointmentDate = $currentAppointment->getRawOriginal('appointment_date');
                $rawInProgressDate = $currentAppointment->getRawOriginal('in_progress_datetime');
                
                // Create Carbon instances without timezone assumption
                $appointmentCarbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $rawAppointmentDate);
                $inProgressCarbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $rawInProgressDate);
                
                // Adjust in_progress_datetime for LA timezone (UTC-8)
                // The database stores UTC time, but LA is 8 hours behind UTC
                $inProgressLA = $inProgressCarbon->copy()->subHours(8);
                
                // Calculate end time
                $endTime = null;
                if ($currentAppointment->duration) {
                    $endTime = $appointmentCarbon->copy()->addMinutes($currentAppointment->duration)->format('g:i A');
                }

                $currentAppointmentData = [
                    'xid' => $currentAppointment->xid,
                    'patient_name' => $patientName,
                    'room_name' => $currentAppointment->room ? $currentAppointment->room->name : null,
                    'treatment_type' => $currentAppointment->treatmentType ? $currentAppointment->treatmentType->name : null,
                    'start_time' => $appointmentCarbon->format('g:i A'),
                    'end_time' => $endTime,
                    'duration' => $currentAppointment->duration,
                    'in_progress_since' => $inProgressLA->format('g:i A'),
                    'in_progress_datetime' => $inProgressLA->toIso8601String(),
                ];
            }

            // Format the response with all needed data
            $response = [
                'xid' => $doctor->xid,
                'qualification' => $doctor->qualification,
                'designation' => $doctor->designation,
                'specialist' => $doctor->specialist,
                'description' => $doctor->description,
                'practice_id' => $doctor->practice_id,
                'appointment_charge' => $doctor->appointment_charge,
                'professional_id' => $doctor->professional_id,
                'current_status' => $currentStatus,
                'is_available' => $isAvailable,
                'is_clocked_in' => $isClockedIn,
                'unavailability_reasons' => $unavailabilityReasons,
                'last_attendance' => $lastAttendance ? [
                    'clock_time' => $lastAttendance->clock_time,
                    'status' => $lastAttendance->status,
                    'notes' => $lastAttendance->notes,
                ] : null,
                'today_schedule' => $todaySchedule,
                'today_appointments_count' => $todayAppointmentsCount,
                'user' => $doctor->user ? [
                    'xid' => $doctor->user->xid,
                    'name' => $doctor->user->name,
                    'last_name' => $doctor->user->last_name,
                    'email' => $doctor->user->email,
                    'phone' => $doctor->user->phone,
                    'profile_image' => $doctor->user->profile_image,
                    'profile_image_url' => $doctor->user->profile_image ? Common::getFileUrl(null, $doctor->user->profile_image) : asset('images/user.png'),
                    'gender' => $doctor->user->gender,
                    'date_of_birth' => $doctor->user->date_of_birth,
                    'status' => $doctor->user->status,
                ] : null,
                'doctor_department' => $doctor->doctorDepartment ? [
                    'xid' => $doctor->doctorDepartment->xid,
                    'name' => $doctor->doctorDepartment->name,
                    'code' => $doctor->doctorDepartment->code,
                ] : null,
                'specialties' => $doctor->specialties->map(function($specialty) {
                    return [
                        'xid' => $specialty->xid,
                        'name' => $specialty->name,
                    ];
                })->toArray(),
                'current_appointment' => $currentAppointmentData,
            ];

            return ApiResponse::make('Doctor information retrieved successfully', $response);

        } catch (\Exception $e) {
            return ApiResponse::make('Error retrieving doctor information: ' . $e->getMessage(), [], 500);
        }
    }

    public function storeDoctor(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $company = company();

            $doctorRole = Role::withoutGlobalScope(CompanyScope::class)->where('name', 'doctor')->where('company_id', $company->id)->first();

            // Create user first
            $user = new User();
            
            // Handle both nested user object or flat structure
            if ($request->has('user') && is_array($request->user)) {
                // Use user object if provided
                $userData = $request->user;
                $user->name = $request->name ?? $userData['name'] ?? '';
                $user->last_name = $request->last_name ?? $userData['last_name'] ?? '';
                $user->email = $request->email ?? $userData['email'] ?? '';
                $user->phone = $request->phone ?? $userData['phone'] ?? '';
                $user->gender = $request->gender ?? $userData['gender'] ?? null;
                $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : 
                                      (isset($userData['date_of_birth']) && $userData['date_of_birth'] ? date('Y-m-d', strtotime($userData['date_of_birth'])) : null);
                $user->status = $request->status ?? $userData['status'] ?? 'enabled';
                $user->user_type = 'doctors';
                
                // Handle password
                if (isset($userData['password']) && $userData['password'] != '') {
                    $user->password = $userData['password'];
                } elseif ($request->has('password') && $request->password != '') {
                    $user->password = $request->password;
                }
                
                $user->address = $request->address ?? $userData['address'] ?? '';
            } else {
                // Handle flat structure (all fields at top level)
                $user->name = $request->name ?? $request->first_name ?? '';
                $user->last_name = $request->last_name ?? '';
                $user->email = $request->email ?? '';
                $user->phone = $request->phone ?? '';
                $user->gender = $request->gender ?? null;
                $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : null;
                $user->status = $request->status ?? 'enabled';
                $user->user_type = 'doctors';
                
                if ($request->has('password') && $request->password != '') {
                    $user->password = $request->password;
                }
                
                $user->address = $request->address ?? '';
            }

            if ($request->hasFile('profile_image')) {
                $user->profile_image = Files::upload($request->file('profile_image'), 'doctors/profile-images');
            }
            
            $user->company_id = $company->id;
            $user->role_id = $doctorRole->id;
            $user->role_type = 'doctor';
            $user->save();
            $user->assignRole($doctorRole->name, '');

            // Create doctor
            $doctor = new Doctor();
            $doctor->company_id = $company->id;
            $doctor->user_id = $user->id;
            $doctor->doctor_department_id = $this->getIdFromHash($request->doctor_department_id);
            $doctor->qualification = $request->qualification;
            
            // Set specialist field as a string, not an array
            // $doctor->specialist = $request->has('specialist') ? $request->specialist : null;

            $doctor->designation = $request->designation;
            $doctor->description = $request->description;
            $doctor->practice_id = $request->practice_id;
            $doctor->color = $request->color ?? '#3B82F6';
            $doctor->appointment_charge = $request->appointment_charge;
            
            // Handle professional_id file upload
            if ($request->hasFile('professional_id')) {
                $doctor->professional_id = Files::upload($request->file('professional_id'), 'doctors/professional-ids');
            }
            
            $doctor->save();
            
            // Handle specialties through the pivot table
            if ($request->has('specialist')) {
                $specialistArray = is_array($request->specialist) ? $request->specialist : explode(',', $request->specialist);
                $validSpecialtyIds = [];
                
                // Collect all valid specialty IDs
                foreach ($specialistArray as $specialtyHashId) {
                    try {
                        // Convert hash to ID using the proper method
                        $specialtyId = $this->getIdFromHash($specialtyHashId);
                        // dd($specialtyId);
                        
                        // Check if the specialty exists in the doctor_specialties table
                        $specialtyExists = DB::table('doctor_specialties')->where('id', $specialtyId)->exists();
                        
                        if ($specialtyExists) {
                            $validSpecialtyIds[] = $specialtyId;
                        } else {
                            // Log that we're skipping this specialty because it doesn't exist
                            Log::warning("Specialty with ID {$specialtyId} (hash: {$specialtyHashId}) not found in doctor_specialties table");
                        }
                    } catch (\Exception $e) {
                        // Log the error but continue processing other specialties
                        Log::error("Error processing specialty {$specialtyHashId}: " . $e->getMessage());
                    }
                }
                
                // Use the relationship method to attach specialties with additional pivot data
                if (!empty($validSpecialtyIds)) {

                               
                    // Create pivot data with company_id for each specialty
                    $pivotData = array_fill_keys($validSpecialtyIds, ['company_id' => $company->id]);
                    
                    // Attach specialties to the doctor with the company_id in pivot table
                    $doctor->specialties()->attach($pivotData);
                }
            }

            // Handle addresses using the trait - save to user
            $this->addAddressToEntity($user, $request->all());

            // Sync User Clinics
            $this->syncClinics($user);

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $doctor->xid
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            throw new ApiException('Failed. ' . $e->getMessage());
        }
    }

    public function updateDoctor(UpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            
            // Get doctor
            $xid = $id;
            $id = $this->getIdFromHash($xid);
            $doctor = Doctor::find($id);
            
            if (!$doctor) {
                throw new ApiException('Doctor not found');
            }
            
            // Find and update user
            $user = User::find($doctor->user_id);
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->gender = $request->gender;
            $user->date_of_birth = $request->date_of_birth ? date('Y-m-d', strtotime($request->date_of_birth)) : null;
            $user->status = $request->status;
            
            if ($request->has('password') && $request->password != '') {
                $user->password = $request->password;
            }
            
            // $user->address = $request->address;

            if ($request->hasFile('profile_image')) {
                $user->profile_image = Files::upload($request->file('profile_image'), 'doctors/profile-images');
            }
            
            $user->save();
            
            // Handle addresses if provided



            // Update doctor
            $doctor->doctor_department_id = $this->getIdFromHash($request->doctor_department_id);
            $doctor->qualification = $request->qualification;
                
            $doctor->designation = $request->designation;
            $doctor->description = $request->description;
            $doctor->practice_id = $request->practice_id;
            $doctor->color = $request->color ?? '#3B82F6';
            $doctor->appointment_charge = $request->appointment_charge;
            
            // Handle professional_id file upload
            if ($request->hasFile('professional_id')) {
                $doctor->professional_id = Files::upload($request->file('professional_id'), 'doctors/professional-ids');
            }
            
            $doctor->save();
            // Handle specialties through the pivot table
            if ($request->has('specialist')) {
                $company = company();
                $specialistRaw = $request->specialist;

                // Convert comma-separated string to array if needed
                $specialistArray = is_array($specialistRaw) ? $specialistRaw : explode(',', $specialistRaw);

                $validSpecialtyIds = [];

                // Collect all valid specialty IDs
                foreach ($specialistArray as $specialtyHashId) {
                    try {
                        // Convert hash to ID using the proper method
                        $specialtyId = $this->getIdFromHash($specialtyHashId);

                        // Check if the specialty exists in the doctor_specialties table
                        $specialtyExists = DB::table('doctor_specialties')->where('id', $specialtyId)->exists();

                        if ($specialtyExists) {
                            $validSpecialtyIds[] = $specialtyId;
                        } else {
                            // Log that we're skipping this specialty because it doesn't exist
                            Log::warning("Specialty with ID {$specialtyId} (hash: {$specialtyHashId}) not found in doctor_specialties table");
                        }
                    } catch (\Exception $e) {
                        // Log the error but continue processing other specialties
                        Log::error("Error processing specialty {$specialtyHashId}: " . $e->getMessage());
                    }
                }

                // Create pivot data with company_id for each specialty
                $pivotData = array_fill_keys($validSpecialtyIds, ['company_id' => $company->id]);

                // Sync specialties to the doctor (this will remove existing ones and add new ones)
                $doctor->specialties()->sync($pivotData);
            }

            // Handle addresses using the trait
            $this->addAddressToEntity($user, $request->all());

            // Sync User Clinics
            $this->syncClinics($user);

            DB::commit();

            return ApiResponse::make('Success', [
                'xid' => $doctor->xid
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed. ' . $e->getMessage());
        }
    }

    public function allDoctors()
    {
        $company = Company();
        $departments = cache()->remember("company_{$company->id}_departments", 3600, function () {
            return $this->model::get();
        });

        return response()->json([
            'data' => $departments,
        ]);
    }

    public function getStats(Request $request)
    {   
        // Total doctors count
        $totalDoctors = Doctor::count();
        
        // Top specialist (most common specialization)
        $topSpecialist = Doctor::select('specialist', DB::raw('count(*) as total'))
            ->whereNotNull('specialist')
            ->where('specialist', '!=', '')
            ->groupBy('specialist')
            ->orderBy('total', 'desc')
            ->first();
        
        // Average appointment charge
        $avgAppointmentCharge = Doctor::avg('appointment_charge') ?? 0;
        
        // Available today - doctors who have schedule for today and are not on holiday
        $today = now()->format('Y-m-d');
        $dayOfWeek = now()->dayOfWeek;
        
        // First get doctors who have schedule for today
        $availableDoctorIds = DoctorScheduleDay::join('doctor_schedules', 'doctor_schedule_days.schedule_id', '=', 'doctor_schedules.id')
            ->where('doctor_schedule_days.day_of_week', $dayOfWeek)
            ->where('doctor_schedule_days.status', 1) // Only enabled schedule days
            ->pluck('doctor_schedules.doctor_id')
            ->unique()
            ->toArray();
        
        // Exclude doctors who are on holiday today (only approved/active holidays)
        $doctorsOnHoliday = DoctorHoliday::where(function($query) use ($today) {
                $query->where('start_date', '<=', $today)
                      ->where('end_date', '>=', $today);
            })
            ->where('status', 'approved')
            ->pluck('doctor_id')
            ->toArray();
            
        $availableDoctorIds = array_diff($availableDoctorIds, $doctorsOnHoliday);
        
        // Count the doctors who are actually available today (have schedules and aren't on holiday)
        $availableToday = count($availableDoctorIds);
        
        return response()->json([
            'totalDoctors' => $totalDoctors,
            'topSpecialist' => $topSpecialist ? $topSpecialist->specialist : '-',
            'avgAppointmentCharge' => round($avgAppointmentCharge, 2),
            'availableToday' => $availableToday
        ]);
    }

    public function updateStatus(Request $request)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:available,busy,break'
        ]);

        $user = user();
        $company = company();
        
        // Get the doctor record from the authenticated user
        $doctor = Doctor::where('user_id', $user->id)->first();
        
        if (!$doctor) {
            return ApiResponse::make('Doctor not found', [], 404);
        }
 
        try {
            // Update or create status in user_statuses table
            $userStatus = UserStatus::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => $request->status,
                    'company_id' => $company->id
                ]
            );

            return ApiResponse::make('Doctor status updated successfully', [
                'doctor_id' => $doctor->xid,
                'status' => $userStatus->status,
                'updated_at' => $userStatus->updated_at->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return ApiResponse::make('Error updating doctor status: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update a doctor's status (for admin/receptionist use)
     * 
     * @param Request $request
     * @param string $xid Doctor's hashed ID
     * @return mixed
     */
    public function updateDoctorStatus(Request $request, $xid)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:available,busy,break',
            'notes' => 'nullable|string|max:255'
        ]);

        $company = company();
        
        try {
            // Get the doctor by xid
            $doctorId = $this->getIdFromHash($xid);
            $doctor = Doctor::find($doctorId);
            
            if (!$doctor) {
                return ApiResponse::make('Doctor not found', [], 404);
            }

            // Update or create status in user_statuses table
            $userStatus = UserStatus::updateOrCreate(
                ['user_id' => $doctor->user_id],
                [
                    'status' => $request->status,
                    'company_id' => $company->id,
                    'notes' => $request->notes
                ]
            );

            return ApiResponse::make('Doctor status updated successfully', [
                'doctor_id' => $doctor->xid,
                'doctor_name' => $doctor->user ? $doctor->user->name : null,
                'status' => $userStatus->status,
                'notes' => $userStatus->notes,
                'updated_at' => $userStatus->updated_at->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return ApiResponse::make('Error updating doctor status: ' . $e->getMessage(), [], 500);
        }
    }

    public function availibleDoctors(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'selectedTimeRange' => 'required|array|size:2',
                'selectedTimeRange.*' => 'required|string',
                'selectedDate' => 'required|date',
                'selectedSpecialties' => 'nullable|array',
                'selectedTreatments' => 'nullable|array'
            ]);

            $company = company();
            $selectedDate = $request->selectedDate;
            $timeRange = $request->selectedTimeRange;
            $startTime = $timeRange[0];
            $endTime = $timeRange[1];
            $selectedSpecialties = $request->selectedSpecialties ?? [];
            $selectedTreatments = $request->selectedTreatments ?? [];

            // Convert date to day of week (0 = Sunday, 1 = Monday, etc.)
            $dayOfWeek = date('w', strtotime($selectedDate));
            $dateOnly = date('Y-m-d', strtotime($selectedDate));

            // Base query for doctors
            $doctorsQuery = Doctor::with(['user', 'specialties', 'doctorDepartment'])
                ->whereHas('user', function($query) {
                    $query->where('status', 'enabled');
                        //   ->where('user_type', 'doctors');
                });

            // dd($doctorsQuery->toSql(), $doctorsQuery->getBindings());

            // Filter by specialties if provided
            if (!empty($selectedSpecialties)) {
                $specialtyIds = [];
                foreach ($selectedSpecialties as $specialtyHashId) {
                    try {
                        $specialtyId = $this->getIdFromHash($specialtyHashId);
                        $specialtyIds[] = $specialtyId;
                    } catch (\Exception $e) {
                        Log::warning("Invalid specialty hash: {$specialtyHashId}");
                    }
                }

                if (!empty($specialtyIds)) {
                    $doctorsQuery->whereHas('specialties', function($query) use ($specialtyIds) {
                        $query->whereIn('doctor_specialties.id', $specialtyIds);
                    });
                }
            }

            $doctors = $doctorsQuery->get();
            $availableDoctors = [];
            $debugInfo = [
                'total_doctors_found' => $doctors->count(),
                'doctors_with_schedules' => 0,
                'doctors_with_valid_times' => 0,
                'doctors_on_holiday' => 0,
                'doctors_available_in_time_range' => 0
            ];

            foreach ($doctors as $doctor) {
                // Check if doctor is on holiday on the selected date
                $isOnHoliday = DoctorHoliday::where('doctor_id', $doctor->id)
                    ->where('date', $dateOnly)
                    ->exists();

                if ($isOnHoliday) {
                    $debugInfo['doctors_on_holiday']++;
                    continue; // Skip this doctor
                }

                // Check if doctor has schedule for the selected day
                $scheduleDay = DoctorScheduleDay::join('doctor_schedules', 'doctor_schedule_days.schedule_id', '=', 'doctor_schedules.id')
                    ->where('doctor_schedules.doctor_id', $doctor->id)
                    ->where('doctor_schedule_days.day_of_week', $dayOfWeek)
                    ->where('doctor_schedule_days.status', 1)
                    ->select('doctor_schedule_days.*')
                    ->first();

                if (!$scheduleDay) {
                    // No schedule for this day - let's include the doctor anyway but mark as "no specific schedule"
                    $availableDoctors[] = [
                        'id' => $doctor->id,
                        'xid' => $doctor->xid,
                        'name' => $doctor->user->name,
                        'last_name' => $doctor->user->last_name,
                        'full_name' => $doctor->user->name . ' ' . $doctor->user->last_name,
                        'email' => $doctor->user->email,
                        'phone' => $doctor->user->phone,
                        'qualification' => $doctor->qualification,
                        'designation' => $doctor->designation,
                        'description' => $doctor->description,
                        'appointment_charge' => $doctor->appointment_charge,
                        'profile_image' => $doctor->user->profile_image ? Files::getFileS3Url($doctor->user->profile_image) : null,
                        'department' => $doctor->doctorDepartment ? [
                            'xid' => $doctor->doctorDepartment->xid,
                            'name' => $doctor->doctorDepartment->name
                        ] : null,
                        'specialties' => $doctor->specialties->map(function($specialty) {
                            return [
                                'xid' => $specialty->xid,
                                'name' => $specialty->name
                            ];
                        }),
                        'schedule' => [
                            'day_of_week' => $dayOfWeek,
                            'start_time' => null,
                            'end_time' => null,
                            'available_time_range' => [$startTime, $endTime],
                            'status' => 'no_schedule_configured'
                        ]
                    ];
                    continue;
                }

                $debugInfo['doctors_with_schedules']++;

                // Check if the requested time range falls within doctor's schedule
                $doctorStartTime = $scheduleDay->start_time;
                $doctorEndTime = $scheduleDay->end_time;

                // Skip if doctor doesn't have valid start/end times
                if (empty($doctorStartTime) || empty($doctorEndTime)) {
                    // Include doctor but mark as having invalid schedule times
                    $availableDoctors[] = [
                        'id' => $doctor->id,
                        'xid' => $doctor->xid,
                        'name' => $doctor->user->name,
                        'last_name' => $doctor->user->last_name,
                        'full_name' => $doctor->user->name . ' ' . $doctor->user->last_name,
                        'email' => $doctor->user->email,
                        'phone' => $doctor->user->phone,
                        'qualification' => $doctor->qualification,
                        'designation' => $doctor->designation,
                        'description' => $doctor->description,
                        'appointment_charge' => $doctor->appointment_charge,
                        'profile_image' => $doctor->user->profile_image ? Files::getFileS3Url($doctor->user->profile_image) : null,
                        'department' => $doctor->doctorDepartment ? [
                            'xid' => $doctor->doctorDepartment->xid,
                            'name' => $doctor->doctorDepartment->name
                        ] : null,
                        'specialties' => $doctor->specialties->map(function($specialty) {
                            return [
                                'xid' => $specialty->xid,
                                'name' => $specialty->name
                            ];
                        }),
                        'schedule' => [
                            'day_of_week' => $dayOfWeek,
                            'start_time' => $doctorStartTime,
                            'end_time' => $doctorEndTime,
                            'available_time_range' => [$startTime, $endTime],
                            'status' => 'invalid_schedule_times'
                        ]
                    ];
                    continue; // Skip time validation for this doctor
                }

                $debugInfo['doctors_with_valid_times']++;

                // Convert times to timestamps for comparison
                $requestedStart = strtotime($startTime);
                $requestedEnd = strtotime($endTime);
                $doctorStart = strtotime($doctorStartTime);
                $doctorEnd = strtotime($doctorEndTime);

                // Verify that all time conversions were successful
                if ($requestedStart === false || $requestedEnd === false || 
                    $doctorStart === false || $doctorEnd === false) {
                    continue; // Skip if any time conversion failed
                }

                // Check if requested time range is within doctor's schedule
                if ($requestedStart >= $doctorStart && $requestedEnd <= $doctorEnd) {
                    $debugInfo['doctors_available_in_time_range']++;
                    // Doctor is available during the requested time
                    $availableDoctors[] = [
                        'id' => $doctor->id,
                        'name' => $doctor->user->name,
                        'last_name' => $doctor->user->last_name,
                        'full_name' => $doctor->user->name . ' ' . $doctor->user->last_name,
                        'email' => $doctor->user->email,
                        'phone' => $doctor->user->phone,
                        'qualification' => $doctor->qualification,
                        'designation' => $doctor->designation,
                        'description' => $doctor->description,
                        'appointment_charge' => $doctor->appointment_charge,
                        'profile_image' => $doctor->user->profile_image ? Files::getFileS3Url($doctor->user->profile_image) : null,
                        'department' => $doctor->doctorDepartment ? [
                            'id' => $doctor->doctorDepartment->id,
                            'name' => $doctor->doctorDepartment->name
                        ] : null,
                        'specialties' => $doctor->specialties->map(function($specialty) {
                            return [
                                'id' => $specialty->id,
                                'name' => $specialty->name
                            ];
                        }),
                        'schedule' => [
                            'day_of_week' => $dayOfWeek,
                            'start_time' => $doctorStartTime,
                            'end_time' => $doctorEndTime,
                            'available_time_range' => [$startTime, $endTime]
                        ]
                    ];
                }
            }

            return ApiResponse::make('Available doctors retrieved successfully', [
                'doctors' => $availableDoctors,
                'search_criteria' => [
                    'date' => $dateOnly,
                    'day_of_week' => $dayOfWeek,
                    'time_range' => [$startTime, $endTime],
                    'specialties_count' => count($selectedSpecialties),
                    'treatments_count' => count($selectedTreatments)
                ],
                'total_available' => count($availableDoctors),
                'debug_info' => $debugInfo
            ]);

        } catch (\Exception $e) {
            return ApiResponse::make('Error retrieving available doctors: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get available doctors with their current status and last attendance
     * Returns all doctors (available and unavailable) with availability status
     * Filters by clinic assignment when applicable
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        try {
            $clinicContext = app('App\Services\ClinicContext');
            $currentClinicId = $clinicContext->getClinic();
            $isGlobalMode = $clinicContext->isGlobal();
            $today = now()->format('Y-m-d');
            $dayOfWeek = now()->dayOfWeek;

            $query = Doctor::with([
                'user:id,name,profile_image,status',
                'user.clinics:clinic_locations.id,name',
                'doctorDepartment:id,name',
                'doctorSchedules.schedule' => function($query) use ($dayOfWeek) {
                    $query->where('day_of_week', $dayOfWeek)
                          ->where('status', 1);
                },
                'doctorHolidays' => function($query) use ($today) {
                    $query->where('start_date', '<=', $today)
                          ->where('end_date', '>=', $today);
                },
                'attendances' => function($query) use ($currentClinicId, $isGlobalMode) {
                    // Get the most recent attendance records to determine clock in/out status
                    // Remove clinic scope and date filter to check actual current status
                    $query->withoutGlobalScope('clinic')
                          ->latest('clock_time')
                          ->limit(10); // Get last 10 records to find matching clock_in/clock_out pairs
                    
                    // If we're in a specific clinic context, filter by that clinic
                    if (!$isGlobalMode && $currentClinicId) {
                        $query->where('clinic_id', $currentClinicId);
                    }
                }
            ]);

            // Filter by clinic assignment if not in global mode
            if (!$isGlobalMode && $currentClinicId) {
                $query->whereHas('user.clinics', function($q) use ($currentClinicId) {
                    $q->where('clinic_locations.id', $currentClinicId);
                });
            }

            // Search filter - search across doctor name, specialist, and department name
            if ($request->has('search') && $request->search != '') {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('user', function($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhere('specialist', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('doctorDepartment', function($deptQuery) use ($searchTerm) {
                        $deptQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
                });
            }

            // Department filter
            if ($request->has('department_id') && $request->department_id != '') {
                $departmentId = $this->getIdFromHash($request->department_id);
                $query->where('doctor_department_id', $departmentId);
            }

            // Get all doctors
            $doctors = $query->get();

            // Get current statuses for all doctors
            $userIds = $doctors->pluck('user_id')->toArray();
            $statuses = UserStatus::whereIn('user_id', $userIds)
                ->select('user_id', 'status', 'notes', 'updated_at')
                ->get()
                ->groupBy('user_id')
                ->map(function($statusGroup) {
                    return $statusGroup->sortByDesc('updated_at')->first();
                });

            // Status filter - filter by status after getting all doctors
            $statusFilter = $request->has('status') && $request->status != '' ? $request->status : null;

            // Transform the data
            $data = $doctors->map(function($doctor) use ($statuses, $statusFilter, $today, $dayOfWeek) {
                $userStatus = $statuses->get($doctor->user_id);
                $currentStatus = $userStatus ? $userStatus->status : 'available';

                // Check attendance status (clocked in?)
                // A doctor is clocked in if their most recent attendance record is a clock_in
                $isClockedIn = false;
                $lastClockIn = null;
                $lastClockOut = null;
                
                if ($doctor->attendances && $doctor->attendances->count() > 0) {
                    // Get all attendance records (sorted by most recent)
                    $allAttendances = $doctor->attendances;
                    
                    // Find the most recent clock_in and clock_out
                    foreach ($allAttendances as $attendance) {
                        if ($attendance->status === 'clock_in' && !$lastClockIn) {
                            $lastClockIn = $attendance;
                        } elseif ($attendance->status === 'clock_out' && !$lastClockOut) {
                            $lastClockOut = $attendance;
                        }
                        
                        // Break if we found both
                        if ($lastClockIn && $lastClockOut) {
                            break;
                        }
                    }
                    
                    // Doctor is clocked in if:
                    // 1. There's a clock_in record and no clock_out record, OR
                    // 2. The most recent clock_in is more recent than the most recent clock_out
                    if ($lastClockIn && (!$lastClockOut || $lastClockIn->clock_time > $lastClockOut->clock_time)) {
                        $isClockedIn = true;
                    }
                }

                // Check availability
                $isAvailable = true;
                $unavailabilityReasons = [];

                // Check if user is enabled
                if ($doctor->user && $doctor->user->status !== 'enabled') {
                    $isAvailable = false;
                    $unavailabilityReasons[] = 'User account disabled';
                }

                // Check if on holiday
                if ($doctor->doctorHolidays && $doctor->doctorHolidays->count() > 0) {
                    $isAvailable = false;
                    $unavailabilityReasons[] = 'On holiday today';
                }

                // Check if clocked in (critical for availability)
                if (!$isClockedIn) {
                    $isAvailable = false;
                    $unavailabilityReasons[] = 'Not clocked in';
                }

                // Check current availability status (only matters if clocked in)
                if ($isClockedIn) {
                    if ($currentStatus === 'break') {
                        $isAvailable = false;
                        $unavailabilityReasons[] = 'Currently on break';
                    } elseif ($currentStatus === 'busy') {
                        $isAvailable = false;
                        $unavailabilityReasons[] = 'Currently busy';
                    }
                    // If status is 'available' and clocked in, they remain marked as available
                }

                // Get last attendance (use the most recent one)
                $lastAttendance = $doctor->attendances->first();
                $lastAttendanceData = null;
                if ($lastAttendance) {
                    $lastAttendanceData = [
                        'clock_time' => $lastAttendance->clock_time ? $lastAttendance->clock_time->toIso8601String() : null,
                        'status' => $lastAttendance->status,
                        'notes' => $lastAttendance->notes
                    ];
                }

                // Get clinic assignments
                $clinics = [];
                if ($doctor->user && $doctor->user->clinics) {
                    $clinics = $doctor->user->clinics->map(function($clinic) {
                        return [
                            'id' => $clinic->id,
                            'xid' => Common::getHashFromId($clinic->id),
                            'name' => $clinic->name
                        ];
                    })->toArray();
                }

                return [
                    'id' => $doctor->id,
                    'xid' => Common::getHashFromId($doctor->id),
                    'user_id' => $doctor->user_id,
                    'name' => $doctor->user ? $doctor->user->name : null,
                    'profile_image_url' => $doctor->user ? $doctor->user->profile_image_url : null,
                    'doctor_department_id' => $doctor->doctor_department_id,
                    'x_doctor_department_id' => $doctor->x_doctor_department_id,
                    'department' => $doctor->doctorDepartment ? $doctor->doctorDepartment->name : null,
                    'qualification' => $doctor->qualification,
                    'specialist' => $doctor->specialist,
                    'designation' => $doctor->designation,
                    'description' => $doctor->description,
                    'practice_id' => $doctor->practice_id,
                    'appointment_charge' => $doctor->appointment_charge,
                    'missed_appointment_charge' => $doctor->missed_appointment_charge,
                    'cancellation_notice_hours' => $doctor->cancellation_notice_hours,
                    'status' => $currentStatus,
                    'is_clocked_in' => $isClockedIn,
                    'is_available' => $isAvailable,
                    'unavailability_reasons' => $unavailabilityReasons,
                    'clinics' => $clinics,
                    'last_attendance' => $lastAttendanceData,
                    'status_priority' => $this->getStatusPriority($currentStatus),
                    'availability_priority' => $isAvailable ? 1 : 2
                ];
            });

            // Apply status filter if provided
            if ($statusFilter) {
                $data = $data->filter(function($doctor) use ($statusFilter) {
                    return $doctor['status'] === $statusFilter;
                });
            }

            // Sort by availability (available first), then status priority, then by name
            $data = $data->sortBy([
                ['availability_priority', 'asc'],
                ['status_priority', 'asc'],
                ['name', 'asc']
            ])->values();

            // Remove the priority fields from final output
            $data = $data->map(function($doctor) {
                unset($doctor['status_priority']);
                unset($doctor['availability_priority']);
                return $doctor;
            });

            return ApiResponse::make('Success', [
                'data' => $data->values(),
                'clinic_context' => [
                    'clinic_id' => $currentClinicId,
                    'is_global' => $isGlobalMode
                ]
            ]);

        } catch (\Exception $e) {
            return ApiResponse::make('Error retrieving available doctors: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get status priority for sorting
     * available = 1 (highest priority)
     * busy = 2
     * break = 3
     * other = 4
     */
    private function getStatusPriority($status)
    {
        switch ($status) {
            case 'available':
                return 1;
            case 'busy':
                return 2;
            case 'break':
                return 3;
            default:
                return 4;
        }
    }
}
