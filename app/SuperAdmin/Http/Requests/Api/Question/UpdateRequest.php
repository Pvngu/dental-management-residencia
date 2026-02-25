<?php

namespace App\SuperAdmin\Http\Requests\Api\Question;

use App\Rules\ValidForeignKey;
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
            'section_id' => [
                'required',
                'string',
                new ValidForeignKey('questionnaire_sections'),
            ],
            'code' => 'required|string|max:50',
            'prompt' => 'required|string',
            'response_type' => 'required|in:TEXT,BOOL,SINGLE_CHOICE,MULTI_CHOICE,INFO,DATE,NUMERIC,FILE',
            'position' => 'nullable|integer|min:1',
            'is_required' => 'nullable|boolean',
            'weight' => 'nullable|numeric|between:0,999.99',
            'skip_logic' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'metadata' => 'nullable|json',
        ];
    }
}
