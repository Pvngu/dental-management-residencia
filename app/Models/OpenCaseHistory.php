<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OpenCaseHistory extends BaseModel
{
    use HasFactory;

    protected $table = 'open_case_histories';

    protected $hidden = ['id', 'open_case_id', 'user_id', 'company_id'];

    protected $appends = ['xid', 'x_open_case_id', 'x_user_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXOpenCaseIdAttribute' => 'open_case_id',
        'getXUserIdAttribute' => 'user_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'open_case_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function openCase()
    {
        return $this->belongsTo(OpenCase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
