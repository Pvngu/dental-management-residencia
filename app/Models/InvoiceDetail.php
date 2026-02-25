<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDetail extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_details';

    protected $hidden = [
        'id', 'invoice_id', 'item_id', 'company_id'
    ];

    protected $appends = [
        'xid', 'x_invoice_id', 'x_item_id', 'x_company_id'
    ];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXInvoiceIdAttribute' => 'invoice_id',
        'getXItemIdAttribute' => 'item_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'invoice_id' => Hash::class . ':hash',
        'item_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
}
