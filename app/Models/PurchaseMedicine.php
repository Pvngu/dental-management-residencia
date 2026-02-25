<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseMedicine extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'purchase_medicines';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['reference_no', 'delivery_date', 'payment_type', 'payment_status'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'subtotal' => 'double',
        'discount' => 'double',
        'tax' => 'double',
        'adjustments' => 'double',
        'total' => 'double',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function orderMedicines()
    {
        return $this->hasMany(OrderMedicine::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}