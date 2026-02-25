<?php

namespace App\Http\Requests\Api\PromotionTarget;

use App\Rules\ValidForeignKey;
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
            'promotion_id' => [
                'sometimes',
                'required',
                'string',
                new ValidForeignKey('promotions'),
            ],
            'target_type' => 'sometimes|required|in:product,brand,category',
            'target_id' => [
                'sometimes',
                'required',
                'string',
                // Foreign key validation will depend on target_type, handled in controller/request
            ],
        ];
    }
}
