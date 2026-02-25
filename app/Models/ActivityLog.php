<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends BaseModel
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $fillable = [
        'action',
        'entity', 
        'description',
        'user',
        'json_log',
        'company_id'
    ];

    protected $filterable = ['action', 'entity', 'description', 'patient_id'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'user' => 'array',
        'json_log' => 'array',
        'datetime' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Aplicar filtro por company_id manualmente ya que no podemos usar foreign keys en tablas particionadas
        static::addGlobalScope('company', function ($builder) {
            if (Auth::check() && Auth::user()->company_id) {
                $builder->where('company_id', Auth::user()->company_id);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
