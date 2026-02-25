<?php

namespace App\Http\Requests\Api\Fax;

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
                'nullable',
                'string',
                new ValidForeignKey('patients'),
            ],
            'insurance_provider_id' => [
                'nullable',
                'string',
                new ValidForeignKey('insurance_providers'),
            ],
            'to_number' => 'nullable|string|max:255',
            'from_number' => 'nullable|string|max:255',
            'direction' => 'nullable|in:inbound,outbound',
            'status' => 'nullable|in:queued,sending,sent,failed,received',
            'file' => 'nullable|file|mimes:pdf,tiff,tif|max:10240',
            'file_name' => 'nullable|string|max:255',
            'provider_message_id' => 'nullable|string|max:255',
            'transmitted_at' => 'nullable|date',
            'error_message' => 'nullable|string',
            'meta' => 'nullable|json',
            'notes' => 'nullable|string',
        ];
    }
}
