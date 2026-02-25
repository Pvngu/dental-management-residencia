<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionnaireTemplate;

use App\SuperAdmin\Http\Requests\Api\SuperAdminBaseRequest;

class UpdateRequest extends SuperAdminBaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|string|max:50',
            'version' => 'required|string|max:20',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_evergreen' => 'boolean',
            'normative_ref' => 'nullable|string|max:100',
            'target_population' => 'required|string|max:10',
            'estimated_duration' => 'nullable|integer',
            'config_json' => 'nullable|array',
        ];
    }
}
