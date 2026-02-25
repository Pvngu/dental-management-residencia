<?php

namespace App\Http\Requests\Api\DoctorScheduleDay;

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
            'doctor_id' => 'required|exists:doctors,id',
            'schedule_id' => 'required|exists:doctor_schedules,id',
            'day_of_week' => 'required|string|max:255',
            'available_from' => 'required|date_format:H:i',
            'available_to' => 'required|date_format:H:i|after:available_from',
            'status' => 'required|string|in:active,inactive',
        ];
    }
}
