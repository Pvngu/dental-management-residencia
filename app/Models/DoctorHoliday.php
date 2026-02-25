<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorHoliday extends BaseModel
{
    use HasFactory;

    protected $table = 'doctor_holidays';

    protected $hidden = ['id', 'doctor_id', 'company_id'];

    protected $appends = ['xid', 'x_doctor_id', 'x_company_id'];

    protected $filterable = ['holiday_type', 'status'];

    protected $hashableGetterFunctions = [
        'getXDoctorIdAttribute' => 'doctor_id',
    ];

    protected $casts = [
        'doctor_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        // 'date' => 'date:Y-m-d', --- IGNORE ---
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
