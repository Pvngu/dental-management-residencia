<?php

namespace App\Http\Requests\Api\UserStatus;

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
            'user_id' => [
                'required',
                'string',
                new ValidForeignKey('users'),
            ],
            'status' => 'required|string|max:12',
            'notes' => 'nullable|string|max:500',
        ];
    }
}