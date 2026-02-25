<?php
namespace App\Http\Requests\Api\Appointment;

use App\Http\Requests\Api\BaseRequest;

class StoreRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'treatment_details' => 'nullable|string',
            'status' => 'required|string|in:pending,confirmed,cancelled',
        ];
    }
}
