<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'medicines';

    protected $fillable = ['item_id', 'salt_composition', 'side_effects', 'company_id'];

    protected $default = ['xid', 'item_id', 'salt_composition', 'side_effects'];

    protected $hidden = ['id', 'item_id', 'company_id', 'deleted_at'];

    protected $appends = ['xid', 'x_item_id', 'x_company_id'];

    protected $filterable = ['salt_composition'];

    protected $hashableGetterFunctions = [
        'getXItemIdAttribute' => 'item_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'item_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function orderMedicines()
    {
        return $this->hasMany(OrderMedicine::class);
    }
}