<?php

namespace App\Http\Requests\Api\ClinicLocation;

use App\Http\Requests\Api\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|boolean',
            'logo' => 'nullable|string',
            'addresses' => 'nullable|array',
            'addresses.*.address_line_1' => 'required_with:addresses|string|max:255',
            'addresses.*.address_line_2' => 'nullable|string|max:255',
            'addresses.*.neighborhood' => 'nullable|string|max:255',
            'addresses.*.postal_code' => 'nullable|string|max:20',
            'addresses.*.city' => 'nullable|string|max:255',
            'addresses.*.state' => 'nullable|string|max:255',
            'addresses.*.country_code' => 'nullable|string|max:3',
            'addresses.*.country_name' => 'nullable|string|max:255',
            'addresses.*.reference' => 'nullable|string|max:255',
            'addresses.*.latitude' => 'nullable|numeric|between:-90,90',
            'addresses.*.longitude' => 'nullable|numeric|between:-180,180',
            'addresses.*.contact_name' => 'nullable|string|max:255',
            'addresses.*.contact_phone' => 'nullable|string|max:20',
            'addresses.*.notes' => 'nullable|string',
            'addresses.*.status' => 'nullable|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $company = company();
            
            if (!$company) {
                $validator->errors()->add('company', 'Unable to validate clinic location limit - no company found.');
                return;
            }

            // Check if company can add more clinic locations
            $currentCount = $company->clinicLocations()->count();
            $maxAllowed = $company->max_clinic_locations;
            
            if ($currentCount >= $maxAllowed) {
                $validator->errors()->add('limit', "Cannot create clinic location. Your company has reached the maximum limit of {$maxAllowed} clinic location(s). Currently you have {$currentCount}/{$maxAllowed} clinic locations.");
            }
        });
    }
}
