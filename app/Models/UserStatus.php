<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserStatus extends BaseModel
{
    use HasFactory;

    protected $table = 'user_statuses';

    protected $hidden = ['id', 'user_id', 'company_id'];

    protected $appends = ['xid', 'x_user_id', 'x_company_id'];

    protected $filterable = ['status', 'user_id'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}