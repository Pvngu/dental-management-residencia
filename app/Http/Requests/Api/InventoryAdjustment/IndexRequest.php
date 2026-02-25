<?php

namespace App\Http\Requests\Api\InventoryAdjustment;

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
            // Add validation rules if needed
        ];
    }
}
