<?php

namespace App\Http\Requests\Api\PatientCreditCard;

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
            'patient_id' => [
                'nullable',
                'string',
                new ValidForeignKey('patients'),
            ],
        ];
    }
}
