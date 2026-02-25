<?php

namespace App\Http\Requests\Api\Prescription;

use App\Http\Requests\Api\BaseRequest;
use App\Rules\ValidForeignKey;

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
                'nullable',
                'string',
                new ValidForeignKey('patients'),
            ],
            'doctor_id' => [
                'nullable',
                'string',
                new ValidForeignKey('doctors'),
            ],
            'appointment_id' => [
                'nullable',
                'string',
                new ValidForeignKey('appointments'),
            ],
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,completed,cancelled',
            'medicines' => 'nullable|array|min:1',
            'medicines.*.medicine_id' => 'nullable|string',
            'medicines.*.medicine_name' => 'nullable|string|max:255',
            'medicines.*.dosage' => 'required|string|max:255',
            'medicines.*.frequency' => 'required|string|max:255',
            'medicines.*.duration' => 'required|string|max:255',
            'medicines.*.instructions' => 'nullable|string',
        ];
    }
}
