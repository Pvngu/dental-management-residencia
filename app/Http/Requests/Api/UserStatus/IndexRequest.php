<?php

namespace App\Http\Requests\Api\UserStatus;

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
            'user_id' => 'nullable|string',
            'status' => 'nullable|string|max:12',
        ];
    }
}