<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Attendance\IndexRequest;
use App\Http\Requests\Api\Attendance\StoreRequest;
use App\Http\Requests\Api\Attendance\UpdateRequest;
use App\Http\Requests\Api\Attendance\DeleteRequest;
use App\Models\User;
use App\Classes\Common;

class AttendanceController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = Attendance::class;
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

                $query = $query->whereRaw('attendances.clock_time >= ?', [$startDate])
                    ->whereRaw('attendances.clock_time <= ?', [$endDate]);
            }
        }

        return $query->with(['user', 'lastUpdatedByUser']);
    }

    public function toggle()
    {
        $user = user();
        $company = \App\Models\Company::find($user->company_id);

        // Get the latest attendance record for this user today (in company timezone)
        $today = Common::getCompanyToday($company);
        
        $latestAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('clock_time', $today)
            ->orderBy('clock_time', 'desc')
            ->first();

        // Determine the next status based on the latest attendance
        $nextStatus = 'clock_in'; // Default to clock_in if no attendance today
        
        if ($latestAttendance) {
            $nextStatus = match($latestAttendance->status) {
                'clock_in' => 'clock_out',
                'clock_out' => 'clock_in',
                'break_start' => 'break_end',
                'break_end' => 'clock_out',
                default => 'clock_in'
            };
        }

        // Prevent clock-in if user has already completed daily attendance (clocked out)
        if ($nextStatus === 'clock_in' && $this->hasCompletedDailyAttendance($user->id, $company)) {
            return response()->json([
                'error' => 'You cannot clock in again after clocking out for today. Please wait until tomorrow.',
                'has_completed_daily_attendance' => true
            ], 422);
        }

        // Create new attendance record with company timezone
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->company_id = $user->company_id;
        $attendance->clock_time = Common::getCompanyDateTime($company);
        $attendance->status = $nextStatus;
        $attendance->notes = "Auto-generated via toggle endpoint";
        $attendance->save();

        // Prepare clock_in_time for response
        $clockInTime = null;
        if ($nextStatus === 'clock_in') {
            $clockInTime = $attendance->clock_time->format('Y-m-d H:i:s');
        } else {
            // If clocking out, no active clock_in time
            $clockInTime = null;
        }

        // Compute today's and week's summaries using existing helpers
        // Fetch today's attendances for this user
        $todayAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('clock_time', $today)
            ->orderBy('clock_time', 'asc')
            ->get();

        $startOfWeek = Common::getCompanyStartOfWeek($company);
        $endOfWeek = Common::getCompanyEndOfWeek($company);

        $weekAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('clock_time', '>=', $startOfWeek)
            ->whereDate('clock_time', '<=', $endOfWeek)
            ->orderBy('clock_time', 'asc')
            ->get();

        $todayHours = $this->calculateHoursWorked($todayAttendances, $company);
        $weekHours = $this->calculateWeekHours($weekAttendances, $company);

        // Check if user has completed daily attendance (after the new record is created)
        $hasCompletedDaily = $this->hasCompletedDailyAttendance($user->id, $company);

        // Map internal status to response status string
        $responseStatus = $nextStatus === 'clock_in' ? 'clocked_in' : 'clocked_out';
        $responseMessage = $nextStatus === 'clock_in' ? 'You have clocked in successfully.' : 'You have clocked out successfully.';

        return response()->json([
            'status' => $responseStatus,
            'message' => $responseMessage,
            'clock_in_time' => $clockInTime,
            'has_completed_daily_attendance' => $hasCompletedDaily,
            'summary' => [
                'today' => $todayHours['formatted'],
                'week' => $weekHours['formatted']
            ]
        ]);
    }

    public function history()
    {
        $user = user();
        $request = request();
        $company = \App\Models\Company::find($user->company_id);
        
        // Get date range parameters (default to current month in company timezone)
        $companyNow = Common::getCompanyDateTime($company);
        $startDate = $request->get('start_date', $companyNow->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', $companyNow->copy()->endOfMonth()->format('Y-m-d'));
        
        // Get attendance records for the user within date range
        $attendances = Attendance::where('user_id', $user->id)
            ->whereDate('clock_time', '>=', $startDate)
            ->whereDate('clock_time', '<=', $endDate)
            ->orderBy('clock_time', 'desc')
            ->get();

        // Group by date for better organization
        $attendancesByDate = $attendances->groupBy(function ($attendance) {
            return $attendance->clock_time->format('Y-m-d');
        });

        // Calculate summary statistics
        $totalDays = $attendancesByDate->count();
        $totalHours = 0;
        $dailySummaries = [];

        foreach ($attendancesByDate as $date => $dailyAttendances) {
            $clockIn = $dailyAttendances->where('status', 'clock_in')->first();
            $clockOut = $dailyAttendances->where('status', 'clock_out')->last();
            
            $hoursWorked = 0;
            if ($clockIn && $clockOut) {
                $hoursWorked = $clockOut->clock_time->diffInHours($clockIn->clock_time);
                $totalHours += $hoursWorked;
            }

            $dailySummaries[] = [
                'date' => $date,
                'clock_in' => $clockIn ? $clockIn->clock_time->format('H:i:s') : null,
                'clock_out' => $clockOut ? $clockOut->clock_time->format('H:i:s') : null,
                'hours_worked' => round($hoursWorked, 2),
                'total_entries' => $dailyAttendances->count(),
                'status_summary' => $dailyAttendances->groupBy('status')->map->count(),
                'entries' => $dailyAttendances->map(function ($attendance) {
                    return [
                        'id' => $attendance->xid,
                        'status' => $attendance->status,
                        'time' => $attendance->clock_time->format('H:i:s'),
                        'datetime' => $attendance->clock_time->format('Y-m-d H:i:s'),
                        'notes' => $attendance->notes
                    ];
                })
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance summary retrieved successfully',
            'data' => [
                'doctor_name' => $user->name,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'summary' => [
                    'total_days_attended' => $totalDays,
                    'total_hours_worked' => round($totalHours, 2),
                    'average_hours_per_day' => $totalDays > 0 ? round($totalHours / $totalDays, 2) : 0,
                    'total_attendance_records' => $attendances->count()
                ],
                'daily_summaries' => $dailySummaries,
                'recent_activity' => $attendances->take(10)->map(function ($attendance) {
                    return [
                        'id' => $attendance->xid,
                        'status' => $attendance->status,
                        'datetime' => $attendance->clock_time->format('Y-m-d H:i:s'),
                        'notes' => $attendance->notes
                    ];
                })
            ]
        ]);
    }

    public function summary()
    {
        $user = user();
        $company = \App\Models\Company::find($user->company_id);

        // Get today's date in company timezone
        $today = Common::getCompanyToday($company);
        
        // Get start and end of current week in company timezone
        $startOfWeek = Common::getCompanyStartOfWeek($company);
        $endOfWeek = Common::getCompanyEndOfWeek($company);

        // Get today's attendances
        $todayAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('clock_time', $today)
            ->orderBy('clock_time', 'asc')
            ->get();

        // Get this week's attendances
        $weekAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('clock_time', '>=', $startOfWeek)
            ->whereDate('clock_time', '<=', $endOfWeek)
            ->orderBy('clock_time', 'asc')
            ->get();

        // Calculate today's hours
        $todayHours = $this->calculateHoursWorked($todayAttendances, $company);
        
        // Calculate this week's hours
        $weekHours = $this->calculateWeekHours($weekAttendances, $company);

        // Get current status and clock_in time
        $currentStatus = $todayAttendances->last();
        $clockInEntry = $todayAttendances->where('status', 'clock_in')->first();
        $isClockedIn = $currentStatus && in_array($currentStatus->status, ['clock_in', 'break_end']);

        // Check if user has completed daily attendance
        $hasCompletedDaily = $this->hasCompletedDailyAttendance($user->id, $company);

        // Return simplified format
        return response()->json([
            'isClockedIn' => $isClockedIn,
            'clock_in_time' => $clockInEntry ? $clockInEntry->clock_time->format('Y-m-d H:i:s') : null,
            'has_completed_daily_attendance' => $hasCompletedDaily,
            'summary' => [
                'today' => $todayHours['formatted'],
                'week' => $weekHours['formatted']
            ]
        ]);
    }

    /**
     * Calculate hours worked for a collection of attendances
     * 
     * @param \Illuminate\Support\Collection $attendances
     * @param \App\Models\Company|null $company
     * @return array
     */
    private function calculateHoursWorked($attendances, $company = null)
    {
        $totalMinutes = 0;
        $clockIn = null;
        $breakStart = null;

        foreach ($attendances as $attendance) {
            switch ($attendance->status) {
                case 'clock_in':
                    $clockIn = $attendance->clock_time;
                    break;
                    
                case 'break_start':
                    if ($clockIn) {
                        // Add time from clock_in to break_start
                        $totalMinutes += $clockIn->diffInMinutes($attendance->clock_time);
                    }
                    $breakStart = $attendance->clock_time;
                    $clockIn = null; // Reset clock_in
                    break;
                    
                case 'break_end':
                    $clockIn = $attendance->clock_time; // Restart working time
                    $breakStart = null;
                    break;
                    
                case 'clock_out':
                    if ($clockIn) {
                        // Add time from clock_in (or break_end) to clock_out
                        $totalMinutes += $clockIn->diffInMinutes($attendance->clock_time);
                        $clockIn = null;
                    }
                    break;
            }
        }

        // If still clocked in, calculate time until now (in company timezone)
        if ($clockIn && !$breakStart) {
            $now = Common::getCompanyDateTime($company);
            $totalMinutes += $clockIn->diffInMinutes($now);
        }

        return [
            'minutes' => $totalMinutes,
            'formatted' => $this->formatMinutesToHours($totalMinutes)
        ];
    }

    /**
     * Calculate total hours for the week grouped by day
     * 
     * @param \Illuminate\Support\Collection $attendances
     * @param \App\Models\Company|null $company
     * @return array
     */
    private function calculateWeekHours($attendances, $company = null)
    {
        $attendancesByDate = $attendances->groupBy(function ($attendance) {
            return $attendance->clock_time->format('Y-m-d');
        });

        $totalMinutes = 0;

        foreach ($attendancesByDate as $date => $dailyAttendances) {
            $dailyHours = $this->calculateHoursWorked($dailyAttendances, $company);
            $totalMinutes += $dailyHours['minutes'];
        }

        return [
            'minutes' => $totalMinutes,
            'formatted' => $this->formatMinutesToHours($totalMinutes)
        ];
    }

    /**
     * Check if the user has completed their daily attendance (clocked out)
     * 
     * @param int $userId
     * @param \App\Models\Company $company
     * @return bool
     */
    private function hasCompletedDailyAttendance($userId, $company)
    {
        $today = Common::getCompanyToday($company);
        
        $latestAttendance = Attendance::where('user_id', $userId)
            ->whereDate('clock_time', $today)
            ->orderBy('clock_time', 'desc')
            ->first();

        // If there's no attendance today, they haven't completed it
        if (!$latestAttendance) {
            return false;
        }

        // If the latest status is clock_out, they have completed daily attendance
        return $latestAttendance->status === 'clock_out';
    }

    /**
     * Format minutes to "Xh Ym" format
     */
    private function formatMinutesToHours($totalMinutes)
    {
        if ($totalMinutes <= 0) {
            return '0h 0m';
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%dh %dm', $hours, $minutes);
    }
}