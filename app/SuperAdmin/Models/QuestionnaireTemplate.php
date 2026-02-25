<?php

namespace App\SuperAdmin\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireTemplate extends BaseModel
{
    use HasFactory;

    protected $table = 'questionnaire_templates';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['code', 'version', 'name', 'description', 'is_active', 'is_evergreen', 'target_population'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'is_active' => 'boolean',
        'is_evergreen' => 'boolean',
        'config_json' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope(new CompanyScope);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(\App\SuperAdmin\Models\QuestionnaireSection::class, 'template_id', 'id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(\App\SuperAdmin\Models\QuestionnaireInstance::class, 'template_id', 'template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
