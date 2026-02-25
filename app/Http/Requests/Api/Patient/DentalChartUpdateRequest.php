<?php

namespace App\Http\Requests\Api\Patient;

use App\Http\Requests\Api\BaseRequest;

class DentalChartUpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'conditions' => ['required', 'array'],
            // Each tooth key maps to a conditions object
            'conditions.*.missing' => ['nullable', 'boolean'],
            'conditions.*.otherNotes' => ['nullable', 'string'],
            'conditions.*.pus' => ['nullable', 'array'],
            'conditions.*.pus.buccal' => ['nullable', 'boolean'],
            'conditions.*.pus.lingual' => ['nullable', 'boolean'],
            'conditions.*.pus.mesial' => ['nullable', 'boolean'],
            'conditions.*.pus.distal' => ['nullable', 'boolean'],
            'conditions.*.inflammation' => ['nullable', 'array'],
            'conditions.*.inflammation.buccal' => ['nullable', 'boolean'],
            'conditions.*.inflammation.lingual' => ['nullable', 'boolean'],
            'conditions.*.inflammation.mesial' => ['nullable', 'boolean'],
            'conditions.*.inflammation.distal' => ['nullable', 'boolean'],
        ];
    }
}
