<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends BaseModel
{
    use HasFactory, BelongsToClinic;

    protected $table = 'invoices';

    protected $hidden = [
        'id', 'patient_id', 'created_by', 'company_id'
    ];

    protected $appends = [
        'xid', 'x_patient_id', 'x_created_by', 'x_company_id'
    ];

    protected $filterable = ['status', 'date_of_issue', 'invoice_number', 'patient_id'];

    protected $hashableGetterFunctions = [
        'getXPatientIdAttribute' => 'patient_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'patient_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id', 'id');
    }
}
