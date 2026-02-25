<?php

namespace App\Http\Requests\Api\DentalTreatMonitor;

use App\Rules\ValidForeignKey;
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
            'patient_id' => 'nullable|string',
            'tooth_number' => 'nullable|string',
            'type' => 'nullable|in:urgent,important,normal',
            'status' => 'nullable|in:active,resolved,deleted',
        ];
    }
}
