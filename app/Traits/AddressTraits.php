<?php

namespace App\Traits;

use App\Models\UserAddress;
use App\Models\ClinicAddress;
use App\Models\InsuranceProviderAddress;

trait AddressTraits
{
    /**
     * Handle addresses for user entity
     * Can handle both array of addresses and individual address fields
     */
    protected function handleUserAddresses($user, $addressData)
    {
        $company = company();
        
        // If it's an array of addresses (multiple addresses)
        if (is_array($addressData) && isset($addressData[0]) && is_array($addressData[0])) {
            return $this->handleMultipleUserAddresses($user, $addressData);
        }

        // Check if addresses key exists in data
        if (isset($addressData['addresses'])) {
            $addresses = $addressData['addresses'];
            
            // Decode if string (JSON)
            if (is_string($addresses)) {
                $addresses = json_decode($addresses, true);
            }

            if (is_array($addresses) && count($addresses) > 0) {
                return $this->handleMultipleUserAddresses($user, $addresses);
            }
        }
        
        // If it's individual address fields or single address object
        return $this->handleSingleUserAddress($user, $addressData);
    }

    /**
     * Handle multiple user addresses array
     */
    private function handleMultipleUserAddresses($user, $addresses)
    {
        $company = company();
        
        // Delete existing addresses for this user
        $user->addresses()->delete();
        
        foreach ($addresses as $addressData) {
            $this->createUserAddress($user, $addressData, $company);
        }
    }

    /**
     * Handle single address from individual fields
     */
    private function handleSingleUserAddress($user, $data)
    {
        $company = company();
        
        // Delete existing addresses for this user
        $user->addresses()->delete();
        
        // Create address from individual fields
        $addressData = [
            'address_line_1' => $data['address'] ?? $data['address_line_1'] ?? '',
            'address_line_2' => $data['address_2'] ?? $data['address_line_2'] ?? '',
            'neighborhood' => $data['neighborhood'] ?? '',
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'postal_code' => $data['postal_code'] ?? $data['zip_code'] ?? '',
            'country_code' => $data['country_code'] ?? '',
            'country_name' => $data['country_name'] ?? '',
            'address_type' => $data['address_type'] ?? 'home',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_default' => $data['is_default'] ?? true,
            'status' => $this->convertStatusToBoolean($data['status'] ?? true),
            'contact_name' => $data['contact_name'] ?? '',
            'contact_phone' => $data['contact_phone'] ?? '',
            'reference' => $data['reference'] ?? '',
            'notes' => $data['notes'] ?? '',
        ];
        
        $this->createUserAddress($user, $addressData, $company);
    }

    /**
     * Create user address record
     */
    private function createUserAddress($user, $addressData, $company)
    {
        $address = new UserAddress();
        $address->user_id = $user->id;
        $address->company_id = $company->id;
        $this->fillAddressData($address, $addressData);
        $address->save();
    }

    /**
     * Add address to user from request data
     */
    protected function addAddressToUser($user, $requestData)
    {
        // Check if we have address data in the request
        if ($this->hasAddressData($requestData)) {
            $this->handleUserAddresses($user, $requestData);
        }
    }

    /**
     * Handle clinic addresses
     */
    protected function handleClinicAddresses($clinic, $addressData)
    {
        $company = company();
        
        if (is_array($addressData) && count($addressData) > 0) {
            $clinic->addresses()->delete();
            
            foreach ($addressData as $addrData) {
                $address = new ClinicAddress();
                $address->clinic_location_id = $clinic->id;
                $address->company_id = $company->id;
                $this->fillAddressData($address, $addrData);
                $address->save();
            }
        }
    }

    /**
     * Handle insurance provider addresses
     */
    protected function handleInsuranceProviderAddresses($provider, $addressData)
    {
        $company = company();
        
        if (is_array($addressData) && count($addressData) > 0) {
            $provider->addresses()->delete();
            
            foreach ($addressData as $addrData) {
                $address = new InsuranceProviderAddress();
                $address->insurance_provider_id = $provider->id;
                $address->company_id = $company->id;
                $this->fillAddressData($address, $addrData);
                $address->save();
            }
        }
    }

    /**
     * Fill address data (shared logic for all address types)
     */
    private function fillAddressData($address, $addressData)
    {
        $address->address_line_1 = $addressData['address_line_1'] ?? '';
        $address->address_line_2 = $addressData['address_line_2'] ?? '';
        $address->neighborhood = $addressData['neighborhood'] ?? '';
        $address->city = $addressData['city'] ?? '';
        $address->state = $addressData['state'] ?? '';
        $address->postal_code = $addressData['postal_code'] ?? '';
        $address->country_code = $addressData['country_code'] ?? '';
        $address->country_name = $addressData['country_name'] ?? '';
        $address->reference = $addressData['reference'] ?? '';
        $address->latitude = $addressData['latitude'] ?? null;
        $address->longitude = $addressData['longitude'] ?? null;
        $address->address_type = $addressData['address_type'] ?? 'home';
        $address->contact_name = $addressData['contact_name'] ?? '';
        $address->contact_phone = $addressData['contact_phone'] ?? '';
        $address->notes = $addressData['notes'] ?? '';
        $address->is_default = $addressData['is_default'] ?? false;
        $address->status = $this->convertStatusToBoolean($addressData['status'] ?? true);
    }

    /**
     * Check if request has address data
     */
    private function hasAddressData($data)
    {
        $addressFields = ['address', 'address_line_1', 'city', 'zip_code', 'state', 'addresses'];
        
        foreach ($addressFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Convert status to boolean
     */
    private function convertStatusToBoolean($status)
    {
        if (is_bool($status)) {
            return $status;
        }
        
        if (is_string($status)) {
            return in_array(strtolower($status), ['enabled', 'active', 'true', '1']);
        }
        
        return (bool) $status;
    }

    /**
     * Backward compatibility method
     * @deprecated Use handleUserAddresses instead
     */
    protected function handleAddresses($entity, $addressData)
    {
        return $this->handleUserAddresses($entity, $addressData);
    }

    /**
     * Backward compatibility method
     * @deprecated Use addAddressToUser instead
     */
    protected function addAddressToEntity($entity, $requestData)
    {
        return $this->addAddressToUser($entity, $requestData);
    }
}
