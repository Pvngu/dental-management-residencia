<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorSpecialty extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctor_specialties';

    protected $hidden = ['id', 'company_id', 'deleted_at'];

    protected $appends = ['xid', 'x_company_id'];

    protected $fillable = ['name', 'description', 'status', 'company_id'];

    protected $filterable = ['name', 'status'];

    protected $default = ['xid', 'name', 'description', 'status'];

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

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_doctor_specialty');
    }
}
