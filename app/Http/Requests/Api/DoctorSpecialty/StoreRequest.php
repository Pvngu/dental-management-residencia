<?php

namespace App\Http\Requests\Api\DoctorSpecialty;

use App\Http\Requests\Api\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['boolean']
        ];

        return $rules;
    }
}
