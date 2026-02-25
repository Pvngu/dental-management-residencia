<?php

namespace App\Http\Requests\Api\InsuranceProvider;

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
            'name' => 'required|string|max:255',
            'payor_id' => 'nullable|string|max:255',
            'fax_number' => 'nullable|string|max:255',
            'phone_support' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
