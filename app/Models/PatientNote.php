<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientNote extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_notes';

    protected $hidden = ['id', 'patient_id', 'user_id', 'related_id', 'company_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_user_id', 'x_related_id', 'x_company_id'];

    protected $filterable = ['patient_id', 'user_id', 'is_highlighted'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXUserIdAttribute' => 'user_id',
        'getXRelatedIdAttribute' => 'related_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'related_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'is_private' => 'boolean',
        'is_highlighted' => 'boolean',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }
}
