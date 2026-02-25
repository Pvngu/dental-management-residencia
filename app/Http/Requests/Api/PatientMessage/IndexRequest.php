<?php

namespace App\Http\Requests\Api\PatientMessage;

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
            'direction' => 'nullable|in:inbound,outbound',
            'status' => 'nullable|in:pending,sent,delivered,failed,received',
        ];
    }
}
