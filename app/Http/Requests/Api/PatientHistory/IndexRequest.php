<?php

namespace App\Http\Requests\Api\PatientHistory;

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
            // No required fields for index
        ];
    }
}
