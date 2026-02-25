<?php

namespace App\Http\Requests\Api\Notification;

use App\Http\Requests\Api\BaseRequest;

class MarkAllAsReadRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}
