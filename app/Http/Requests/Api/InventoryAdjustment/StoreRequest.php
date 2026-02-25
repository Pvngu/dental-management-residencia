<?php

namespace App\Http\Requests\Api\InventoryAdjustment;

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
            'reference_number' => 'required|string|max:255',
            'date' => 'required|date',
            'adjustments_reason_id' => [
                'required',
                'string',
                new ValidForeignKey('adjustments_reason'),
            ],
        ];
    }
}
