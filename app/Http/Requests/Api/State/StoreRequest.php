<?php

namespace App\Http\Requests\Api\State;

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
            'code' => 'nullable|string|max:10',
            'country_id' => 'required|string', // hashable ID
            'status' => 'boolean',
        ];
    }
}
