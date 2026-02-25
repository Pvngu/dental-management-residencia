<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'sales';

    protected $hidden = ['id', 'patient_id', 'user_id', 'appointment_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_user_id', 'x_appointment_id', 'x_company_id'];

    protected $filterable = ['status', 'sold_at', 'sale_number', 'patient_id', 'user_id', 'appointment_id'];

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'user_id',
        'appointment_id',
        'sale_number',
        'sold_at',
        'status',
        'subtotal',
        'tax',
        'discount',
        'discount_type',
        'total',
        'company_id',
    ];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXUserIdAttribute' => 'user_id',
        'getXAppointmentIdAttribute' => 'appointment_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'appointment_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id', 'id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
