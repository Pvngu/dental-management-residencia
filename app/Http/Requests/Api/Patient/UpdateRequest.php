<?php

namespace App\Http\Requests\Api\Patient;

use App\Http\Requests\Api\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // User details
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|string|in:enabled,disabled',
            'password' => 'nullable|string|min:6',
            'address' => 'nullable|string|max:500',
            'allergies' => 'nullable|string|max:1000',
            'blood_type' => 'nullable|string|max:10',
            'ssn' => 'nullable|string|max:50',
            // 'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Pharmacy information
            'pharmacy_name' => 'nullable|string|max:255',
            'pharmacy_phone' => 'nullable|string|max:20',
            'preferred_doctor_id' => 'nullable|string',
            
            // Insurance fields
            // 'has_secondary_insurance' => 'nullable|boolean',
            // 'primary_insurance' => 'nullable|string|max:255',
            // 'secondary_insurance' => 'nullable|string|max:255',
            
            // Primary insurance details
            'insurance_name' => 'nullable|string|max:255',
            'insured_ssn' => 'nullable|string|max:50',
            'insured_name' => 'nullable|string|max:255',
            'insured_dob' => 'nullable|date',
            'employer_name' => 'nullable|string|max:255',
            'employer_phone' => 'nullable|string|max:20',
            
            // Secondary insurance details
            'secondary_insurance_name' => 'nullable|string|max:255',
            'secondary_insured_ssn' => 'nullable|string|max:50',
            'secondary_insured_name' => 'nullable|string|max:255',
            'secondary_insured_dob' => 'nullable|date',
            'secondary_employer_name' => 'nullable|string|max:255',
            'secondary_employer_phone' => 'nullable|string|max:20',
            
            // Emergency contacts
            'emergency_contacts' => 'nullable',
            'emergency_contacts.*.name' => 'required_with:emergency_contacts|string|max:255',
            'emergency_contacts.*.phone' => 'required_with:emergency_contacts|string|max:20',
            'emergency_contacts.*.relation' => 'required_with:emergency_contacts|string|max:255',
            
            // Addresses            
            'address_line_1' => 'nullable|string|max:500',
            'address_line_2' => 'nullable|string|max:500',
            'neighborhood' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:3',
            'country_name' => 'nullable|string|max:255',
            'address_type' => 'nullable|string|in:home,work,other',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        // Decode JSON strings to arrays
        if ($this->has('emergency_contacts') && is_string($this->emergency_contacts)) {
            $this->merge([
                'emergency_contacts' => json_decode($this->emergency_contacts, true)
            ]);
        }
        
        if ($this->has('heard_from_channel') && is_string($this->heard_from_channel)) {
            $this->merge([
                'heard_from_channel' => json_decode($this->heard_from_channel, true)
            ]);
        }
        
        if ($this->has('addresses') && is_string($this->addresses)) {
            $this->merge([
                'addresses' => json_decode($this->addresses, true)
            ]);
        }
    }

    protected function withValidator($validator)
    {
        $validator->sometimes('email', 'required|email|max:255', function ($input) {
            return !empty($input->email) || !empty($input->password);
        });
    }
}
