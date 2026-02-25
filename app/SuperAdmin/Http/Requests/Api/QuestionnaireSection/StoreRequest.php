<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionnaireSection;

use App\Rules\ValidForeignKey;
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
            'template_id' => [
                'required',
                'string',
                new ValidForeignKey('questionnaire_templates'),
            ],
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'position' => 'required|integer|min:1',
            'is_required' => 'boolean',
            'skip_logic' => 'nullable|json',
        ];
    }
}
