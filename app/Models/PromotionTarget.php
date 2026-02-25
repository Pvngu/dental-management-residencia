<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromotionTarget extends BaseModel
{
    use HasFactory;

    protected $table = 'promotion_targets';

    protected $hidden = ['id', 'promotion_id', 'company_id', 'target_id'];

    protected $default = ['xid', 'x_promotion_id', 'x_target_id', 'target_type'];

    protected $appends = ['xid', 'x_promotion_id', 'x_company_id', 'x_target_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXPromotionIdAttribute' => 'promotion_id',
        'getXCompanyIdAttribute' => 'company_id',
        'getXTargetIdAttribute' => 'target_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'promotion_id' => Hash::class . ':hash',
        'target_id' => Hash::class . ':hash',
        'target_type' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime', 
        
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CompanyScope);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
