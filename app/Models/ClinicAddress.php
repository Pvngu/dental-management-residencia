<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BaseAddressTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClinicAddress extends BaseModel
{
    use HasFactory, BaseAddressTrait;

    protected $table = 'clinic_addresses';

    protected $fillable = [
        'clinic_location_id',
        'company_id',
        'address_line_1',
        'address_line_2', 
        'neighborhood',
        'postal_code',
        'city',
        'state',
        'country_code',
        'country_name',
        'reference',
        'latitude',
        'longitude',
        'contact_name',
        'contact_phone',
        'notes',
        'status'
    ];

    protected $default = ['id', 'address_line_1', 'address_line_2', 'neighborhood', 'postal_code', 'city', 'state', 'country_code', 'country_name', 'latitude', 'longitude'];

    protected $hidden = ['id', 'clinic_location_id', 'company_id'];

    protected $appends = ['xid', 'x_clinic_location_id', 'x_company_id', 'full_address'];

    protected $filterable = ['address_line_1', 'status'];

    protected $hashableGetterFunctions = [
        'getXClinicLocationIdAttribute' => 'clinic_location_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'clinic_location_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function clinicLocation()
    {
        return $this->belongsTo(ClinicLocation::class);
    }
}
