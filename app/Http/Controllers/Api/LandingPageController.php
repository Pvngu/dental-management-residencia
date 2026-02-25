<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Company\UpdateRequest;
use App\Models\Company;
use App\Models\CompanyLandingPage;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Http\Request;

class LandingPageController extends ApiBaseController
{
    /**
     * Get integrated landing page data for display
     * This method combines company basic settings with company_landing_pages advanced settings
     */
    public function getLandingPageData(Request $request, $slug)
    {
        $company = Company::where('slug', $slug)->first();

        if (!$company || !$company->landing_page_enabled) {
            return ApiResponse::make('Landing page not found or not enabled by super admin', 404);
        }

        $landingPage = $company->landingPage;

        // Check if the company admin has activated the landing page
        if (!$landingPage || !$landingPage->landing_page_is_active) {
            return ApiResponse::make('Landing page is not activated by the company', 404);
        }

        $landingPageData = $company->getIntegratedLandingPageData();

        return ApiResponse::make($landingPageData, 'Landing page data fetched successfully');
    }

    /**
     * Get current company landing page settings
     */
    public function getCurrentSettings()
    {
        $company = company();
        
        // Get settings from company_landing_pages table
        $landingPage = CompanyLandingPage::where('company_id', $company->id)->first();
        
        if ($landingPage) {
            $landingPageSettings = [
                'page_title' => $landingPage->page_title ?? '',
                'page_subtitle' => $landingPage->page_subtitle ?? '',
                'page_image_url' => $landingPage->page_image_url ?? '',
                'about_title' => $landingPage->about_title ?? '',
                'about_text' => $landingPage->about_text ?? '',
                'about_image_url' => $landingPage->about_image_url ?? '',
                'services_enabled' => $landingPage->services_enabled ?? true,
                'services_title' => $landingPage->services_title ?? '',
                'services_subtitle' => $landingPage->services_subtitle ?? '',
                'team_enabled' => $landingPage->team_enabled ?? true,
                'team_title' => $landingPage->team_title ?? '',
                'team_subtitle' => $landingPage->team_subtitle ?? '',
                'template_id' => $landingPage->template_id ?? 'dental-classic',
                'custom_domain' => $landingPage->custom_domain ?? '',
                'logo_url' => $landingPage->logo_url ?? '',
                'primary_color' => $landingPage->primary_color ?? '#2563eb',
                'secondary_color' => $landingPage->secondary_color ?? '#f59e0b',
                'show_online_booking' => $landingPage->show_online_booking ?? true,
                'show_phone_booking' => $landingPage->show_phone_booking ?? true,
                'custom_css' => $landingPage->custom_css ?? '',
                'contact_info' => $landingPage->contact_info ?? [],
                'social_media' => $landingPage->social_media ?? [],
                'seo_meta' => $landingPage->seo_meta ?? [],
                'custom_sections' => $landingPage->custom_sections ?? [],
                'is_published' => $landingPage->is_published ?? false,
            ];
        } else {
            // Return default values if no landing page settings exist
            $landingPageSettings = [
                'page_title' => 'Tu Sonrisa es Nuestra Prioridad',
                'page_subtitle' => 'Ofrecemos atención dental de calidad con tecnología de vanguardia',
                'page_image_url' => '',
                'about_title' => 'Acerca de Nosotros',
                'about_text' => 'Somos una clínica dental moderna comprometida con brindar el mejor cuidado dental usando tecnología avanzada y técnicas innovadoras.',
                'about_image_url' => '',
                'services_enabled' => true,
                'services_title' => 'Nuestros Servicios',
                'services_subtitle' => 'Ofrecemos una amplia gama de servicios dentales para todas las edades',
                'team_enabled' => true,
                'team_title' => 'Nuestro Equipo',
                'team_subtitle' => 'Profesionales altamente calificados a tu servicio',
                'template_id' => 'dental-classic',
                'custom_domain' => '',
                'logo_url' => '',
                'primary_color' => '#2563eb',
                'secondary_color' => '#f59e0b',
                'show_online_booking' => true,
                'show_phone_booking' => true,
                'custom_css' => '',
                'contact_info' => [],
                'social_media' => [],
                'seo_meta' => [],
                'custom_sections' => [],
                'is_published' => false,
            ];
        }

        return ApiResponse::make('Success', [
            'data' => $landingPageSettings,
            'company' => $company,
            'has_landing_page' => $landingPage !== null,
        ]);
    }

    /**
     * Update landing page settings
     */
    public function updateSettings(Request $request)
    {
        $company = company();

        // All landing page data is now stored in company_landing_pages table
        $validatedData = $request->validate([
            'page_title' => 'nullable|string|max:255',
            'page_subtitle' => 'nullable|string',
            'page_image_url' => 'nullable|string|max:500',
            'about_title' => 'nullable|string|max:255',
            'about_text' => 'nullable|string',
            'about_image_url' => 'nullable|string|max:500',
            'services_enabled' => 'nullable|boolean',
            'services_title' => 'nullable|string|max:255',
            'services_subtitle' => 'nullable|string',
            'team_enabled' => 'nullable|boolean',
            'team_title' => 'nullable|string|max:255',
            'team_subtitle' => 'nullable|string',
            'template_id' => 'nullable|string|max:100',
            'custom_domain' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'show_online_booking' => 'nullable|boolean',
            'show_phone_booking' => 'nullable|boolean',
            'custom_css' => 'nullable|string',
            'contact_info' => 'nullable|array',
            'social_media' => 'nullable|array',
            'seo_meta' => 'nullable|array',
            'custom_sections' => 'nullable|array',
            'is_published' => 'nullable|boolean',
        ]);

        // Add company_id to the data
        $validatedData['company_id'] = $company->id;
        
        // Update or create landing page settings in company_landing_pages table
        $landingPage = CompanyLandingPage::updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return ApiResponse::make('Landing page settings updated successfully', [
            'data' => $landingPage
        ]);
    }
}
