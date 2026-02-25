<?php

namespace App\Http\Requests\Api\InvoiceDetail;

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
            // Add filter rules if needed
        ];
    }
}
