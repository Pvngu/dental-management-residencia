<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BaseAddressTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserAddress extends BaseModel
{
    use HasFactory, BaseAddressTrait;

    protected $table = 'user_addresses';

    protected $default = ['id', 'address_line_1', 'address_line_2', 'neighborhood', 'postal_code', 'city', 'state', 'country_code', 'country_name', 'address_type', 'latitude', 'longitude', 'is_default', 'status'];

    protected $hidden = ['id', 'user_id', 'company_id'];

    protected $appends = ['xid', 'x_user_id', 'x_company_id', 'full_address'];

    protected $filterable = ['address_line_1', 'address_type', 'status'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
