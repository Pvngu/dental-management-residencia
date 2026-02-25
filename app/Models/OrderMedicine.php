<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderMedicine extends BaseModel
{
    use HasFactory;

    protected $table = 'order_medicines';

    protected $hidden = ['id', 'purchase_medicine_id', 'medicine_id', 'company_id'];

    protected $appends = ['xid', 'x_purchase_medicine_id', 'x_medicine_id', 'x_company_id', 'formatted_expiry_date'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXPurchaseMedicineIdAttribute' => 'purchase_medicine_id',
        'getXMedicineIdAttribute' => 'medicine_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'purchase_medicine_id' => Hash::class . ':hash',
        'medicine_id' => Hash::class . ':hash',
        'quantity' => 'integer',
        'rate' => 'double',
        'amount' => 'double',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function getFormattedExpiryDateAttribute()
    {
        return date('d M, Y', strtotime($this->expiry_date));
    }

    public function purchaseMedicine()
    {
        return $this->belongsTo(PurchaseMedicine::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}