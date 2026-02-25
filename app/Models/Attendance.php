<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'attendances';

    protected $hidden = ['id', 'user_id', 'company_id', 'last_updated_by'];

    protected $appends = ['xid', 'x_user_id', 'x_company_id', 'x_last_updated_by'];

    protected $filterable = ['user_id', 'status'];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
        'getXLastUpdatedByAttribute' => 'last_updated_by',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'last_updated_by' => Hash::class . ':hash',
        'clock_time' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);

        // Automatically set last_updated_by on creating
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->last_updated_by = auth()->id();
            }
        });

        // Automatically set last_updated_by on updating
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->last_updated_by = auth()->id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function lastUpdatedByUser()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}