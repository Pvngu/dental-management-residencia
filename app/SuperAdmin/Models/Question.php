<?php

namespace App\SuperAdmin\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends BaseModel
{
    use HasFactory;

    protected $table = 'questions';

    protected $hidden = ['id', 'section_id', 'company_id'];

    protected $appends = ['xid', 'x_section_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXSectionIdAttribute' => 'section_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'section_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'is_required' => 'boolean',
        'weight' => 'decimal:2',
        'skip_logic' => 'json',
        'validation_rules' => 'json',
        'metadata' => 'json',
    ];

    protected $fillable = [
        'section_id',
        'code',
        'prompt',
        'response_type',
        'position',
        'is_required',
        'weight',
        'skip_logic',
        'validation_rules',
        'metadata',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(\App\SuperAdmin\Models\QuestionnaireSection::class, 'section_id', 'id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(\App\SuperAdmin\Models\QuestionOption::class, 'question_id', 'id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(\App\Models\QuestionResponse::class, 'question_id', 'id');
    }
}
