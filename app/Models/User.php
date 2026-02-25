<?php

namespace App\Models;

use App\Casts\Hash;
use App\Classes\Common;
use App\Models\ClinicLocation;
use App\Models\BaseModel;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Database\Eloquent\Builder;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Notifiable, HasRoles, Authenticatable, Authorizable, HasFactory;

    protected $default = ["xid","user_type","role_type","name","last_name","email","profile_image","profile_image_url","phone","country_code","status","language","gender","date_of_birth","created_at","updated_at","role_id"];

    protected $guarded = ['id', 'company_id', 'created_at', 'updated_at'];

    protected $dates = ['last_active_on'];

    protected $hidden = ['id', 'role_id', 'password', 'remember_token'];

    protected $appends = ['xid', 'x_company_id', 'x_role_id', 'profile_image_url', 'age', 'default_address'];

    protected $filterable = ['name', 'last_name', 'user_type', 'role_type', 'email', 'status', 'phone'];

    protected $guard_name = 'web';

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXRoleIdAttribute' => 'role_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'role_id' => Hash::class . ':hash',
        'login_enabled' => 'integer',
        'is_superadmin' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope('type', function (Builder $builder) {
        //     $builder->where('users.user_type', '=', 'staff_members');
        // });
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = FacadesHash::make($value);
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }



    public function getProfileImageUrlAttribute()
    {
        // if (app()->environment('local')) {
        //     if ($this->gender == 'male') {
        //         return 'https://avatar.iran.liara.run/public/boy';
        //     } elseif ($this->gender == 'female') {
        //         return 'https://avatar.iran.liara.run/public/girl';
        //     }
        // }
        return $this->profile_image == null ? asset('images/user.png') : Common::getFileUrl(null, $this->profile_image);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function getDefaultAddressAttribute()
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function userStatuses()
    {
        return $this->hasMany(UserStatus::class);
    }

    public function currentStatus()
    {
        return $this->hasOne(UserStatus::class)->latest();
    }

    public function getAgeAttribute()
    {
        if ($this->date_of_birth) {
            return \Carbon\Carbon::parse($this->date_of_birth)->age;
        }
        return null;
    }

    public function defaultClinic()
    {
        return $this->belongsTo(ClinicLocation::class, 'default_clinic_id');
    }

    /**
     * Get all clinics this user belongs to.
     * WARNING: This assumes a Pivot table 'clinic_user' or similar logic.
     * For now, we will return a relationship that includes the default clinic
     * or all clinics if superadmin/global scope logic allows, or you should implement
     * a proper many-to-many relationship.
     *
     * Given the prompt asked to "assume" it exists, I'll implement a flexible version.
     *
     * If you don't have a `clinic_user` pivot table yet, this might need adjustment.
     * For "Single Database, Shared Schema", often valid clinics are determined by
     * Company ID.
     */
    /**
     * Get all clinics this user belongs to.
     */
    public function clinics()
    {
        return $this->belongsToMany(ClinicLocation::class, 'clinic_user', 'user_id', 'clinic_id')
            ->using(ClinicUser::class)
            ->withPivot('role_id');
    }

    /**
     * Get the role for the current clinic context.
     * 
     * @return \App\Models\Role|null
     */
    public function currentRole()
    {
        $clinicContext = app(\App\Services\ClinicContext::class);
        
        if ($clinicContext->hasClinic()) {
            $clinicId = $clinicContext->getClinic();
            
            // Find pivot for this clinic
            $assignment = $this->clinics()
                ->where('clinic_locations.id', $clinicId)
                ->first();
                
            if ($assignment && $assignment->pivot->role_id) {
                return \App\Models\Role::find($assignment->pivot->role_id);
            }
        }
        
        return null;
    }

    /**
     * Check if the user has access to a specific clinic.
     *
     * @param int $clinicId
     * @return bool
     */
    public function canAccessClinic($clinicId)
    {
        // 1. Super Admin Bypass
        if ($this->is_superadmin) {
            return true;
        }

        // 2. Global View Permission Bypass (Admin Role or Specific Permission)
        // If they can view ALL clinics, they can view THIS clinic.
        if ($this->hasRole('admin') || $this->can('clinic_locations_view_all')) {
            return true;
        }

        // 3. Check Pivot Table
        return $this->clinics()->where('clinic_locations.id', $clinicId)->exists();
    }
}
