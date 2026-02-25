<?php

namespace App\SuperAdmin\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireSection extends BaseModel
{
    use HasFactory;

    protected $table = 'questionnaire_sections';

    protected $default = ['id', 'template_id', 'code', 'name', 'description', 'instructions', 'position', 'is_required', 'skip_logic', 'created_at'];

    protected $hidden = ['id', 'template_id', 'company_id'];

    protected $appends = ['xid', 'x_template_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXTemplateIdAttribute' => 'template_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'template_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'is_required' => 'boolean',
        'skip_logic' => 'json',
    ];

    protected $fillable = [
        'template_id',
        'code',
        'name',
        'description',
        'instructions',
        'position',
        'is_required',
        'skip_logic',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(\App\SuperAdmin\Models\QuestionnaireTemplate::class, 'template_id', 'id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(\App\SuperAdmin\Models\Question::class, 'section_id', 'id');
    }
}
