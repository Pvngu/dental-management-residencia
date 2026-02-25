<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionOption;

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
            'code' => 'string|max:50',
            'label' => 'string|max:255',
            'value_numeric' => 'nullable|numeric',
            'position' => 'integer|min:1',
            'response_tags' => 'nullable|json',
        ];
    }
}
