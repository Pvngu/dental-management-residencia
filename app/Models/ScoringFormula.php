<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringFormula extends BaseModel
{
    use HasFactory;

    protected $table = 'scoring_formulas';

    protected $primaryKey = 'formula_id';

    protected $hidden = ['formula_id', 'template_id', 'company_id'];

    protected $appends = ['xid', 'x_template_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXTemplateIdAttribute' => 'template_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'template_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'active' => 'boolean',
    ];

    protected $fillable = [
        'template_id',
        'code',
        'name',
        'description',
        'expression',
        'formula_type',
        'active',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireTemplate::class, 'template_id', 'template_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ScoringSnapshot::class, 'formula_id', 'formula_id');
    }
}
