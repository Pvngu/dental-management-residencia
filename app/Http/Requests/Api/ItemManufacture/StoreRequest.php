<?php

namespace App\Http\Requests\Api\ItemManufacture;

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
                Rule::unique('item_manufactures', 'name')->where(function ($query) {
                    return $query->where('company_id', Company()->id);
                }),
            ],
        ];
    }
}
