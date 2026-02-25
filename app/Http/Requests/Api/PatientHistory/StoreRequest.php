<?php

namespace App\Http\Requests\Api\PatientHistory;

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
            'patient_id' => [
                'required',
                'string',
                new ValidForeignKey('leads'),
            ],
            'user_id' => [
                'nullable',
                'string',
                new ValidForeignKey('users'),
            ],
            'event_type' => 'required|string|max:100',
            'referenceable_id' => 'required|integer',
            'referenceable_type' => 'required|string|max:255',
            'data' => 'nullable|array',
        ];
    }
}
