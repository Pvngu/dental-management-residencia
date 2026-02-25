<?php

namespace App\Http\Requests\Api\CompanyLandingPage;

use App\Rules\ValidForeignKey;
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
            'company_id' => [
                'required',
                'string',
                new ValidForeignKey('companies'),
            ],
            'template_id' => 'nullable|string|max:100',
            'page_title' => 'nullable|string|max:255',
            'page_subtitle' => 'nullable|string|max:500',
            'page_image_url' => 'nullable|string|max:500',
            'about_title' => 'nullable|string|max:255',
            'about_text' => 'nullable|string',
            'about_image_url' => 'nullable|string|max:500',
            'services_enabled' => 'boolean',
            'services_title' => 'nullable|string|max:255',
            'services_subtitle' => 'nullable|string|max:500',
            'team_enabled' => 'boolean',
            'team_title' => 'nullable|string|max:255',
            'team_subtitle' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'show_online_booking' => 'boolean',
            'show_phone_booking' => 'boolean',
            'custom_css' => 'nullable|string',
            'contact_info' => 'nullable|array',
            'social_media' => 'nullable|array',
            'seo_meta' => 'nullable|array',
            'custom_sections' => 'nullable|array',
            'is_published' => 'boolean',
        ];
    }
}
