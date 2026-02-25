<?php

namespace App\Http\Requests\Api\PatientMessage;

use App\Http\Requests\Api\BaseRequest;
use App\Rules\ValidForeignKey;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => [
                'required',
                'string',
                new ValidForeignKey('patients'),
            ],
            'message' => 'required|string|max:1600',
            'phone_number' => 'required|string',
        ];
    }
}
