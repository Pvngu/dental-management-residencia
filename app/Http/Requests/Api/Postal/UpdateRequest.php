<?php

namespace App\Http\Requests\Api\Postal;

use App\Http\Requests\Api\BaseRequest;
use App\Rules\ValidForeignKey;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('date') && $this->date) {
            $this->merge([
                'date' => date('Y-m-d', strtotime($this->date)),
            ]);
        }
    }

    public function rules()
    {
        return [
            'from_title' => 'required|string|max:255',
            'to_title' => 'required|string|max:255',
            'reference_no' => 'required|string|max:255',
            'date' => 'required|date',
            'address' => 'required|string|max:255',
            'postal_type' => 'required|in:received,dispatched',
            'received_by' => [
                'nullable',
                'string',
                new ValidForeignKey('users'),
            ],
            'dispatched_by' => [
                'nullable',
                'string',
                new ValidForeignKey('users'),
            ],
            'file' => 'nullable|string|max:255',
            'patient_name' => 'nullable|string|max:255',
            'patient_id' => [
                'nullable',
                'string',
                new ValidForeignKey('users'),
            ],
            'type' => 'nullable|in:Lab Case,Letter,Package',
            'mail_type' => 'nullable|in:Lab Case,Letter,Package',
            'courier_method' => 'nullable|string|max:255',
            'email_type_id' => [
                'nullable',
                'string',
                new ValidForeignKey('email_types'),
            ],
            'tracking_number' => 'nullable|string|max:255',
            'status' => 'required|in:Pending,Shipped,In Transit,Received,Delivered',
        ];
    }
}
