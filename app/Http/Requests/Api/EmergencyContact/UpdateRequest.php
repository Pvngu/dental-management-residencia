<?php

namespace App\Http\Requests\Api\EmergencyContact;

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
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'relation' => 'required|string|max:255',
            'patient_id' => 'required|string',
        ];
    }
}
