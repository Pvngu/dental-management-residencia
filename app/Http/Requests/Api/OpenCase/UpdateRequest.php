<?php

namespace App\Http\Requests\Api\OpenCase;

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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,critical',
            'status' => 'required|string|in:open,in_progress,resolved,closed',
            'patient_id' => [
                'required',
                'string',
                new ValidForeignKey('patients'),
            ],
        ];
    }
}
