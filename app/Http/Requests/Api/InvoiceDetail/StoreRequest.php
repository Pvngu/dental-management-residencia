<?php

namespace App\Http\Requests\Api\InvoiceDetail;

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
            'invoice_id' => [
                'required',
                'string',
                new ValidForeignKey('invoices'),
            ],
            'item_id' => [
                'required',
                'string',
                new ValidForeignKey('items'),
            ],
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price_at_time' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
        ];
    }
}
