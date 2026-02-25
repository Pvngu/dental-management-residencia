<?php

namespace App\Http\Requests\Api\DoctorBreak;

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
            'doctor_id' => [
                'required',
                'array',
            ],
            'doctor_id.*' => [
                'required',
                'string',
                new ValidForeignKey('doctors'),
            ],
            'break_from' => 'required',
            'break_to' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (request('break_to') && $value <= request('break_from')) {
                        $fail(__('The break to time must be greater than break from time.'));
                    }
                },
            ],
            'every_day' => 'required|boolean',
            'date' => 'nullable|required_if:every_day,0|date',
        ];
    }
}
