<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Email extends BaseModel
{
    use HasFactory;

    protected $table = 'emails';

    protected $hidden = ['id', 'patient_id', 'company_id', 'sent_by_user_id'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id', 'x_sent_by_user_id'];

    protected $filterable = ['status', 'patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCompanyIdAttribute' => 'company_id',
        'getXSentByUserIdAttribute' => 'sent_by_user_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'patient_id' => Hash::class . ':hash',
        'sent_by_user_id' => Hash::class . ':hash',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    /**
     * Relationship with Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Relationship with Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Relationship with User who sent the email
     */
    public function sentByUser()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }
}
