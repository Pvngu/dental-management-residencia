<?php

namespace App\Http\Requests\Api\PatientNote;

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
            'patient_id' => [
                'required',
                'string',
                new ValidForeignKey('patients'),
            ],
            'related_type' => 'nullable|string|max:255',
            'related_id' => 'nullable|string',
            'note_type' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'is_private' => 'boolean',
            'is_highlighted' => 'boolean',
        ];
    }
}
