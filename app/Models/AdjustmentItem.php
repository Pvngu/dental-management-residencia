<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdjustmentItem extends BaseModel
{
    use HasFactory;

    protected $table = 'adjustment_items';

    protected $hidden = ['id', 'adjustment_id', 'item_id', 'company_id'];

    protected $appends = ['xid', 'x_adjustment_id', 'x_item_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXAdjustmentIdAttribute' => 'adjustment_id',
        'getXItemIdAttribute' => 'item_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'adjustment_id' => Hash::class . ':hash',
        'item_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    // Relations if needed
    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'adjustment_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}