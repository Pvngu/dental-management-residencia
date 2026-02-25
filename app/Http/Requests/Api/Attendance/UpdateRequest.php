<?php

namespace App\Http\Requests\Api\Attendance;

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
            'user_id' => [
                'nullable',
                'string',
                new ValidForeignKey('users'),
            ],
            'clock_time' => 'nullable|date',
            'status' => 'nullable|string|in:clock_in,clock_out,break_start,break_end',
            'notes' => 'nullable|string',
        ];
    }
}