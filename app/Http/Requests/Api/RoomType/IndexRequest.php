<?php

namespace App\Http\Requests\Api\RoomType;

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
            // Add any specific validation rules for index if needed
        ];
    }
}
