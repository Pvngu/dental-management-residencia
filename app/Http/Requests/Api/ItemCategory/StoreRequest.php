<?php

namespace App\Http\Requests\Api\ItemCategory;

use Illuminate\Validation\Rule;
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_categories', 'name')->where(function ($query) {
                    return $query->where('company_id', Company()->id);
                }),
            ],
            'type' => 'nullable|string|in:general,medicine',
            'description' => 'nullable|string',
        ];
    }
}
