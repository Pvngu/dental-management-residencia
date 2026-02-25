<?php

namespace App\Http\Requests\Api\DoctorSpecialty;

use App\Http\Requests\Api\BaseRequest;

class AssignSpecialtiesRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'doctor_id' => ['required'],
            'specialty_ids' => ['required'],
        ];
    }
    
    /**
     * Prepare the data for validation.
     * This method allows us to handle both form-data and JSON inputs.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // If specialty_ids is a string and looks like JSON, try to decode it
        if (is_string($this->specialty_ids)) {
            $decoded = json_decode($this->specialty_ids, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['specialty_ids' => $decoded]);
            }
        }
    }
}
