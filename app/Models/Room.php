<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToClinic;

class Room extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToClinic;

    protected $table = 'rooms';

    protected $default = ['id', 'name', 'floor', 'capacity', 'status', 'notes'];

    protected $hidden = ['id', 'room_type_id', 'company_id', 'deleted_at'];

    protected $appends = ['xid', 'x_room_type_id', 'x_company_id'];

    protected $filterable = ['name', 'floor', 'status'];

    protected $hashableGetterFunctions = [
        'getXRoomTypeIdAttribute' => 'room_type_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'room_type_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'capacity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'room_id');
    }

    public function currentAppointment()
    {
        return $this->hasOne(Appointment::class, 'room_id')
            ->where('status', 'in-progress')
            // ->where('appointment_date', '<=', now())
            ->orderBy('appointment_date', 'desc');
    }
}
