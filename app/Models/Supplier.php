<?php
namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends BaseModel
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['name', 'email', 'phone', 'status'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'contact_person',
        'notes',
        'status',
        'company_id',
    ];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'supplier_id');
    }
}
