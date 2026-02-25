<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BaseAddressTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InsuranceProviderAddress extends BaseModel
{
    use HasFactory, BaseAddressTrait;

    protected $table = 'insurance_provider_addresses';

    protected $default = ['id', 'address_line_1', 'address_line_2', 'neighborhood', 'postal_code', 'city', 'state', 'country_code', 'country_name', 'latitude', 'longitude'];

    protected $hidden = ['id', 'insurance_provider_id', 'company_id'];

    protected $appends = ['xid', 'x_insurance_provider_id', 'x_company_id', 'full_address'];

    protected $filterable = ['address_line_1', 'status'];

    protected $hashableGetterFunctions = [
        'getXInsuranceProviderIdAttribute' => 'insurance_provider_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'insurance_provider_id' => Hash::class . ':hash',
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

    public function insuranceProvider()
    {
        return $this->belongsTo(InsuranceProvider::class);
    }
}
