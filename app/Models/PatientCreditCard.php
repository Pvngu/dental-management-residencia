<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class PatientCreditCard extends BaseModel
{
    use HasFactory;

    protected $table = 'patient_credit_cards';

    protected $fillable = [
        'card_number',
        'exp_date',
        'cvc',
        'card_type',
        'name_on_card',
        'street_address',
        'city',
        'state',
        'zip_code',
        'country',
        'is_default',
        'patient_id',
        'company_id',
    ];

    protected $hidden = ['id', 'patient_id', 'company_id', 'card_number_encrypted', 'cvc_encrypted'];

    protected $appends = ['xid', 'x_patient_id', 'x_company_id', 'card_number_masked'];

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

    // Accessor methods for hash IDs
    public function getXPatientIdAttribute()
    {
        return $this->patient_id ? Hashids::encode($this->patient_id) : null;
    }

    public function getXCompanyIdAttribute()
    {
        return $this->company_id ? Hashids::encode($this->company_id) : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            // Generate unique token
            $model->card_token = Str::random(32);
            
            // Encrypt sensitive data only if they exist
            if (isset($model->card_number) && $model->card_number) {
                $model->card_number_encrypted = Crypt::encryptString($model->card_number);
                // Store last four digits for display
                $model->last_four_digits = substr($model->card_number, -4);
                // Clear plain text data
                unset($model->card_number);
            }
            
            if (isset($model->cvc) && $model->cvc) {
                $model->cvc_encrypted = Crypt::encryptString($model->cvc);
                // Clear plain text data
                unset($model->cvc);
            }
        });

        static::updating(function ($model) {
            // If card number is being updated, re-encrypt it
            if (isset($model->card_number) && $model->card_number) {
                $model->card_number_encrypted = Crypt::encryptString($model->card_number);
                $model->last_four_digits = substr($model->card_number, -4);
                unset($model->card_number);
            }
            
            // If CVC is being updated, re-encrypt it
            if (isset($model->cvc) && $model->cvc) {
                $model->cvc_encrypted = Crypt::encryptString($model->cvc);
                unset($model->cvc);
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
    public function getCardNumberMaskedAttribute()
    {
        return '**** **** **** ' . $this->last_four_digits;
    }

    // Methods to decrypt sensitive data (use with caution)
    public function getDecryptedCardNumber()
    {
        return Crypt::decryptString($this->card_number_encrypted);
    }

    public function getDecryptedCvc()
    {
        return Crypt::decryptString($this->cvc_encrypted);
    }

    // Set as default card for patient
    public function setAsDefault()
    {
        // Remove default status from other cards
        static::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Set this card as default
        $this->update(['is_default' => true]);
    }
}
