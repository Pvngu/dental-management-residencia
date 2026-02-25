<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;
use Vinkla\Hashids\Facades\Hashids;

class PatientBankAccount extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_bank_accounts';

    protected $hidden = ['id', 'patient_id', 'company_id', 'account_number_encrypted', 'routing_number_encrypted'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id'];

    protected $filterable = ['patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            // Encrypt sensitive data
            if (isset($model->account_number) && $model->account_number) {
                $model->account_number_encrypted = Crypt::encryptString($model->account_number);
                $model->last_four_digits = substr($model->account_number, -4);
                unset($model->account_number);
            }
            
            if (isset($model->routing_number) && $model->routing_number) {
                $model->routing_number_encrypted = Crypt::encryptString($model->routing_number);
                unset($model->routing_number);
            }
        });

        static::updating(function ($model) {
            if (isset($model->account_number) && $model->account_number) {
                $model->account_number_encrypted = Crypt::encryptString($model->account_number);
                $model->last_four_digits = substr($model->account_number, -4);
                unset($model->account_number);
            }
            
            if (isset($model->routing_number) && $model->routing_number) {
                $model->routing_number_encrypted = Crypt::encryptString($model->routing_number);
                unset($model->routing_number);
            }
        });
    }

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Accessors
    public function getXPatientIdAttribute()
    {
        return $this->patient_id ? Hashids::encode($this->patient_id) : null;
    }

    public function getXCompanyIdAttribute()
    {
        return $this->company_id ? Hashids::encode($this->company_id) : null;
    }

    // Methods to decrypt sensitive data
    public function getDecryptedAccountNumber()
    {
        return $this->account_number_encrypted ? Crypt::decryptString($this->account_number_encrypted) : null;
    }

    public function getDecryptedRoutingNumber()
    {
        return $this->routing_number_encrypted ? Crypt::decryptString($this->routing_number_encrypted) : null;
    }

    // Set as default account for patient
    public function setAsDefault()
    {
        static::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        $this->update(['is_default' => true]);
    }
}
