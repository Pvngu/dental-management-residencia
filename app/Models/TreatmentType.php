<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TreatmentType extends BaseModel
{
    use HasFactory;

    protected $table = 'treatment_types';

    protected $hidden = ['id', 'company_id'];

    protected $default = ['id', 'name', 'description', 'duration_minutes', 'price', 'is_active', 'category'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['name', 'category', 'is_active'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'price' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }
}
