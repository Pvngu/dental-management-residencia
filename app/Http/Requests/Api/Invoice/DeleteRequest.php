<?php

namespace App\Http\Requests\Api\Invoice;

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
            // Add rules if needed
        ];
    }
}
