<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance;

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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'launch_date' => 'required|date',
            'start_date' => 'required|date|after_or_equal:launch_date',
            'end_date' => 'required|date|after:start_date',
            'population_mode' => 'required|string|in:ALL,SAMPLE',
            'launch_reason' => 'required|string|in:EVENT,PERIODIC,MANUAL',
            'status' => 'nullable|string|in:DRAFT,OPEN,CLOSED,ARCHIVED',
            'target_sucursales' => 'nullable|array',
            'target_roles' => 'nullable|array',
            'config_overrides' => 'nullable|array',
            'anonymize_responses' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ];
    }
}
