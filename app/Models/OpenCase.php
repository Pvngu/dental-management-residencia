<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpenCase extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToClinic;

    protected $table = 'open_cases';

    protected $hidden = ['id', 'patient_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id'];

    protected $filterable = ['priority', 'status', 'patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic()
    {
        return $this->belongsTo(ClinicLocation::class, 'clinic_id');
    }

    public function histories()
    {
        return $this->hasMany(OpenCaseHistory::class)->with('user')->orderBy('created_at', 'desc');
    }
}
