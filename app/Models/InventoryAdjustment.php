<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustment extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'inventory_adjustments';

    protected $hidden = ['id', 'adjustments_reason_id', 'company_id'];

    protected $appends = ['xid', 'x_adjustments_reason_id', 'x_company_id'];

    protected $filterable = ['reference_number', 'date'];

    protected $hashableGetterFunctions = [
        'getXAdjustmentsReasonIdAttribute' => 'adjustments_reason_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'adjustments_reason_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function adjustmentItems()
    {
        return $this->hasMany(AdjustmentItem::class, 'adjustment_id');
    }

    public function adjustmentReason()
    {
        return $this->belongsTo(AdjustmentsReason::class, 'adjustments_reason_id');
    }
}
