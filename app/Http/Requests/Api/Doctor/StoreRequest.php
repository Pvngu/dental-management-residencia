<?php

namespace App\Http\Requests\Api\Doctor;

use App\Rules\ValidForeignKey;
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
            // User details
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:5',
            'gender' => 'required|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|string|in:enabled,disabled',
            'password' => 'nullable|string|min:6',
            
            // Doctor specific details
            'doctor_department_id' => [
                'required',
                'string',
                new ValidForeignKey('doctor_departments'),
            ],
            'qualification' => 'nullable|string|max:255',
            'specialist' => 'required',
            'description' => 'nullable|string',
            'practice_id' => 'nullable|string',
            'appointment_charge' => 'required|numeric',
            'designation' => 'nullable|string|max:255',
            'professional_id' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
            
            // Address details
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'city' => 'nullable|string',
            'zip_code' => 'nullable|string',
        ];
    }
}
