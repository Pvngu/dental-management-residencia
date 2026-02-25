<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionOption;

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
            'question_id' => 'required|string',
            'code' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'value_numeric' => 'nullable|numeric',
            'position' => 'required|integer|min:1',
            'response_tags' => 'nullable|json',
        ];
    }
}
