<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Postal extends BaseModel
{
    use HasFactory;

    protected $table = 'postals';


    protected $hidden = ['id', 'company_id', 'patient_id', 'created_by', 'mail_type_id', 'received_by', 'sent_by', 'dispatched_by'];

    protected $appends = ['xid', 'x_company_id', 'x_patient_id', 'x_created_by', 'x_mail_type_id', 'x_received_by', 'x_sent_by', 'x_dispatched_by'];

    protected $filterable = ['postal_type', 'status', 'courier_method', 'mail_type_id', 'patient_id', 'received_by', 'sent_by', 'dispatched_by'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXPatientIdAttribute' => 'patient_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXMailTypeIdAttribute' => 'mail_type_id',
        'getXReceivedByAttribute' => 'received_by',
        'getXSentByAttribute' => 'sent_by',
        'getXDispatchedByAttribute' => 'dispatched_by',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'patient_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'mail_type_id' => Hash::class . ':hash',
        'received_by' => Hash::class . ':hash',
        'sent_by' => Hash::class . ':hash',
        'dispatched_by' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mailType()
    {
        return $this->belongsTo(MailType::class, 'mail_type_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function dispatchedBy()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }
}
