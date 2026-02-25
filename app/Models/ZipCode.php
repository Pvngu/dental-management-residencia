<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ZipCode extends BaseModel
{
    use HasFactory;

    protected $table = 'zip_codes';

    protected $hidden = ['id'];

    protected $appends = ['xid'];

    protected $fillable = [
        'zip_code',
        'city',
        'state_code',
        'latitude',
        'longitude',
        'county',
        'timezone'
    ];

    protected $filterable = [
        'zip_code',
        'city',
        'state_code'
    ];

    protected $hashableGetterFunctions = [];

    protected $casts = [];
}
