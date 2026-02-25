<?php

namespace App\Http\Requests\Api\Visitor;

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
            'purpose' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'id_card' => 'nullable|string|max:100',
            'no_of_person' => 'required|integer|min:1',
            'date' => 'required|date',
            'in_time' => 'required|date_format:H:i',
            'out_time' => 'nullable|date_format:H:i',
            'note' => 'nullable|string',
        ];
    }
}
