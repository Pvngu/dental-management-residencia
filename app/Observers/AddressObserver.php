<?php

namespace App\Observers;

use App\Models\UserAddress;
use App\Models\PatientHistory;
use App\Models\Patient;
use App\Models\User;
use App\Services\ActivityLog\AddressActivityLogService;
use Illuminate\Support\Facades\Log;

class AddressObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new AddressActivityLogService();
    }

    /**
     * Handle the UserAddress "created" event.
     */
    public function created(UserAddress $address): void
    {
        // Get the user and check if they have a patient record
        $user = User::find($address->user_id);
        
        if ($user && $user->patient) {
            $data = [
                'address_id' => $address->id,
                'address_type' => $address->address_type,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'neighborhood' => $address->neighborhood,
                'postal_code' => $address->postal_code,
                'city' => $address->city,
                'state' => $address->state,
                'country_code' => $address->country_code,
                'is_default' => $address->is_default,
                'latitude' => $address->latitude,
                'longitude' => $address->longitude,
                'status' => $address->status,
            ];

            $patientId = $user->patient->id;

            PatientHistory::createEntry(
                $patientId,
                'address_added',
                $data,
                null,
                $address
            );

            $this->activityLogService->logAddressCreated($address, $patientId);
        }
    }

    /**
     * Handle the UserAddress "updated" event.
     */
    public function updated(UserAddress $address): void
    {
        // Get the user and check if they have a patient record
        $user = User::find($address->user_id);
        
        if ($user && $user->patient) {
            $original = $address->getOriginal();
            $changes = [];

            foreach ($address->getDirty() as $key => $value) {
                if (in_array($key, ['updated_at'])) {
                    continue;
                }

                $changes[$key] = [
                    'old' => array_key_exists($key, $original) ? $original[$key] : null,
                    'new' => $value
                ];
            }

            if (!empty($changes)) {
                $data = [
                    'address_id' => $address->id,
                    'address_type' => $address->address_type,
                    'changes' => $changes,
                    'current_address' => $address->full_address,
                ];

                $patientId = $user->patient->id;

                PatientHistory::createEntry(
                    $patientId,
                    'address_updated',
                    $data,
                    null,
                    $address
                );

                $this->activityLogService->logAddressUpdated($address, $patientId);
            }
        }
    }

    /**
     * Handle the UserAddress "deleted" event.
     */
    public function deleted(UserAddress $address): void
    {
        // Get the user and check if they have a patient record
        $user = User::find($address->user_id);
        
        if ($user && $user->patient) {
            $data = [
                'address_id' => $address->id,
                'address_type' => $address->address_type,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'neighborhood' => $address->neighborhood,
                'deleted_at' => now(),
            ];

            $patientId = $user->patient->id;

            PatientHistory::createEntry(
                $patientId,
                'address_deleted',
                $data,
                null,
                $address
            );

            $this->activityLogService->logAddressDeleted($address, $patientId);
        }
    }
}
