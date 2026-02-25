<?php

namespace App\Http\Requests\Api\ActivityLog;

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
            'action' => 'required|string|max:255',
            'entity' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'user' => 'nullable|array',
            'json_log' => 'nullable|array',
        ];
    }
}
