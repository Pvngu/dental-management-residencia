<?php

namespace App\Http\Requests\Api\Email;

use App\Http\Requests\Api\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|string',
            'recipient' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'nullable|in:draft,scheduled,sent,failed',
        ];
    }
}
