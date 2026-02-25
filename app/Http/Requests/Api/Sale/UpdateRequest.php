<?php

namespace App\Http\Requests\Api\Sale;

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
            'patient_id' => [
                'nullable',
                'string',
                new ValidForeignKey('patients'),
            ],
            'user_id' => [
                'nullable',
                'string',
                new ValidForeignKey('users'),
            ],
            'appointment_id' => [
                'nullable',
                'string',
                new ValidForeignKey('appointments'),
            ],
            'sale_number' => 'required|string|max:255',
            'sold_at' => 'required|date',
            'status' => 'required|string|max:255',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
        ];
    }
}
