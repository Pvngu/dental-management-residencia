<?php

namespace App\Http\Requests\Api\Email;

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
            'recipient' => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'status' => 'nullable|in:draft,scheduled,sent,failed',
        ];
    }
}
