<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Classes\Common;

class ClinicUser extends Pivot
{
    protected $table = 'clinic_user';

    public $incrementing = true;

    protected $appends = ['x_role_id'];

    public function getXRoleIdAttribute()
    {
        return $this->role_id ? Common::getHashFromId($this->role_id) : null;
    }
}
