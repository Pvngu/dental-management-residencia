<?php

namespace App\Http\Requests\Api\PatientCreditCard;

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
            'name_on_card' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'exp_date' => [
                'sometimes',
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            ],
            'expiry_month' => [
                'sometimes',
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])$/',
            ],
            'expiry_year' => [
                'sometimes',
                'required',
                'integer',
                'min:' . date('Y'),
                'max:' . (date('Y') + 20),
            ],
            'street_address' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'city' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'state' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
            'zip_code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
            ],
            'country' => [
                'sometimes',
                'required',
                'string',
                'size:2',
            ],
            'is_default' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages()
    {
        return [
            'exp_date.regex' => 'The expiry date must be in MM/YY format.',
            'expiry_month.regex' => 'The expiry month must be between 01-12.',
            'expiry_year.min' => 'The expiry year cannot be in the past.',
            'expiry_year.max' => 'The expiry year is too far in the future.',
            'country.size' => 'The country must be a 2-letter ISO code (e.g., US, CA, MX).',
        ];
    }
}
