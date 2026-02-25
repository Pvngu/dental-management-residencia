<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Patient;
use Vinkla\Hashids\Facades\Hashids;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Company-wide channel for all staff members
Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    // Check if user belongs to this company
    return $user->company_id === (int) $companyId;
});

// Patient-specific channel - accessible by staff of the same company
Broadcast::channel('patient.{patientId}', function ($user, $patientId) {
    // Try to decode the xid (hash) to get the real ID
    try {
        $realId = null;
        
        // Try to decode as hash ID
        if (!is_numeric($patientId)) {
            $decoded = Hashids::decode($patientId);
            $realId = $decoded[0] ?? null;
        } else {
            // Already numeric
            $realId = (int)$patientId;
        }
        
        if (!$realId) {
            return false;
        }
        
        $patient = Patient::find($realId);
        
        if (!$patient) {
            return false;
        }
        
        return $user->company_id === $patient->company_id;
    } catch (\Exception $e) {
        \Log::error('Channel auth error: ' . $e->getMessage());
        return false;
    }
});
