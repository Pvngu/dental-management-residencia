<?php

namespace App\Http\Requests\Api\Medicine;

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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selling_price' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'salt_composition' => 'nullable|string|max:255',
            'side_effects' => 'nullable|string',
            'alert_quantity' => 'nullable|integer|min:0',
            'category_id' => [
                'required',
                'string',
                new ValidForeignKey('item_categories'),
            ],
            'brand_id' => [
                'nullable',
                'string',
                new ValidForeignKey('item_brands'),
            ],
        ];
    }
}