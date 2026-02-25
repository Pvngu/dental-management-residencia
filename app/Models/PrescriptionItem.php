<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends BaseModel
{
    use HasFactory;

    protected $table = 'prescription_items';

    protected $hidden = ['id', 'prescription_id', 'medicine_id', 'company_id'];

    protected $appends = ['xid', 'x_prescription_id', 'x_medicine_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXPrescriptionIdAttribute' => 'prescription_id',
        'getXMedicineIdAttribute' => 'medicine_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'prescription_id' => Hash::class . ':hash',
        'medicine_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
