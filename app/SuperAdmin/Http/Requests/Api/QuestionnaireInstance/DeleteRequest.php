<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance;

use App\Http\Requests\Api\BaseRequest;

class DeleteRequest extends BaseRequest
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
