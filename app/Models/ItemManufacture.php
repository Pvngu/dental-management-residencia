<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemManufacture extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'item_manufactures';
    protected $default = ['id', 'name', 'is_active'];

    protected $hidden = ['id', 'company_id', 'deleted_at'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function getDefaultAttribute()
    {
        return $this->default;
    }
}
