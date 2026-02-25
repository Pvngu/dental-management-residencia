<?php

namespace App\Http\Requests\Api\OpenCase;

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
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:open,in_progress,resolved,closed',
            'patient_id' => 'nullable|string',
        ];
    }
}
