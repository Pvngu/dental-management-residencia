<?php

namespace App\Http\Requests\Api\ZipCode;

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
            'code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'state_id' => 'required|string', // hashable ID
            'status' => 'boolean',
        ];
    }
}
