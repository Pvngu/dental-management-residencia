<?php

namespace App\SuperAdmin\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireInstance extends BaseModel
{
    use HasFactory;

    protected $table = 'questionnaire_instances';

    protected $hidden = ['id', 'template_id', 'created_by', 'company_id'];

    protected $appends = ['xid', 'x_template_id', 'x_created_by', 'x_company_id'];

    protected $filterable = ['name', 'status', 'population_mode', 'launch_reason'];

    protected $hashableGetterFunctions = [
        'getXTemplateIdAttribute' => 'template_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'template_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'launch_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'target_sucursales' => 'json',
        'target_roles' => 'json',
        'config_overrides' => 'json',
        'anonymize_responses' => 'boolean',
    ];

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'launch_date',
        'start_date',
        'end_date',
        'population_mode',
        'launch_reason',
        'status',
        'target_sucursales',
        'target_roles',
        'config_overrides',
        'anonymize_responses',
        'notes',
        'created_by',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(\App\SuperAdmin\Models\QuestionnaireTemplate::class, 'template_id', 'template_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(\App\SuperAdmin\Models\QuestionnaireAssignment::class, 'instance_id', 'instance_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
