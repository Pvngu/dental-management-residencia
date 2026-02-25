<?php

namespace App\SuperAdmin\Http\Requests\Api\QuestionnaireTemplate;

use App\SuperAdmin\Http\Requests\Api\SuperAdminBaseRequest;

class DeleteRequest extends SuperAdminBaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // No specific rules for delete request
        ];
    }
}
