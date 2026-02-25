<?php
namespace App\Http\Requests\Api\Appointment;

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
            'patient_id' => 'sometimes',
            'doctor_id' => 'sometimes',
            'room_id' => 'sometimes|nullable|string',
            'treatment_type_id' => 'sometimes|nullable|string',
            'appointment_date' => 'sometimes|date',
            'duration' => 'sometimes|integer|min:1',
            'treatment_details' => 'nullable|string',
            'appointment_notes' => 'nullable|string',
            'reason_visit' => 'nullable|string',
            'status' => 'sometimes|string|in:pending,confirmed,cancelled,completed,in-progress,delayed',
            'send_notification' => 'sometimes|boolean',
        ];
    }
}
