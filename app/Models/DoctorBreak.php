<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorBreak extends BaseModel
{
    use HasFactory;

    protected $table = 'doctor_breaks';

    protected $hidden = ['id', 'doctor_id', 'company_id'];

    protected $appends = ['xid', 'x_doctor_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXDoctorIdAttribute' => 'doctor_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'doctor_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'every_day' => 'boolean',
        'break_from' => 'string',
        'break_to' => 'string',
        'date' => 'date:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    // Mutators
    public function setDateAttribute($value)
    {
        if ($value) {
            // Convert ISO datetime or any date format to Y-m-d
            $this->attributes['date'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
        } else {
            $this->attributes['date'] = null;
        }
    }

    // Relations
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
