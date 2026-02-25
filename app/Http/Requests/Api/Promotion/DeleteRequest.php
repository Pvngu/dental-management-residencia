<?php

namespace App\Http\Requests\Api\Promotion;

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
            // No rules for delete
        ];
    }
}
