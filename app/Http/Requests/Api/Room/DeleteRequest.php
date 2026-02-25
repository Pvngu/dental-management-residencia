<?php

namespace App\Http\Requests\Api\Room;

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
