<?php

namespace App\Http\Requests\Api\Country;

use App\Http\Requests\Api\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('country');
        
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:3|unique:countries,code,' . $id,
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'status' => 'boolean',
        ];
    }
}
