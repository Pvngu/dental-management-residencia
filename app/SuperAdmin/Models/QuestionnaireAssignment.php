<?php

namespace App\SuperAdmin\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireAssignment extends BaseModel
{
    use HasFactory;

    protected $table = 'questionnaire_assignments';

    protected $hidden = ['assignment_id', 'instance_id', 'company_id'];

    protected $appends = ['xid', 'x_instance_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXInstanceIdAttribute' => 'instance_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'instance_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    protected $fillable = [
        'instance_id',
        'employee_id',
        'status',
        'started_at',
        'submitted_at',
        'public_folio',
        'public_token',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(\App\SuperAdmin\Models\QuestionnaireInstance::class, 'instance_id', 'instance_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(\App\Models\QuestionResponse::class, 'assignment_id', 'assignment_id');
    }

    public function scoringSnapshots(): HasMany
    {
        return $this->hasMany(\App\Models\ScoringSnapshot::class, 'assignment_id', 'assignment_id');
    }
}
