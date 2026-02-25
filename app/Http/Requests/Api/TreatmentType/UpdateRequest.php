<?php

namespace App\Http\Requests\Api\TreatmentType;

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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'category' => 'required|in:Preventive,Restorative,Surgical,Orthodontic,Diagnostic,Consultation',
        ];
    }
}
