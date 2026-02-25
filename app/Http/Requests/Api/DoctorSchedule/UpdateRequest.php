<?php

namespace App\Http\Requests\Api\DoctorSchedule;

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
            'doctor_id' => 'required',
            'per_patient_time' => 'required',
            'schedule' => 'array',
            'schedule.*.from' => 'required_if:schedule.*.status,1',
            'schedule.*.to' => 'required_if:schedule.*.status,1',
        ];
    }
}
