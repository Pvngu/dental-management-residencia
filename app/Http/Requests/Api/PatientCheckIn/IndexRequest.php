<?php

namespace App\Http\Requests\Api\PatientCheckIn;

use App\Rules\ValidForeignKey;
use App\Http\Requests\Api\BaseRequest;

class IndexRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => [
                'nullable',
                'string',
                new ValidForeignKey('patients'),
            ],
            'doctor_id' => [
                'nullable',
                'string',
                new ValidForeignKey('doctors'),
            ],
            'room_id' => [
                'nullable',
                'string',
                new ValidForeignKey('rooms'),
            ],
            'status' => 'nullable|string|in:checked_in,checked_out',
            'dates' => 'nullable|string',
        ];
    }
}
