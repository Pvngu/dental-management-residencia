<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance;

use App\Http\Requests\Api\BaseRequest;

class IndexRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:255',
            'dates' => 'nullable|string',
            'status' => 'nullable|string|in:DRAFT,OPEN,CLOSED,ARCHIVED',
            'population_mode' => 'nullable|string|in:ALL,SAMPLE',
        ];
    }
}
