<?php

namespace App\Http\Requests\Api\AppointmentItem;

use App\Http\Requests\Api\BaseRequest;
use App\Rules\ValidForeignKey;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'appointment_xid' => [
                'required',
                'string',
                new ValidForeignKey('appointments'),
            ],
            'items' => 'required|array|min:1',
            'items.*.item_xid' => [
                'required',
                'string',
                new ValidForeignKey('items'),
            ],
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'appointment_xid.required' => 'Appointment is required',
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.*.item_xid.required' => 'Item is required',
            'items.*.quantity.required' => 'Quantity is required',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
        ];
    }
}
