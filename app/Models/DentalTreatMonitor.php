<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalTreatMonitor extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToClinic;

    protected $table = 'dental_treat_monitors';

    protected $hidden = ['id', 'patient_id', 'created_by', 'resolved_by', 'updated_by', 'deleted_by', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_created_by', 'x_resolved_by', 'x_updated_by', 'x_deleted_by', 'x_company_id'];

    protected $filterable = ['type', 'status', 'tooth_number', 'patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXResolvedByAttribute' => 'resolved_by',
        'getXUpdatedByAttribute' => 'updated_by',
        'getXDeletedByAttribute' => 'deleted_by',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'resolved_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'deleted_by' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
