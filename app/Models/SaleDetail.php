<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleDetail extends BaseModel
{
    use HasFactory;

    protected $table = 'sale_details';

    protected $hidden = ['id', 'sale_id', 'item_id', 'company_id'];

    protected $appends = ['xid', 'x_sale_id', 'x_item_id', 'x_company_id'];

    protected $filterable = [];

    protected $fillable = [
        'sale_id',
        'item_id',
        'product_name',
        'quantity',
        'price_at_time',
        'subtotal',
        'total',
        'company_id',
    ];

    protected $hashableGetterFunctions = [
        'getXSaleIdAttribute' => 'sale_id',
        'getXItemIdAttribute' => 'item_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'sale_id' => Hash::class . ':hash',
        'item_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }
}
