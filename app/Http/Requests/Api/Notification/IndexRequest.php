<?php

namespace App\Http\Requests\Api\Notification;

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
            'type' => 'nullable|string|in:appointment_created,appointment_cancelled,followup_reminder,patient_checked_in,appointment_completed,appointment_rescheduled',
            'is_read' => 'nullable|boolean',
        ];
    }
}
