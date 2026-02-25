<?php

namespace App\Models;

use App\Casts\Hash;
use App\Classes\Common;
use App\Models\BaseModel;
use App\Models\Currency;
use App\SuperAdmin\Models\PaymentTranscation;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Cashier\Billable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends BaseModel
{
    use Billable, Notifiable, HasFactory;

    protected $table = 'companies';

    protected $dates = ['licence_expire_on'];

    protected $default = ['xid','enable_landing_page'];

    protected $guarded = ['id', 'is_global', 'subscription_plan_id', 'payment_transcation_id', 'licence_expire_on', 'package_type', 'stripe_id', 'trial_ends_at',  'created_at', 'updated_at'];

    protected $hidden = ['id', 'currency_id', 'lang_id', 'admin_id', 'subscription_plan_id', 'payment_transcation_id', 'updated_at'];

    protected $appends = ['xid', 'x_currency_id', 'x_lang_id', 'x_admin_id', 'x_subscription_plan_id', 'x_payment_transcation_id', 'light_logo_url', 'dark_logo_url', 'small_light_logo_url', 'small_dark_logo_url', 'beep_audio_url', 'clinic_locations_count'];

    protected $hashableGetterFunctions = [
        'getXCurrencyIdAttribute' => 'currency_id',
        'getXLangIdAttribute' => 'lang_id',
        'getXAdminIdAttribute' => 'admin_id',
        'getXSubscriptionPlanIdAttribute' => 'subscription_plan_id',
        'getXPaymentTranscationIdAttribute' => 'payment_transcation_id',
    ];

    protected $casts = [
        'licence_expire_on' => 'datetime',
        'currency_id' => Hash::class . ':hash',
        'lang_id' => Hash::class . ':hash',
        'admin_id' => Hash::class . ':hash',
        'subscription_plan_id' => Hash::class . ':hash',
        'app_debug' => 'integer',
        'rtl' => 'integer',
        'auto_detect_timezone' => 'integer',
        'update_app_notification' => 'integer',
        'is_global' => 'integer',
        'verified' => 'integer',
        'white_label_completed' => 'integer',
        'grace_period' => 'integer',
    ];

    protected $filterable = ['name', 'max_clinic_locations'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('company', function (Builder $builder) {
            $builder->where('companies.is_global', 0);
        });
    }

    

    public function getLightLogoUrlAttribute()
    {
        $companyLogoPath = Common::getFolderPath('companyLogoPath');

        return $this->light_logo == null ? asset('images/light.svg') : Common::getFileUrl($companyLogoPath, $this->light_logo);
    }

    public function getDarkLogoUrlAttribute()
    {
        $companyLogoPath = Common::getFolderPath('companyLogoPath');

        return $this->dark_logo == null ? asset('images/dark.png') : Common::getFileUrl($companyLogoPath, $this->dark_logo);
    }

    public function getSmallDarkLogoUrlAttribute()
    {
        $companyLogoPath = Common::getFolderPath('companyLogoPath');

        return $this->small_dark_logo == null ? asset('images/small_dark.png') : Common::getFileUrl($companyLogoPath, $this->small_dark_logo);
    }

    public function getSmallLightLogoUrlAttribute()
    {
        $companyLogoPath = Common::getFolderPath('companyLogoPath');

        return $this->small_light_logo == null ? asset('images/small_light.svg') : Common::getFileUrl($companyLogoPath, $this->small_light_logo);
    }

    public function getBeepAudioUrlAttribute()
    {
        // $audioFilesPath = Common::getFolderPath('audioFilesPath');

        return asset('images/beep.wav');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function paymentTranscation()
    {
        return $this->belongsTo(PaymentTranscation::class);
    }

    public function admin()
    {
        return $this->belongsTo(StaffMember::class, 'admin_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(ClinicSchedule::class, 'company_id', 'id');
    }

    public function landingPage()
    {
        return $this->hasOne(CompanyLandingPage::class, 'company_id', 'id');
    }

    public function clinicLocations()
    {
        return $this->hasMany(ClinicLocation::class, 'company_id', 'id');
    }

    /**
     * Get the current number of clinic locations for this company.
     */
    public function getClinicLocationsCountAttribute()
    {
        return $this->clinicLocations()->count();
    }

    /**
     * Check if the company can add more clinic locations.
     */
    public function canAddClinicLocation()
    {
        return $this->clinic_locations_count < $this->max_clinic_locations;
    }

    /**
     * Get remaining clinic location slots.
     */
    public function getRemainingClinicSlotsAttribute()
    {
        return max(0, $this->max_clinic_locations - $this->clinic_locations_count);
    }

    /**
     * Get integrated landing page data combining basic and advanced settings
     */
    public function getIntegratedLandingPageData()
    {
        $advancedSettings = $this->landingPage;
        
        return [
            // Basic company information
            'company_name' => $this->name,
            'company_email' => $this->email,
            'company_phone' => $this->phone,
            'company_address' => $this->address,
            'company_logo' => $this->logo_url,
            
            // Page title and description (from advanced settings or defaults)
            'page_title' => $advancedSettings && $advancedSettings->page_title 
                ? $advancedSettings->page_title 
                : 'Tu Sonrisa es Nuestra Prioridad',
                
            'page_description' => $advancedSettings && $advancedSettings->page_subtitle 
                ? $advancedSettings->page_subtitle 
                : 'Ofrecemos atención dental de calidad con tecnología de vanguardia',
            
            // Hero section
            'hero_title' => $advancedSettings && $advancedSettings->page_title 
                ? $advancedSettings->page_title 
                : 'Tu Sonrisa es Nuestra Prioridad',
                
            'hero_subtitle' => $advancedSettings && $advancedSettings->page_subtitle 
                ? $advancedSettings->page_subtitle 
                : 'Ofrecemos atención dental de calidad con tecnología de vanguardia y un equipo profesional comprometido con tu salud bucal.',
                
            'hero_image' => $advancedSettings && $advancedSettings->page_image_url 
                ? $advancedSettings->page_image_url 
                : null,
            
            // About section
            'about_title' => $advancedSettings && $advancedSettings->about_title 
                ? $advancedSettings->about_title 
                : 'Acerca de Nosotros',
                
            'about_description' => $advancedSettings && $advancedSettings->about_text 
                ? $advancedSettings->about_text 
                : 'Somos una clínica dental moderna comprometida con brindar el mejor cuidado dental usando tecnología avanzada y técnicas innovadoras.',
                
            'about_image' => $advancedSettings && $advancedSettings->about_image_url 
                ? $advancedSettings->about_image_url 
                : null,
            
            // Services section
            'services_enabled' => $advancedSettings ? $advancedSettings->services_enabled : true,
            'services_title' => $advancedSettings && $advancedSettings->services_title 
                ? $advancedSettings->services_title 
                : 'Nuestros Servicios',
                
            'services_description' => $advancedSettings && $advancedSettings->services_subtitle 
                ? $advancedSettings->services_subtitle 
                : 'Ofrecemos una amplia gama de servicios dentales para todas las edades',
            
            // Contact section
            'contact_title' => 'Contáctanos',
            'contact_description' => 'Estamos aquí para ayudarte',
            'contact_phone' => $this->phone,
            'contact_email' => $this->email,
            'contact_address' => $this->address,
            
            // Social media
            'social_facebook' => null,
            'social_instagram' => null,
            'social_twitter' => null,
            'social_youtube' => null,
            
            // SEO
            'meta_title' => $this->name,
            'meta_description' => 'Ofrecemos atención dental de calidad con tecnología de vanguardia',
            'meta_keywords' => null,
            
            // Design settings (from advanced)
            'template_id' => $advancedSettings ? $advancedSettings->template_id : 'dental-classic',
            'primary_color' => $advancedSettings ? $advancedSettings->primary_color : '#2563eb',
            'secondary_color' => $advancedSettings ? $advancedSettings->secondary_color : '#f59e0b',
            'custom_css' => $advancedSettings ? $advancedSettings->custom_css : null,
            
            // Team section
            'team_enabled' => $advancedSettings ? $advancedSettings->team_enabled : true,
            'team_title' => $advancedSettings && $advancedSettings->team_title 
                ? $advancedSettings->team_title 
                : 'Nuestro Equipo',
            'team_subtitle' => $advancedSettings && $advancedSettings->team_subtitle 
                ? $advancedSettings->team_subtitle 
                : 'Profesionales altamente calificados a tu servicio',
            
            // Booking settings
            'show_online_booking' => $advancedSettings ? $advancedSettings->show_online_booking : true,
            'show_phone_booking' => $advancedSettings ? $advancedSettings->show_phone_booking : true,
            
            // Advanced JSON fields
            'contact_info' => $advancedSettings ? $advancedSettings->contact_info : null,
            'social_media' => $advancedSettings ? $advancedSettings->social_media : null,
            'seo_meta' => $advancedSettings ? $advancedSettings->seo_meta : null,
            'custom_sections' => $advancedSettings ? $advancedSettings->custom_sections : null,
            
            // Publishing status
            'is_published' => $advancedSettings ? $advancedSettings->is_published : false,
        ];
    }
}
