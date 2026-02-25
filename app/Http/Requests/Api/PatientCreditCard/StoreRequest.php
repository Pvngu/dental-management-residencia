<?php

namespace App\Http\Requests\Api\PatientCreditCard;

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
            'card_number' => [
                'required',
                'string',
                'regex:/^[0-9\s]{13,19}$/',
            ],
            'expiry_month' => [
                'required',
                'string',
                'regex:/^(0[1-9]|1[0-2])$/',
            ],
            'expiry_year' => [
                'required',
                'integer',
                'min:' . date('Y'),
                'max:' . (date('Y') + 20),
            ],
            'cvc' => [
                'required',
                'string',
                'regex:/^[0-9]{3,4}$/',
            ],
            'name_on_card' => [
                'required',
                'string',
                'max:255',
            ],
            'street_address' => [
                'required',
                'string',
                'max:255',
            ],
            'city' => [
                'required',
                'string',
                'max:100',
            ],
            'state' => [
                'required',
                'string',
                'max:50',
            ],
            'zip_code' => [
                'required',
                'string',
                'max:20',
            ],
            'country' => [
                'required',
                'string',
                'size:2',
            ],
            'patient_id' => [
                'required',
                'string',
                new ValidForeignKey('patients'),
            ],
        ];
    }

    public function messages()
    {
        return [
            'card_number.regex' => 'The card number must contain only numbers and be between 13-19 digits.',
            'expiry_month.regex' => 'The expiry month must be between 01-12.',
            'expiry_year.min' => 'The expiry year cannot be in the past.',
            'expiry_year.max' => 'The expiry year is too far in the future.',
            'cvc.regex' => 'The CVC must be 3-4 digits.',
            'country.size' => 'The country must be a 2-letter ISO code (e.g., US, CA, MX).',
        ];
    }
}
