<?php

namespace App\Http\Requests\Api\DoctorHoliday;

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
                'required',
                new ValidForeignKey('doctors'),
            ],
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'holiday_type' => 'required|in:vacation,sick_leave,personal,other',
            'status' => 'required|in:pending,approved,rejected',
        ];
    }
}