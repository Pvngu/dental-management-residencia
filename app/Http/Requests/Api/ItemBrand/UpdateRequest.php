<?php

namespace App\Http\Requests\Api\ItemBrand;

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
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:general,medicine',
        ];
    }
}
