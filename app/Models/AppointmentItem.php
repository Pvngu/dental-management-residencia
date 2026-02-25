<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentItem extends BaseModel
{
    use HasFactory;

    protected $table = 'appointment_items';

    protected $hidden = ['id', 'appointment_id', 'item_id', 'company_id'];

    protected $appends = ['xid', 'x_appointment_id', 'x_item_id', 'x_company_id'];

    protected $filterable = ['appointment_id', 'item_id'];

    protected $fillable = [
        'appointment_id',
        'item_id',
        'quantity',
        'unit',
        'notes',
        'company_id',
    ];

    protected $hashableGetterFunctions = [
        'getXAppointmentIdAttribute' => 'appointment_id',
        'getXItemIdAttribute' => 'item_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'appointment_id' => Hash::class . ':hash',
        'item_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
