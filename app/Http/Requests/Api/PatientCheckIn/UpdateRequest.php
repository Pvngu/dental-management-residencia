<?php

namespace App\Http\Requests\Api\PatientCheckIn;

use App\Rules\ValidForeignKey;
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
            'check_out_datetime' => 'nullable|date',
            'comments' => 'nullable|string|max:1000',
        ];
    }
}
