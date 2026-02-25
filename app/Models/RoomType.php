<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomType extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'room_types';

    protected $default = ['id', 'name', 'description', 'is_active'];

    protected $hidden = ['id', 'company_id', 'deleted_at'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['name', 'description'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
