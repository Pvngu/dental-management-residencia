<?php

namespace App\Http\Requests\Api\RoomType;

use App\Http\Requests\Api\BaseRequest;

class DeleteRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Add any specific validation rules for delete if needed
        ];
    }
}
