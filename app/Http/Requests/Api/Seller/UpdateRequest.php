<?php

namespace App\Http\Requests\Api\Seller;

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
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|string|in:enabled,disabled',
            'password' => 'nullable|string|min:6',
            'address' => 'nullable|string',
            
            // Seller specific details
            'commission_rate' => 'nullable|string|max:255',
            
            // Profile image
            'profile_image' => 'nullable|file|mimes:jpeg,jpg,png|max:5120',
        ];
    }
}
