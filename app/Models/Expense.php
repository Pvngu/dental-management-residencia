<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'expenses';

    protected $hidden = ['id', 'category_id', 'company_id'];

    protected $appends = ['xid', 'x_category_id', 'x_company_id'];

    protected $filterable = ['category_id', 'expense_for', 'payment_type', 'reference_number', 'notes'];

    protected $hashableGetterFunctions = [
        'getXCategoryIdAttribute' => 'category_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'category_id' => Hash::class . ':hash',
        'amount' => 'float',
        'expense_for' => 'string',
        'payment_type' => 'string',
        'reference_number' => 'string',
        'notes' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
