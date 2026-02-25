<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use App\Classes\Common;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClinicLocation extends BaseModel
{
    use HasFactory;

    protected $table = 'clinic_locations';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id', 'logo_url'];

    protected $filterable = ['name', 'email', 'status'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'status' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function getLogoUrlAttribute()
    {
        $clinicLogoPath = Common::getFolderPath('clinicLogoPath');

        return $this->logo == null ? null : Common::getFileUrl($clinicLogoPath, $this->logo);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'clinic_user', 'clinic_id', 'user_id')
            ->using(ClinicUser::class)
            ->withPivot('role_id');
    }

    public function addresses()
    {
        return $this->hasMany(ClinicAddress::class);
    }

    public function getDefaultAddressAttribute()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}
