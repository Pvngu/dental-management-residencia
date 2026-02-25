<?php

namespace App\Services\ActivityLog;

use App\Models\UserAddress;

class AddressActivityLogService extends BaseActivityLogService
{
    /**
     * Log address creation
     */
    public function logAddressCreated(UserAddress $address, $patientId)
    {
        $patientName = $this->getPatientName($patientId);
        $formattedAddress = $this->formatAddress($address);
        
        return $this->logActivity(
            'CREATED',
            'addresses',
            ActivityLogMessageProvider::getLocalizedMessage('address_added', [
                'patient_name' => $patientName,
                'address' => $formattedAddress
            ]),
            $address->company_id ?? company()->id
        );
    }

    /**
     * Log address update
     */
    public function logAddressUpdated(UserAddress $address, $patientId)
    {
        $changes = $this->formatChanges($address);
        
        if (!empty($changes)) {
            $patientName = $this->getPatientName($patientId);
            $formattedAddress = $this->formatAddress($address);
            
            return $this->logActivity(
                'UPDATED',
                'addresses',
                ActivityLogMessageProvider::getLocalizedMessage('address_updated', [
                    'patient_name' => $patientName,
                    'address' => $formattedAddress,
                    'changes' => $changes
                ]),
                $address->company_id ?? company()->id
            );
        }
        
        return null;
    }

    /**
     * Log address deletion
     */
    public function logAddressDeleted(UserAddress $address, $patientId)
    {
        $patientName = $this->getPatientName($patientId);
        $formattedAddress = $this->formatAddress($address);
        
        return $this->logActivity(
            'DELETED',
            'addresses',
            ActivityLogMessageProvider::getLocalizedMessage('address_deleted', [
                'patient_name' => $patientName,
                'address' => $formattedAddress
            ]),
            $address->company_id ?? company()->id
        );
    }
}