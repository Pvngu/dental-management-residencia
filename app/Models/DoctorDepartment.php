<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorDepartment extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctor_departments';

    protected $default = ['id', 'name'];

    protected $hidden = ['id', 'company_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $appends = ['xid'];

    protected $filterable = ['name'];

    protected $hashableGetterFunctions = [];

    protected $casts = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }
}
