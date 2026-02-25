<?php

namespace App\Http\Controllers\Api;

use App\Models\DoctorScheduleDay;
use App\Models\Appointment;
use App\Models\DoctorHoliday;
use App\Models\ClinicSchedule;
use App\Models\DoctorBreak;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use Carbon\Carbon;

use App\Http\Requests\Api\DoctorScheduleDay\IndexRequest;
use App\Http\Requests\Api\DoctorScheduleDay\StoreRequest;
use App\Http\Requests\Api\DoctorScheduleDay\UpdateRequest;
use App\Http\Requests\Api\DoctorScheduleDay\DeleteRequest;

// Establecer zona horaria por defecto para Carbon
Carbon::setLocale('es');
date_default_timezone_set('America/Los_Angeles');

class DoctorScheduleDayController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = DoctorScheduleDay::class;
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

            $query = $query->whereRaw('doctor_schedule_days.created_at >= ?', [$startDate])
                ->whereRaw('doctor_schedule_days.created_at <= ?', [$endDate]);
        }

        return $query;
    }

    /**
     * Crear una nueva cita para un doctor
     */
    public function createAppointment()
    {
        $request = request();
        
        // Validar los datos de entrada
        $request->validate([
            'doctor_id' => 'required|string', // Hashable ID
            'patient_id' => 'required|string', // Hashable ID
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|date_format:H:i',
            'duration' => 'nullable|integer|min:15', // Duración en minutos
            'reason_visit' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:pending,confirmed,cancelled,completed',
            'treatment_type_id' => 'nullable|string',
            'price' => 'nullable|numeric',
            'room_id' => 'nullable|string',
        ]);

        try {
            $doctorId = $this->getIdFromHash($request->doctor_id);
            $duration = (int) ($request->duration ?? 30); // Duración por defecto 30 minutos, convertir a entero
            
            // Combinar fecha y hora en un datetime usando Carbon para asegurar el formato correcto
            // Treat input time as company local time (no timezone conversion)
            $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);
            $appointmentEndTime = $appointmentDateTime->copy()->addMinutes($duration);
            // Convert Carbon's dayOfWeek (0-6, 0=Sunday) to database format (1-7, 1=Monday, 7=Sunday)
            $dayOfWeek = $appointmentDateTime->dayOfWeek == 0 ? 7 : $appointmentDateTime->dayOfWeek;

            // 0. Obtener Clinic ID del request o header
            $clinicId = $request->clinic_id ? $this->getIdFromHash($request->clinic_id) : (request()->header('X-Clinic-ID') ? $this->getIdFromHash(request()->header('X-Clinic-ID')) : null);
            
            // 1. Verificar holidays del doctor para la fecha específica
            $doctorHoliday = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('start_date', '<=', $request->appointment_date)
                ->where('end_date', '>=', $request->appointment_date)
                ->first();

            if ($doctorHoliday && !$request->boolean('force')) {
                return response()->json([
                    'success' => false,
                    'is_warning' => true,
                    'message' => 'El doctor no está disponible en esta fecha debido a un día feriado programado.',
                    'details' => [
                        'type' => 'doctor_holiday',
                        'date' => $request->appointment_date,
                        'holiday_start' => $doctorHoliday->start_date,
                        'holiday_end' => $doctorHoliday->end_date
                    ]
                ], 400);
            }

            // 2. Verificar horario de la clínica para ese día de la semana y CLINICA específica
            $clinicScheduleQuery = ClinicSchedule::where('day_of_week', $dayOfWeek)
                ->where('company_id', company()->id);
            
            if ($clinicId) {
                $clinicScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $clinicSchedule = $clinicScheduleQuery->first();

            if (!$clinicSchedule && !$request->boolean('force')) {
                // Si no hay horario específico para la clínica, verificamos si hay uno general (sin clinic_id) o fallamos
                // En este caso, asumimos que si se pide para una clínica, debe haber horario para esa clínica
                return response()->json([
                    'success' => false,
                    'is_warning' => true,
                    'message' => 'La clínica no tiene horario de atención para este día de la semana.',
                    'details' => [
                        'type' => 'clinic_closed',
                        'day_of_week' => $dayOfWeek,
                        'clinic_id' => $clinicId
                    ]
                ], 400);
            }

            // 3. Validar que el doctor tenga horario disponible para ese día Y esa clínica
            // Primero buscamos el DoctorSchedule para esta clínica
            $doctorScheduleQuery = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
                ->where('company_id', company()->id);
                
            if ($clinicId) {
                $doctorScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $doctorSchedule = $doctorScheduleQuery->first();
            
            $scheduleDay = null;
            if ($doctorSchedule) {
                $scheduleDay = DoctorScheduleDay::where('schedule_id', $doctorSchedule->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('status', 1)
                    ->first();
            }

            if (!$scheduleDay && !$request->boolean('force')) {
                return response()->json([
                    'success' => false,
                    'is_warning' => true,
                    'message' => 'El doctor no tiene horario disponible para este día de la semana en esta clínica.',
                    'details' => [
                        'type' => 'doctor_schedule_unavailable',
                        'day_of_week' => $dayOfWeek,
                        'clinic_id' => $clinicId
                    ]
                ], 400);
            }

            // 4. Ajustar horario del doctor según el horario de la clínica
            $adjustedSchedule = $this->adjustScheduleWithClinic($scheduleDay, $clinicSchedule);
            if (!$adjustedSchedule && !$request->boolean('force')) {
                return response()->json([
                    'success' => false,
                    'is_warning' => true,
                    'message' => 'No hay horario disponible para este día considerando el horario de la clínica.',
                    'details' => [
                        'type' => 'schedule_conflict',
                        'doctor_schedule' => [
                            'from' => $scheduleDay ? $scheduleDay->available_from : null,
                            'to' => $scheduleDay ? $scheduleDay->available_to : null
                        ],
                        'clinic_schedule' => [
                            'from' => $clinicSchedule ? $clinicSchedule->start_time : null,
                            'to' => $clinicSchedule ? $clinicSchedule->end_time : null
                        ]
                    ]
                ], 400);
            }

            if ($adjustedSchedule) {
                $scheduleStart = Carbon::createFromFormat('H:i:s', $adjustedSchedule['start_time']);
                $scheduleEnd = Carbon::createFromFormat('H:i:s', $adjustedSchedule['end_time']);
                $appointmentTime = Carbon::createFromFormat('H:i', $request->appointment_time);
                $appointmentEndTimeOnly = $appointmentTime->copy()->addMinutes($duration);

                if (($appointmentTime->lt($scheduleStart) || $appointmentEndTimeOnly->gt($scheduleEnd)) && !$request->boolean('force')) {
                    return response()->json([
                        'success' => false,
                        'is_warning' => true,
                        'message' => 'La cita debe estar dentro del horario disponible ajustado.',
                        'details' => [
                            'type' => 'time_outside_schedule',
                            'available_schedule' => [
                                'from' => $adjustedSchedule['start_time'],
                                'to' => $adjustedSchedule['end_time']
                            ],
                            'requested_time' => [
                                'from' => $request->appointment_time,
                                'to' => $appointmentEndTimeOnly->format('H:i')
                            ]
                        ]
                    ], 400);
                }
            }

            // 6. Verificar breaks del doctor para la fecha específica
            if (!$request->boolean('force')) {
                $breaks = $this->getDoctorBreaks($doctorId, $request->appointment_date);
                $appointmentTime = Carbon::createFromFormat('H:i', $request->appointment_time);
                $appointmentEndTimeOnly = $appointmentTime->copy()->addMinutes($duration);
                $appointmentStartTime = $appointmentTime->format('H:i');
                $appointmentEndTimeFormatted = $appointmentEndTimeOnly->format('H:i');

                foreach ($breaks as $break) {
                    $breakStart = strtotime($break['start_time']);
                    $breakEnd = strtotime($break['end_time']);
                    $appointmentStart = strtotime($appointmentStartTime);
                    $appointmentEnd = strtotime($appointmentEndTimeFormatted);

                    // Verificar si hay solapamiento con algún break
                    if (
                        ($appointmentStart >= $breakStart && $appointmentStart < $breakEnd) || // La cita empieza durante un break
                        ($appointmentEnd > $breakStart && $appointmentEnd <= $breakEnd) ||   // La cita termina durante un break
                        ($appointmentStart <= $breakStart && $appointmentEnd >= $breakEnd)   // La cita engloba un break
                    ) {
                        return response()->json([
                            'success' => false,
                            'is_warning' => true,
                            'message' => 'La cita coincide con un break del doctor.',
                            'details' => [
                                'type' => 'doctor_break_conflict',
                                'break_time' => [
                                    'from' => $break['start_time'],
                                    'to' => $break['end_time']
                                ],
                                'requested_time' => [
                                    'from' => $appointmentStartTime,
                                    'to' => $appointmentEndTimeFormatted
                                ]
                            ]
                        ], 400);
                    }
                }
            }

            // 7. Validar que no haya conflictos con otras citas existentes
            $conflictingAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $request->appointment_date)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($appointmentDateTime, $appointmentEndTime) {
                    $query->where(function ($q) use ($appointmentDateTime, $appointmentEndTime) {
                        // La nueva cita empieza durante una cita existente
                        $q->where('appointment_date', '<=', $appointmentDateTime)
                          ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?', [$appointmentDateTime]);
                    })->orWhere(function ($q) use ($appointmentDateTime, $appointmentEndTime) {
                        // La nueva cita termina durante una cita existente
                        $q->where('appointment_date', '<', $appointmentEndTime)
                          ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) >= ?', [$appointmentEndTime]);
                    })->orWhere(function ($q) use ($appointmentDateTime, $appointmentEndTime) {
                        // La nueva cita engloba completamente una cita existente
                        $q->where('appointment_date', '>=', $appointmentDateTime)
                          ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) <= ?', [$appointmentEndTime]);
                    });
                })
                ->first();

            if ($conflictingAppointments && !$request->boolean('force')) {
                $conflictStart = Carbon::parse($conflictingAppointments->appointment_date);
                $conflictEnd = $conflictStart->copy()->addMinutes($conflictingAppointments->duration);
                
                // Ensure $appointmentStartTime and $appointmentEndTimeFormatted are defined 
                // in case the breaks logic was bypassed
                $appointmentTime = Carbon::createFromFormat('H:i', $request->appointment_time);
                $appointmentEndTimeOnly = $appointmentTime->copy()->addMinutes($duration);
                
                return response()->json([
                    'success' => false,
                    'is_warning' => true,
                    'message' => 'Ya existe una cita en el horario solicitado.',
                    'details' => [
                        'type' => 'appointment_conflict',
                        'existing_appointment' => [
                            'from' => $conflictStart->format('H:i'),
                            'to' => $conflictEnd->format('H:i'),
                            'duration' => $conflictingAppointments->duration
                        ],
                        'requested_time' => [
                            'from' => $appointmentTime->format('H:i'),
                            'to' => $appointmentEndTimeOnly->format('H:i')
                        ]
                    ]
                ], 400);
            }

            // 8. Crear la cita si todas las validaciones pasan
            $appointment = new Appointment();
            $appointment->doctor_id = $doctorId;
            $appointment->patient_id = $this->getIdFromHash($request->patient_id);
            $appointment->appointment_date = $appointmentDateTime;
            $appointment->duration = $duration;
            $appointment->reason_visit = $request->reason_visit;
            $appointment->status = $request->status ?? 'confirmed';
            $appointment->company_id = company()->id;
            if ($clinicId) {
                $appointment->clinic_id = $clinicId;
            }
            if ($request->treatment_type_id) {
                $appointment->treatment_type_id = $this->getIdFromHash($request->treatment_type_id);
            }
            if ($request->room_id) {
                $appointment->room_id = $this->getIdFromHash($request->room_id);
            }
            if ($request->has('price')) {
                $appointment->price = $request->price;
            }
            $appointment->save();

            // Refresh to get the formatted datetime
            $appointment->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Cita creada exitosamente',
                'data' => [
                    'xid' => $appointment->xid,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d H:i:s'),
                    'duration' => $appointment->duration,
                    'status' => $appointment->status,
                    'validations_passed' => [
                        'doctor_holiday_check' => 'passed',
                        'clinic_schedule_check' => 'passed',
                        'doctor_schedule_check' => 'passed',
                        'time_range_check' => 'passed',
                        'doctor_breaks_check' => 'passed',
                        'appointment_conflicts_check' => 'passed'
                    ]
                ]
            ], 201);

        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Formato de fecha u hora inválido. Use Y-m-d para fecha y H:i para hora.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una cita existente
     */
    public function updateAppointment()
    {
        $request = request();
        // Lógica para actualizar la cita
        $request->validate([
            'xid' => 'required|string', // Hashable ID de la cita
            'doctor_id' => 'nullable|string', // Hashable ID del doctor
            'patient_id' => 'nullable|string', // Hashable ID del paciente
            'appointment_date' => 'nullable|date_format:Y-m-d',
            'appointment_time' => 'nullable|date_format:H:i',
            'duration' => 'nullable|integer|min:15', // Duración en minutos
            'reason_visit' => 'nullable|string|max:1000',
            'treatment_type_id' => 'nullable|string',
            'price' => 'nullable|numeric',
            'room_id' => 'nullable|string',
        ]);

        $id = $this->getIdFromHash($request->xid);

        // Buscar la cita por su XID
        $appointment = Appointment::where('id', $id)->first();
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Cita no encontrada'
            ], 404);
        }
        $doctorId = $request->doctor_id ? $this->getIdFromHash($request->doctor_id) : $appointment->doctor_id;
        $patientId = $request->patient_id ? $this->getIdFromHash($request->patient_id) : $appointment->patient_id;
        $duration = (int) ($request->duration ?? 30); // Duración por defecto 30 minutos, convertir a entero
            
        // 0. Obtener Clinic ID
        $clinicId = $request->clinic_id ? $this->getIdFromHash($request->clinic_id) : (request()->header('X-Clinic-ID') ? $this->getIdFromHash(request()->header('X-Clinic-ID')) : $appointment->clinic_id);

        // Combinar fecha y hora en un datetime usando Carbon para asegurar el formato correcto
        // Treat input time as company local time (no timezone conversion)
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', $request->appointment_date . ' ' . $request->appointment_time);
        $appointmentEndTime = $appointmentDateTime->copy()->addMinutes($duration);
        // Convert Carbon's dayOfWeek (0-6, 0=Sunday) to database format (1-7, 1=Monday, 7=Sunday)
        $dayOfWeek = $appointmentDateTime->dayOfWeek == 0 ? 7 : $appointmentDateTime->dayOfWeek;

         // 1. Verificar holidays del doctor para la fecha específica
            $doctorHoliday = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('start_date', '<=', $request->appointment_date)
                ->where('end_date', '>=', $request->appointment_date)
                ->first();

            if ($doctorHoliday) {
                return response()->json([
                    'success' => false,
                    'message' => 'El doctor no está disponible en esta fecha debido a un día feriado programado.',
                    'details' => [
                        'type' => 'doctor_holiday',
                        'date' => $request->appointment_date,
                        'holiday_start' => $doctorHoliday->start_date,
                        'holiday_end' => $doctorHoliday->end_date
                    ]
                ], 400);
            }

            // 2. Verificar horario de la clínica para ese día de la semana
            $clinicScheduleQuery = ClinicSchedule::where('day_of_week', $dayOfWeek)
                ->where('company_id', company()->id);
                
            if ($clinicId) {
                $clinicScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $clinicSchedule = $clinicScheduleQuery->first();


            if (!$clinicSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'La clínica no tiene horario de atención para este día de la semana.',
                    'details' => [
                        'type' => 'clinic_closed',
                        'day_of_week' => $dayOfWeek
                    ]
                ], 400);
            }

            // 3. Validar que el doctor tenga horario disponible para ese día Y esa clínica
            // Primero buscamos el DoctorSchedule para esta clínica
            $doctorScheduleQuery = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
                ->where('company_id', company()->id);
                
            if ($clinicId) {
                $doctorScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $doctorSchedule = $doctorScheduleQuery->first();
            
            $scheduleDay = null;
            if ($doctorSchedule) {
                $scheduleDay = DoctorScheduleDay::where('schedule_id', $doctorSchedule->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('status', 1)
                    ->first();
            }

            // Verificar si el doctor tiene horario para ese día
            if (!$scheduleDay) {
                return response()->json([
                    'success' => false,
                    'message' => 'El doctor no tiene horario disponible para este día de la semana en esta clínica.',
                    'details' => [
                        'type' => 'doctor_schedule_unavailable',
                        'day_of_week' => $dayOfWeek
                    ]
                ], 400);
            }

            // 4. Ajustar horario del doctor según el horario de la clínica
            $adjustedSchedule = $this->adjustScheduleWithClinic($scheduleDay, $clinicSchedule);
            // Remove timezone setting as we don't use it anymore
            if (!$adjustedSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay horario disponible para este día considerando el horario de la clínica.',
                    'details' => [
                        'type' => 'schedule_conflict',
                        'doctor_schedule' => [
                            'from' => $scheduleDay->available_from,
                            'to' => $scheduleDay->available_to
                        ],
                        'clinic_schedule' => [
                            'from' => $clinicSchedule->start_time,
                            'to' => $clinicSchedule->end_time
                        ]
                    ]
                ], 400);
            }

            // 5. Validar que la hora esté dentro del horario ajustado
            $scheduleStart = Carbon::createFromFormat('H:i:s', $adjustedSchedule['start_time']);
            $scheduleEnd = Carbon::createFromFormat('H:i:s', $adjustedSchedule['end_time']);
            $appointmentTime = Carbon::createFromFormat('H:i', $request->appointment_time);
            $appointmentEndTimeOnly = $appointmentTime->copy()->addMinutes($duration);

            if ($appointmentTime->lt($scheduleStart) || $appointmentEndTimeOnly->gt($scheduleEnd)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cita debe estar dentro del horario disponible ajustado.',
                    'details' => [
                        'type' => 'time_outside_schedule',
                        'available_schedule' => [
                            'from' => $adjustedSchedule['start_time'],
                            'to' => $adjustedSchedule['end_time']
                        ],
                        'requested_time' => [
                            'from' => $request->appointment_time,
                            'to' => $appointmentEndTimeOnly->format('H:i')
                        ]
                    ]
                ], 400);
            }

            // 6. Verificar breaks del doctor para la fecha específica
            $breaks = $this->getDoctorBreaks($doctorId, $request->appointment_date);

            $appointmentStartTime = $appointmentTime->format('H:i');
            $appointmentEndTimeFormatted = $appointmentEndTimeOnly->format('H:i');

            foreach ($breaks as $break) {
                $breakStart = strtotime($break['start_time']);
                $breakEnd = strtotime($break['end_time']);
                $appointmentStart = strtotime($appointmentStartTime);
                $appointmentEnd = strtotime($appointmentEndTimeFormatted);

                // Verificar si hay solapamiento con algún break
                if (
                    ($appointmentStart >= $breakStart && $appointmentStart < $breakEnd) || // La cita empieza durante un break
                    ($appointmentEnd > $breakStart && $appointmentEnd <= $breakEnd) ||   // La cita termina durante un break
                    ($appointmentStart <= $breakStart && $appointmentEnd >= $breakEnd)   // La cita engloba un break
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La cita coincide con un break del doctor.',
                        'details' => [
                            'type' => 'doctor_break_conflict',
                            'break_time' => [
                                'from' => $break['start_time'],
                                'to' => $break['end_time']
                            ],
                            'requested_time' => [
                                'from' => $appointmentStartTime,
                                'to' => $appointmentEndTimeFormatted
                            ]
                        ]
                    ], 400);
                }
            }


             // 7. Validar que no haya conflictos con otras citas existentes
            $conflictingAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $request->appointment_date)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($appointmentDateTime, $appointmentEndTime) {
                    $query->where(function ($q) use ($appointmentDateTime, $appointmentEndTime) {
                        // La nueva cita empieza durante una cita existente
                        $q->where('appointment_date', '<=', $appointmentDateTime)
                          ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?', [$appointmentDateTime]);
                    })->orWhere(function ($q) use ($appointmentDateTime, $appointmentEndTime) {
                        // La nueva cita termina durante una cita existente
                        $q->where('appointment_date', '<', $appointmentEndTime)
                          ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) >= ?', [$appointmentEndTime]);
                    })->orWhere(function ($q) use ($appointmentDateTime, $appointmentEndTime) {
                        // La nueva cita engloba completamente una cita existente
                        $q->where('appointment_date', '>=', $appointmentDateTime)
                          ->whereRaw('DATE_ADD(appointment_date, INTERVAL duration MINUTE) <= ?', [$appointmentEndTime]);
                    });
                })
                ->first();

            if ($conflictingAppointments) {
                $conflictStart = Carbon::parse($conflictingAppointments->appointment_date);
                $conflictEnd = $conflictStart->copy()->addMinutes($conflictingAppointments->duration);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una cita en el horario solicitado.',
                    'details' => [
                        'type' => 'appointment_conflict',
                        'existing_appointment' => [
                            'from' => $conflictStart->format('H:i'),
                            'to' => $conflictEnd->format('H:i'),
                            'duration' => $conflictingAppointments->duration
                        ],
                        'requested_time' => [
                            'from' => $appointmentStartTime,
                            'to' => $appointmentEndTimeFormatted
                        ]
                    ]
                ], 400);
            }

            // Actualizar los campos de la cita
            $appointment->doctor_id = $doctorId;
            $appointment->patient_id = $patientId;
            $appointment->reason_visit = $request->reason_visit ?? $appointment->reason_visit;
            $appointment->status = $request->status ?? $appointment->status;
            if ($clinicId) {
                $appointment->clinic_id = $clinicId;
            }
            if ($request->treatment_type_id) {
                $appointment->treatment_type_id = $this->getIdFromHash($request->treatment_type_id);
            }
            if ($request->room_id) {
                $appointment->room_id = $this->getIdFromHash($request->room_id);
            }
            if ($request->has('price')) {
                $appointment->price = $request->price;
            }
            $appointment->save();



            return $appointment;

    }

    /**
     * Obtener horarios disponibles de un doctor para una fecha específica
     */
    public function getAvailableSlots()
    {
        $request = request();
        
        $request->validate([
            'doctor_id' => 'required|string',
            'date' => 'required|date',
            'duration' => 'nullable|integer|min:15', // Duración requerida para la nueva cita
        ]);

        try {
            $doctorId = $this->getIdFromHash($request->doctor_id);
            $date = $request->date;
            $requestedDuration = (int) ($request->duration ?? 30); // Duración por defecto 30 minutos
            // Convert date('w') format (0-6, 0=Sunday) to database format (1-7, 1=Monday, 7=Sunday)
            $dayOfWeekRaw = date('w', strtotime($date));
            $dayOfWeek = $dayOfWeekRaw == 0 ? 7 : $dayOfWeekRaw;

            // Verificar holidays del doctor para la fecha específica
            $doctorHoliday = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            if ($doctorHoliday) {
                return response()->json([
                    'success' => false,
                    'message' => 'El doctor no está disponible en esta fecha debido a un día feriado programado.',
                    'data' => [
                        'date' => $date,
                        'doctor_holiday' => [
                            'start_date' => $doctorHoliday->start_date,
                            'end_date' => $doctorHoliday->end_date,
                            'reason' => $doctorHoliday->reason ?? 'Día feriado'
                        ],
                        'available_slots' => []
                    ]
                ], 200);
            }

            // 0. Obtener Clinic ID del request o header
            $clinicId = $request->clinic_id ? $this->getIdFromHash($request->clinic_id) : (request()->header('X-Clinic-ID') ? $this->getIdFromHash(request()->header('X-Clinic-ID')) : null);

            // Verificar horario de la clínica para ese día de la semana
            $clinicScheduleQuery = ClinicSchedule::where('day_of_week', $dayOfWeek)
                ->where('company_id', company()->id);
                
            if ($clinicId) {
                $clinicScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $clinicSchedule = $clinicScheduleQuery->first();

            if (!$clinicSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'La clínica no tiene horario de atención para este día de la semana.',
                    'data' => [
                        'date' => $date,
                        'day_of_week' => $dayOfWeek,
                        'clinic_closed' => true,
                        'available_slots' => []
                    ]
                ], 200);
            }

            // Obtener horarios del doctor para ese día Y CLÍNICA
            // Primero buscamos el DoctorSchedule para esta clínica
            $doctorScheduleQuery = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
                ->where('company_id', company()->id);
                
            if ($clinicId) {
                $doctorScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $doctorSchedule = $doctorScheduleQuery->first();
            
            $scheduleDay = null;
            if ($doctorSchedule) {
                $scheduleDay = DoctorScheduleDay::where('schedule_id', $doctorSchedule->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('status', 1)
                    ->first();
            }

            if (!$scheduleDay) {
                return response()->json([
                    'success' => false,
                    'message' => 'El doctor no tiene horarios disponibles para este día en esta clínica',
                    'data' => [
                        'date' => $date,
                        'day_of_week' => $dayOfWeek,
                        'doctor_schedule_unavailable' => true,
                        'available_slots' => []
                    ]
                ], 200);
            }

            // Ajustar horario del doctor según el horario de la clínica
            $adjustedSchedule = $this->adjustScheduleWithClinic($scheduleDay, $clinicSchedule);
            if (!$adjustedSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay horario disponible para este día considerando el horario de la clínica.',
                    'data' => [
                        'date' => $date,
                        'schedule_conflict' => true,
                        'doctor_schedule' => [
                            'from' => $scheduleDay->available_from,
                            'to' => $scheduleDay->available_to
                        ],
                        'clinic_schedule' => [
                            'from' => $clinicSchedule->start_time,
                            'to' => $clinicSchedule->end_time
                        ],
                        'available_slots' => []
                    ]
                ], 200);
            }

            // Obtener citas existentes para esa fecha con su duración
            $existingAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->get()
                ->map(function($appointment) {
                    return [
                        'start_time' => date('H:i', strtotime($appointment->appointment_date)),
                        'end_time' => date('H:i', strtotime($appointment->appointment_date) + ((int)$appointment->duration * 60)),
                        'duration' => (int)$appointment->duration
                    ];
                })
                ->toArray();

            // Obtener breaks del doctor para esta fecha
            $doctorBreaks = $this->getDoctorBreaks($doctorId, $date);

            // Generar slots disponibles considerando la duración requerida y el horario ajustado
            $availableSlots = $this->generateTimeSlotsWithDurationAndValidations(
                $adjustedSchedule['start_time'],
                $adjustedSchedule['end_time'],
                15, // Intervalo de slots en minutos (cada 15 minutos)
                $requestedDuration,
                $existingAppointments,
                $doctorId,
                $date
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'requested_duration' => $requestedDuration,
                    'available_slots' => $availableSlots,
                    'schedule' => [
                        'original_from' => $scheduleDay->available_from,
                        'original_to' => $scheduleDay->available_to,
                        'adjusted_from' => $adjustedSchedule['start_time'],
                        'adjusted_to' => $adjustedSchedule['end_time']
                    ],
                    'existing_appointments' => $existingAppointments,
                    'doctor_breaks' => $doctorBreaks,
                    'clinic_schedule' => [
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $clinicSchedule->start_time,
                        'end_time' => $clinicSchedule->end_time
                    ],
                    'validations' => [
                        'doctor_holiday_check' => 'passed',
                        'clinic_schedule_check' => 'passed',
                        'doctor_schedule_check' => 'passed',
                        'breaks_consideration' => 'applied',
                        'existing_appointments_check' => 'applied'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios disponibles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener horarios disponibles de un doctor para todo un mes
     */
    public function getMonthlyAvailableSlots()
    {
        $request = request();
        
        $request->validate([
            'doctor_id' => 'required|string',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2024',
            'duration' => 'nullable|integer|min:15', // Duración requerida para la nueva cita
        ]);

        try {
            $doctorId = $this->getIdFromHash($request->doctor_id);
            $month = $request->month ?? date('n'); // Mes actual por defecto
            $year = $request->year ?? date('Y'); // Año actual por defecto
            $requestedDuration = (int) ($request->duration ?? 30); // Duración por defecto 30 minutos

            $clinicId = $request->clinic_id ? $this->getIdFromHash($request->clinic_id) : (request()->header('X-Clinic-ID') ? $this->getIdFromHash(request()->header('X-Clinic-ID')) : null);

            // Obtener el DoctorSchedule para esta clínica específica
            $doctorScheduleQuery = \App\Models\DoctorSchedule::where('doctor_id', $doctorId)
                ->where('company_id', company()->id);
                
            if ($clinicId) {
                $doctorScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $doctorSchedule = $doctorScheduleQuery->first();

            if (!$doctorSchedule) {
                 return response()->json([
                    'success' => false,
                    'message' => 'El doctor no tiene horarios configurados para esta clínica'
                ], 404);
            }

            // Obtener todos los horarios del doctor para ESTE schedule
            $doctorSchedules = DoctorScheduleDay::where('schedule_id', $doctorSchedule->id) // Usamos schedule_id en lugar de doctor_id para filtrar por clínica
                ->where('status', 1)
                ->get()
                ->keyBy('day_of_week');

            if ($doctorSchedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El doctor no tiene horarios disponibles configurados'
                ], 404);
            }

            // Obtener todos los horarios de la clínica
            $clinicScheduleQuery = ClinicSchedule::where('company_id', company()->id);
            if ($clinicId) {
                $clinicScheduleQuery->where('clinic_id', $clinicId);
            }
            
            $clinicSchedules = $clinicScheduleQuery->get()
                ->keyBy('day_of_week');

            // Obtener todas las citas del mes
            $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

            $monthlyAppointments = Appointment::where('doctor_id', $doctorId)
                ->whereBetween('appointment_date', [$startOfMonth, $endOfMonth])
                ->where('status', '!=', 'cancelled')
                ->get()
                ->groupBy(function($appointment) {
                    return Carbon::parse($appointment->appointment_date)->day;
                });

            // Obtener todos los holidays del doctor para el mes (incluyendo rangos que se solapen)
            $doctorHolidays = DoctorHoliday::where('doctor_id', $doctorId)
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                        ->orWhereBetween('end_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                        ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('start_date', '<=', $startOfMonth->format('Y-m-d'))
                              ->where('end_date', '>=', $endOfMonth->format('Y-m-d'));
                        });
                })
                ->get();

            // OPTIMIZATION: Fetch all doctor breaks for the month at once (reduces 62+ queries to just 2)
            $allDailyBreaks = DoctorBreak::where('doctor_id', $doctorId)
                ->where('every_day', 1)
                ->get()
                ->map(function($break) {
                    return [
                        'start_time' => $break->start_time,
                        'end_time' => $break->end_time,
                        'every_day' => true
                    ];
                })
                ->toArray();

            $allSpecificBreaks = DoctorBreak::where('doctor_id', $doctorId)
                ->where('every_day', 0)
                ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                ->get()
                ->groupBy('date')
                ->map(function($breaks) {
                    return $breaks->map(function($break) {
                        return [
                            'start_time' => $break->start_time,
                            'end_time' => $break->end_time,
                            'every_day' => false
                        ];
                    })->toArray();
                })
                ->toArray();

            $availableDates = [];
            $timeSlots = [];
            $bookedSlots = [];
            $blockedDays = [];
            $summary = [
                'total_days_in_month' => $startOfMonth->daysInMonth,
                'available_days' => 0,
                'blocked_by_doctor_schedule' => 0,
                'blocked_by_clinic_schedule' => 0,
                'blocked_by_holidays' => 0,
                'blocked_by_schedule_conflict' => 0
            ];

            // Iterar por cada día del mes
            $daysInMonth = $startOfMonth->daysInMonth;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::createFromDate($year, $month, $day);
                // Convert Carbon's dayOfWeek (0-6, 0=Sunday) to database format (1-7, 1=Monday, 7=Sunday)
                $dayOfWeek = $currentDate->dayOfWeek == 0 ? 7 : $currentDate->dayOfWeek;
                $currentDateString = $currentDate->format('Y-m-d');

                // Verificar si hay holiday del doctor para este día
                $isHoliday = false;
                $holidayInfo = null;
                foreach ($doctorHolidays as $holiday) {
                    if ($currentDateString >= $holiday->start_date && $currentDateString <= $holiday->end_date) {
                        $isHoliday = true;
                        $holidayInfo = $holiday;
                        break;
                    }
                }
                
                if ($isHoliday) {
                    $blockedDays[$day] = [
                        'reason_visit' => 'doctor_holiday',
                        'details' => [
                            'start_date' => $holidayInfo->start_date,
                            'end_date' => $holidayInfo->end_date,
                            'reason' => $holidayInfo->reason ?? 'Día feriado'
                        ]
                    ];
                    $summary['blocked_by_holidays']++;
                    continue;
                }

                // Verificar si el doctor tiene horario para este día
                if (!isset($doctorSchedules[$dayOfWeek])) {
                    $blockedDays[$day] = [
                        'reason_visit' => 'doctor_schedule_unavailable',
                        'details' => [
                            'day_of_week' => $dayOfWeek,
                            'day_name' => $currentDate->format('l')
                        ]
                    ];
                    $summary['blocked_by_doctor_schedule']++;
                    continue;
                }

                // Verificar si la clínica tiene horario para este día
                if (!isset($clinicSchedules[$dayOfWeek])) {
                    $blockedDays[$day] = [
                        'reason_visit' => 'clinic_closed',
                        'details' => [
                            'day_of_week' => $dayOfWeek,
                            'day_name' => $currentDate->format('l')
                        ]
                    ];
                    $summary['blocked_by_clinic_schedule']++;
                    continue;
                }

                $schedule = $doctorSchedules[$dayOfWeek];
                $clinicSchedule = $clinicSchedules[$dayOfWeek];
                
                // Ajustar horario del doctor según el horario de la clínica
                $adjustedSchedule = $this->adjustScheduleWithClinic($schedule, $clinicSchedule);
                if (!$adjustedSchedule) {
                    $blockedDays[$day] = [
                        'reason_visit' => 'schedule_conflict',
                        'details' => [
                            'doctor_schedule' => [
                                'from' => $schedule->available_from,
                                'to' => $schedule->available_to
                            ],
                            'clinic_schedule' => [
                                'from' => $clinicSchedule->start_time,
                                'to' => $clinicSchedule->end_time
                            ]
                        ]
                    ];
                    $summary['blocked_by_schedule_conflict']++;
                    continue;
                }
                
                // Obtener citas existentes para este día
                $dayAppointments = $monthlyAppointments->get($day, collect())->map(function($appointment) {
                    return [
                        'start_time' => date('H:i', strtotime($appointment->appointment_date)),
                        'end_time' => date('H:i', strtotime($appointment->appointment_date) + ((int)$appointment->duration * 60)),
                        'duration' => (int)$appointment->duration
                    ];
                })->toArray();

                // Get breaks for this specific day using pre-fetched data
                $dayBreaks = $this->getBreaksForDate($currentDateString, $allDailyBreaks, $allSpecificBreaks);

                // Generar slots disponibles para este día con todas las validaciones (optimized)
                $daySlots = $this->generateTimeSlotsWithDurationAndValidations(
                    $adjustedSchedule['start_time'],
                    $adjustedSchedule['end_time'],
                    15, // Intervalo de slots en minutos
                    $requestedDuration,
                    $dayAppointments,
                    null, // No need to pass doctorId when using preloaded breaks
                    null, // No need to pass date when using preloaded breaks
                    $dayBreaks // Pass preloaded breaks
                );

                // Si hay slots disponibles, agregar el día
                if (!empty($daySlots)) {
                    $availableDates[] = $day;
                    $summary['available_days']++;
                    
                    // Convertir slots a formato de hora AM/PM
                    $timeSlots[$day] = array_map(function($slot) {
                        return date('g:ia', strtotime($slot['start_time']));
                    }, $daySlots);
                }

                // Agregar slots ocupados si los hay 
                if (!empty($dayAppointments)) {
                    $bookedSlots[$day] = array_map(function($appointment) {
                        return date('g:ia', strtotime($appointment['start_time']));
                    }, $dayAppointments);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'available_dates' => $availableDates,
                    'time_slots' => $timeSlots,
                    'booked_slots' => $bookedSlots,
                    'blocked_days' => $blockedDays,
                    'current_month' => (int)$month,
                    'current_year' => (int)$year,
                    'requested_duration' => $requestedDuration,
                    'summary' => $summary,
                    'doctor_schedules' => $doctorSchedules->map(function($schedule) {
                        return [
                            'day_of_week' => $schedule->day_of_week,
                            'available_from' => $schedule->available_from,
                            'available_to' => $schedule->available_to
                        ];
                    })->values(),
                    'clinic_schedules' => $clinicSchedules->map(function($schedule) {
                        return [
                            'day_of_week' => $schedule->day_of_week,
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time
                        ];
                    })->values(),
                    'doctor_holidays' => $doctorHolidays->map(function($holiday) {
                        return [
                            'start_date' => $holiday->start_date,
                            'end_date' => $holiday->end_date,
                            'reason' => $holiday->reason ?? 'Día feriado',
                            'holiday_type' => $holiday->holiday_type ?? 'vacation'
                        ];
                    })->values(),
                    'validations' => [
                        'doctor_holidays_check' => 'applied',
                        'clinic_schedules_check' => 'applied',
                        'doctor_schedules_check' => 'applied',
                        'breaks_consideration' => 'applied',
                        'existing_appointments_check' => 'applied'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios mensuales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar slots de tiempo disponibles considerando la duración requerida
     */
    private function generateTimeSlotsWithDuration($startTime, $endTime, $slotInterval, $requestedDuration, $existingAppointments = [])
    {
        $slots = [];
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        
        while ($start < $end) {
            $timeSlot = date('H:i', $start);
            $slotEndTime = $start + ($requestedDuration * 60); // Fin del slot considerando la duración requerida
            
            // Verificar que el slot completo (incluyendo duración) esté dentro del horario
            if ($slotEndTime <= $end) {
                $slotEndTimeFormatted = date('H:i', $slotEndTime);
                
                // Verificar si el slot está disponible (no se cruza con ninguna cita existente)
                if ($this->isSlotAvailable($timeSlot, $slotEndTimeFormatted, $existingAppointments)) {
                    $slots[] = [
                        'start_time' => $timeSlot,
                        'end_time' => $slotEndTimeFormatted,
                        'duration' => $requestedDuration
                    ];
                }
            }
            
            $start += $slotInterval * 60; // Avanzar por el intervalo especificado
        }
        
        return $slots;
    }

    /**
     * Verificar si un slot de tiempo está disponible
     */
    private function isSlotAvailable($slotStart, $slotEnd, $existingAppointments, $doctorId = null, $date = null)
    {
        $slotStartTime = strtotime($slotStart);
        $slotEndTime = strtotime($slotEnd);
        
        // Verificar conflictos con citas existentes
        foreach ($existingAppointments as $appointment) {
            $appointmentStart = strtotime($appointment['start_time']);
            $appointmentEnd = strtotime($appointment['end_time']);
            
            // Verificar si hay solapamiento
            if (
                ($slotStartTime >= $appointmentStart && $slotStartTime < $appointmentEnd) || // El slot empieza durante una cita
                ($slotEndTime > $appointmentStart && $slotEndTime <= $appointmentEnd) ||   // El slot termina durante una cita
                ($slotStartTime <= $appointmentStart && $slotEndTime >= $appointmentEnd)   // El slot engloba una cita
            ) {
                return false; // Hay conflicto
            }
        }

        // Verificar conflictos con breaks del doctor (si se proporcionan doctorId y date)
        if ($doctorId && $date) {
            $breaks = $this->getDoctorBreaks($doctorId, $date);
            
            foreach ($breaks as $break) {
                $breakStart = strtotime($break['start_time']);
                $breakEnd = strtotime($break['end_time']);
                
                // Verificar si hay solapamiento con los breaks
                if (
                    ($slotStartTime >= $breakStart && $slotStartTime < $breakEnd) || // El slot empieza durante un break
                    ($slotEndTime > $breakStart && $slotEndTime <= $breakEnd) ||   // El slot termina durante un break
                    ($slotStartTime <= $breakStart && $slotEndTime >= $breakEnd)   // El slot engloba un break
                ) {
                    return false; // Hay conflicto
                }
            }
        }
        
        return true; // No hay conflicto
    }

    /**
     * Generar slots de tiempo disponibles (método legacy - mantener para compatibilidad)
     */
    private function generateTimeSlots($startTime, $endTime, $slotDuration, $existingAppointments = [])
    {
        $slots = [];
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        
        while ($start < $end) {
            $timeSlot = date('H:i', $start);
            
            // Verificar si el slot no está ocupado
            if (!in_array($timeSlot, $existingAppointments)) {
                $slots[] = $timeSlot;
            }
            
            $start += $slotDuration * 60; // Convertir minutos a segundos
        }
        
        return $slots;
    }

    /**
     * Verificar si un día específico está bloqueado por holidays o clinic schedule
     * Retorna un array con información detallada del bloqueo o false si no está bloqueado
     */
    private function isDayBlocked($doctorId, $date)
    {
        $dateObj = Carbon::parse($date);
        $dayOfWeek = $dateObj->dayOfWeek;

        // 1. Verificar si hay holiday del doctor para esa fecha específica
        $doctorHoliday = DoctorHoliday::where('doctor_id', $doctorId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if ($doctorHoliday) {
            return [
                'blocked' => true,
                'reason_visit' => 'doctor_holiday',
                'details' => [
                    'start_date' => $doctorHoliday->start_date,
                    'end_date' => $doctorHoliday->end_date,
                    'reason' => $doctorHoliday->reason ?? 'Día feriado'
                ]
            ];
        }

        // 2. Verificar el horario general de la clínica para ese día de la semana
        $clinicSchedule = ClinicSchedule::where('day_of_week', $dayOfWeek)
            ->where('company_id', company()->id)
            ->first();

        // Si no hay horario de clínica para ese día, está bloqueado
        if (!$clinicSchedule) {
            return [
                'blocked' => true,
                'reason_visit' => 'clinic_closed',
                'details' => [
                    'day_of_week' => $dayOfWeek,
                    'day_name' => $dateObj->format('l')
                ]
            ];
        }

        return false; // No está bloqueado
    }

    /**
     * Obtener los breaks del doctor para una fecha específica
     */
    private function getDoctorBreaks($doctorId, $date)
    {
        $breaks = [];

        // Breaks que aplican todos los días
        $dailyBreaks = DoctorBreak::where('doctor_id', $doctorId)
            ->where('every_day', 1)
            ->get();

        foreach ($dailyBreaks as $break) {
            $breaks[] = [
                'start_time' => $break->break_from,
                'end_time' => $break->break_to
            ];
        }

        // Breaks específicos para la fecha
        $specificBreaks = DoctorBreak::where('doctor_id', $doctorId)
            ->where('every_day', 0)
            ->where('date', $date)
            ->get();

        foreach ($specificBreaks as $break) {
            $breaks[] = [
                'start_time' => $break->break_from,
                'end_time' => $break->break_to
            ];
        }

        return $breaks;
    }

    /**
     * Ajustar horario del doctor según el horario de la clínica
     */
    /**
     * Ajustar horario del doctor según el horario de la clínica
     */
    private function adjustScheduleWithClinic($doctorSchedule, $clinicSchedule)
    {
        if (!$clinicSchedule) {
            return null; // No hay horario de clínica
        }

        // Tomar el horario más restrictivo (el que inicia más tarde y termina más temprano)
        $startTime = max(strtotime($doctorSchedule->available_from), strtotime($clinicSchedule->start_time));
        $endTime = min(strtotime($doctorSchedule->available_to), strtotime($clinicSchedule->end_time));

        // Si el horario resultante es inválido, retornar null
        if ($startTime >= $endTime) {
            return null;
        }

        return [
            'start_time' => date('H:i:s', $startTime),
            'end_time' => date('H:i:s', $endTime)
        ];
    }

    /**
     * Generar slots de tiempo disponibles considerando todas las validaciones
     */
    private function generateTimeSlotsWithDurationAndValidations($startTime, $endTime, $slotInterval, $requestedDuration, $existingAppointments = [], $doctorId = null, $date = null, $preloadedBreaks = null)
    {
        $slots = [];
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        
        while ($start < $end) {
            $timeSlot = date('H:i', $start);
            $slotEndTime = $start + ($requestedDuration * 60); // Fin del slot considerando la duración requerida
            
            // Verificar que el slot completo (incluyendo duración) esté dentro del horario
            if ($slotEndTime <= $end) {
                $slotEndTimeFormatted = date('H:i', $slotEndTime);
                
                // Use preloaded breaks if provided (optimization for monthly queries)
                if ($preloadedBreaks !== null) {
                    // Verificar si el slot está disponible usando breaks pre-cargados
                    if ($this->isSlotAvailableWithBreaks($timeSlot, $slotEndTimeFormatted, $existingAppointments, $preloadedBreaks)) {
                        $slots[] = [
                            'start_time' => $timeSlot,
                            'end_time' => $slotEndTimeFormatted,
                            'duration' => $requestedDuration
                        ];
                    }
                } else {
                    // Fallback to original method with database query
                    if ($this->isSlotAvailable($timeSlot, $slotEndTimeFormatted, $existingAppointments, $doctorId, $date)) {
                        $slots[] = [
                            'start_time' => $timeSlot,
                            'end_time' => $slotEndTimeFormatted,
                            'duration' => $requestedDuration
                        ];
                    }
                }
            }
            
            $start += $slotInterval * 60; // Avanzar por el intervalo especificado
        }
        
        return $slots;
    }

    /**
     * Helper method to get breaks for a specific date from pre-fetched data
     */
    private function getBreaksForDate($date, $dailyBreaks, $specificBreaks)
    {
        $breaks = $dailyBreaks; // Start with daily breaks that apply every day
        
        // Add specific breaks for this date if they exist
        if (isset($specificBreaks[$date])) {
            $breaks = array_merge($breaks, $specificBreaks[$date]);
        }
        
        return $breaks;
    }

    /**
     * Optimized version of isSlotAvailable that uses pre-loaded breaks
     */
    private function isSlotAvailableWithBreaks($slotStart, $slotEnd, $existingAppointments, $breaks)
    {
        $slotStartTime = strtotime($slotStart);
        $slotEndTime = strtotime($slotEnd);
        
        // Verificar conflictos con citas existentes
        foreach ($existingAppointments as $appointment) {
            $appointmentStart = strtotime($appointment['start_time']);
            $appointmentEnd = strtotime($appointment['end_time']);
            
            // Verificar si hay solapamiento
            if (
                ($slotStartTime >= $appointmentStart && $slotStartTime < $appointmentEnd) ||
                ($slotEndTime > $appointmentStart && $slotEndTime <= $appointmentEnd) ||
                ($slotStartTime <= $appointmentStart && $slotEndTime >= $appointmentEnd)
            ) {
                return false;
            }
        }

        // Verificar conflictos con breaks pre-cargados
        foreach ($breaks as $break) {
            $breakStart = strtotime($break['start_time']);
            $breakEnd = strtotime($break['end_time']);
            
            // Verificar si hay solapamiento con el break
            if (
                ($slotStartTime >= $breakStart && $slotStartTime < $breakEnd) ||
                ($slotEndTime > $breakStart && $slotEndTime <= $breakEnd) ||
                ($slotStartTime <= $breakStart && $slotEndTime >= $breakEnd)
            ) {
                return false;
            }
        }
        
        return true;
    }

}
