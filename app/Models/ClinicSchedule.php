<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToClinic;

class ClinicSchedule extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'clinic_schedules';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id', 'x_clinic_id'];

    protected $filterable = ['day_of_week'];

    protected $fillable = [
        'company_id',
        'clinic_id',
        'day_of_week',
        'start_time',
        'end_time'
    ];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXClinicIdAttribute' => 'clinic_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function clinic()
    {
        return $this->belongsTo(ClinicLocation::class);
    }
}
