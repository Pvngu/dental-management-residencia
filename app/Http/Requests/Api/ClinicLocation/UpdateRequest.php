<?php

namespace App\Http\Requests\Api\ClinicLocation;

use App\Http\Requests\Api\BaseRequest;
use Illuminate\Validation\Rule;

use Vinkla\Hashids\Facades\Hashids;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $routeId = $this->route('clinic_location');
        
        $id = null;
        if ($routeId) {
            $convertedId = Hashids::decode($routeId);
            $id = $convertedId[0] ?? null;
        }

        return [
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'nullable|boolean',
            'logo' => 'nullable|string',
            'addresses' => 'nullable|array',
        ];
    }
}
