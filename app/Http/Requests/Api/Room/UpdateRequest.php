<?php

namespace App\Http\Requests\Api\Room;

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
            'name' => 'required|string|max:50',
            'room_type_id' => [
                'required',
                'string',
                new ValidForeignKey('room_types'),
            ],
            'floor' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|string|in:Available,Occupied,Reserved,Maintenance',
            'notes' => 'nullable|string',

        ];
    }
}
