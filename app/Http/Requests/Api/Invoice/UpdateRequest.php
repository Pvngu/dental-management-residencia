<?php

namespace App\Http\Requests\Api\Invoice;

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
                'required',
                'string',
                new ValidForeignKey('patients'),
            ],
            'created_by' => [
                'required',
                'string',
                new ValidForeignKey('users'),
            ],
            'invoice_number' => 'required|string|max:255',
            'date_of_issue' => 'required|date',
            'payment_due_on' => 'required|date',
            'status' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'person_name' => 'nullable|string|max:255',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total_payable' => 'required|numeric',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:255',
        ];
    }
}
