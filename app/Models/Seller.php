<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seller extends BaseModel
{
    use HasFactory;

    protected $table = 'sellers';

    protected $fillable = ['commission_rate', 'status', 'user_id', 'company_id'];

    protected $hidden = ['id', 'user_id', 'company_id'];

    protected $appends = ['xid', 'x_user_id', 'x_company_id'];

    protected $filterable = ['commission_rate', 'status'];

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

    public function getNameAttribute()
    {
        return $this->user ? $this->user->name . ' ' . $this->user->last_name : '';
    }

    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : '';
    }

    public function getPhoneAttribute()
    {
        return $this->user ? $this->user->phone : '';
    }
}
