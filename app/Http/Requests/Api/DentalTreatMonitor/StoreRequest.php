<?php

namespace App\Http\Requests\Api\DentalTreatMonitor;

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
            'x_patient_id' => [
                'required',
                'string',
                new ValidForeignKey('patients'),
            ],
            'tooth_number' => 'required|string|max:5',
            'type' => 'required|in:urgent,important,normal',
            'content' => 'required|string',
            'comment' => 'nullable|string',
        ];
    }
}
