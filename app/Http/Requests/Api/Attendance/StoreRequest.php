<?php

namespace App\Http\Requests\Api\Attendance;

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
            'user_id' => [
                'required',
                'string',
                new ValidForeignKey('users'),
            ],
            'clock_time' => 'required|date',
            'status' => 'required|string|in:clock_in,clock_out,break_start,break_end',
            'notes' => 'nullable|string',
        ];
    }
}