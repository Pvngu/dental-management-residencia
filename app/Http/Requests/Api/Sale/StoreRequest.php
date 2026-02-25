<?php

namespace App\Http\Requests\Api\Sale;

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
            'sale_number' => 'nullable|string|max:255|unique:sales,sale_number',
            'sold_at' => 'required|date',
            'status' => 'required|string|max:255',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percentage,fixed',
            'total' => 'required|numeric',
            'sale_details' => 'nullable|array',
            'sale_details.*.item_id' => [
                'nullable',
                'string',
                new ValidForeignKey('items'),
            ],
            'sale_details.*.product_name' => 'nullable|string|max:255',
            'sale_details.*.quantity' => 'nullable|integer|min:1',
            'sale_details.*.price_at_time' => 'nullable|numeric|min:0',
            'sale_details.*.subtotal' => 'nullable|numeric|min:0',
            'sale_details.*.total' => 'nullable|numeric|min:0',
        ];
    }
}
