<?php

namespace App\Http\Requests\Api\DoctorSchedule;

use App\Classes\Common;
use Illuminate\Validation\Rule;
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
            'doctor_id' => 'required',
            'doctor_id.*' => [
                'required',
                'string',
            ],
            // 'per_patient_time' => 'required|date_format:H:i',
            'schedule' => 'array',
            'schedule.*.from' => 'required_if:schedule.*.status,1',
            'schedule.*.to' => 'required_if:schedule.*.status,1',

        ];
    }
}
