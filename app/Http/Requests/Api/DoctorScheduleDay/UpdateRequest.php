<?php

namespace App\Http\Requests\Api\DoctorScheduleDay;

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
            'doctor_id' => 'sometimes|exists:doctors,id',
            'schedule_id' => 'sometimes|exists:doctor_schedules,id',
            'day_of_week' => 'sometimes|string|max:255',
            'available_from' => 'sometimes|date_format:H:i',
            'available_to' => 'sometimes|date_format:H:i|after:available_from',
            'status' => 'sometimes|string|in:active,inactive',
        ];
    }
}
