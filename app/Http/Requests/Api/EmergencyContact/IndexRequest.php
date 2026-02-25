<?php

namespace App\Http\Requests\Api\EmergencyContact;

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
        ];
    }
}
