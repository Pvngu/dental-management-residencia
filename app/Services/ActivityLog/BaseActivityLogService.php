<?php

namespace App\Services\ActivityLog;

use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class BaseActivityLogService
{
    /**
     * Log activity in the activity_logs table
     */
    public function logActivity(string $action, string $entity, string $description, ?int $companyId = null, ?int $patientId = null)
    {
        $user = user();
        $company = company();
        
        // Use provided company_id or fall back to current company
        $activityCompanyId = $companyId ?? ($company ? $company->id : 1);
        
        $userData = null;
        if ($user) {
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->name . ' ' . ($user->last_name ?? ''),
                'company_id' => $user->company_id,
            ];
        }

        // Create activity log entry
        $id = DB::table('activity_logs')->insertGetId([
            'action' => $action,
            'entity' => ucfirst($entity),
            'datetime' => now(),
            'description' => $description,
            'user' => json_encode($userData),
            'company_id' => $activityCompanyId,
            'patient_id' => $patientId,
            'json_log' => '{}',
            'created_at' => now()
        ]);

        // Build simplified json_log
        $jsonLog = [
            'data' => ['description' => $description],
            'action' => $action,
            'entity' => $entity,
            'metadata' => [
                'server' => gethostname(),
                'database' => config('database.connections.mysql.database')
            ],
            'timestamp' => now()->format('Y-m-d H:i:s.u'),
            'description' => $description,
            'id' => $id
        ];

        // Update with complete JSON log
        DB::table('activity_logs')
            ->where('id', $id)
            ->update(['json_log' => json_encode($jsonLog)]);

        return $id;
    }

    /**
     * Format model changes into a readable string
     */
    public function formatChanges($model)
    {
        if (!$model || !method_exists($model, 'getDirty')) {
            return '';
        }

        $changes = [];
        $dirty = $model->getDirty();

        // Skip certain fields
        $skipFields = ['updated_at', 'password', 'remember_token', 'email_verification_code'];

        foreach ($dirty as $key => $value) {
            if (in_array($key, $skipFields)) {
                continue;
            }

            $original = $model->getOriginal();
            $oldValue = $original[$key] ?? null;

            $changes[$key] = [
                'old' => $oldValue,
                'new' => $value
            ];
        }

        return $this->formatChangesArray($changes);
    }

    /**
     * Format changes array into a readable string
     */
    private function formatChangesArray(array $changes)
    {
        if (empty($changes)) {
            return '';
        }

        if (count($changes) == 1) {
            $field = array_key_first($changes);
            $change = $changes[$field];
            return ActivityLogMessageProvider::getLocalizedMessage('field_changed', [
                'field' => ucfirst(str_replace('_', ' ', $field)),
                'old_value' => $change['old'] ?? 'N/A',
                'new_value' => $change['new'] ?? 'N/A'
            ]);
        }

        return ActivityLogMessageProvider::getLocalizedMessage('multiple_changes', [
            'count' => count($changes)
        ]);
    }

    /**
     * Get patient name safely
     */
    public function getPatientName($patientId)
    {
        try {
            $patient = Patient::find($patientId);
            if ($patient && $patient->user) {
                return $patient->user->name . ' ' . ($patient->user->last_name ?? '');
            }
            return "Patient ID: {$patientId}";
        } catch (\Exception $e) {
            return "Patient ID: {$patientId}";
        }
    }

    /**
     * Get user name safely
     */
    public function getUserName($userId)
    {
        try {
            $user = User::find($userId);
            if ($user) {
                return trim($user->name . ' ' . ($user->last_name ?? ''));
            }
            return "User ID: $userId";
        } catch (\Exception $e) {
            return "User ID: $userId";
        }
    }

    /**
     * Get doctor name safely
     */
    public function getDoctorName($doctorId)
    {
        try {
            if (!$doctorId) {
                return 'N/A';
            }
            
            $doctor = Doctor::find($doctorId);
            if ($doctor && $doctor->user) {
                return 'Dr. ' . $doctor->user->name . ' ' . ($doctor->user->last_name ?? '');
            }
            return "Doctor ID: {$doctorId}";
        } catch (\Exception $e) {
            return "Doctor ID: {$doctorId}";
        }
    }

    /**
     * Truncate text to specified length
     */
    public function truncateText($text, $length = 50)
    {
        if (!$text) return '';
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Format address into a readable string
     */
    public function formatAddress($address)
    {
        if (!$address) return '';
        
        $parts = [];
        if ($address->address_line_1) $parts[] = $address->address_line_1;
        if ($address->address_line_2) $parts[] = $address->address_line_2;
        if ($address->city) $parts[] = $address->city;
        if ($address->state) $parts[] = $address->state;
        if ($address->zipcode) $parts[] = $address->zipcode;
        
        return implode(', ', $parts);
    }
}