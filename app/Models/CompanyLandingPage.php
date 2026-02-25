<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyLandingPage extends BaseModel
{
    use HasFactory;

    protected $table = 'company_landing_pages';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'contact_info' => 'array',
        'social_media' => 'array',
        'seo_meta' => 'array',
        'custom_sections' => 'array',
        'services_enabled' => 'boolean',
        'team_enabled' => 'boolean',
        'is_published' => 'boolean',
        'show_online_booking' => 'boolean',
        'show_phone_booking' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
