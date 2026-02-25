<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends BaseModel
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'is_read',
        'is_important',
        'read_at',
        'company_id',
    ];

    protected $hidden = ['id', 'user_id', 'company_id'];

    protected $appends = ['xid', 'x_user_id', 'x_company_id'];

    protected $filterable = ['type', 'is_read', 'is_important'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'data' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
