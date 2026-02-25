<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorScheduleDay extends BaseModel
{
    use HasFactory;

    protected $table = 'doctor_schedule_days';

    protected $fillable = ['xid', 'id', 'schedule_id', 'doctor_id', 'day_of_week', 'available_from', 'available_to', 'status', 'company_id'];

    protected $hidden = ['id', 'doctor_id', 'schedule_id', 'company_id'];

    protected $appends = ['xid', 'x_doctor_id', 'x_schedule_id', 'x_company_id'];

    protected $filterable = ['day_of_week', 'status'];

    protected $hashableGetterFunctions = [
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXScheduleIdAttribute' => 'schedule_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $default = ['xid', 'x_doctor_id', 'x_schedule_id', 'day_of_week', 'available_from', 'available_to', 'status', 'x_company_id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class);
    }
}
