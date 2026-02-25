<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringSnapshot extends BaseModel
{
    use HasFactory;

    protected $table = 'scoring_snapshots';

    protected $primaryKey = 'snapshot_id';

    protected $hidden = ['snapshot_id', 'assignment_id', 'formula_id', 'company_id'];

    protected $appends = ['xid', 'x_assignment_id', 'x_formula_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXAssignmentIdAttribute' => 'assignment_id',
        'getXFormulaIdAttribute' => 'formula_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'assignment_id' => Hash::class . ':hash',
        'formula_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'raw_score' => 'decimal:4',
        'normalized_score' => 'decimal:4',
        'requires_clinical_review' => 'boolean',
        'requires_follow_up' => 'boolean',
        'is_flagged' => 'boolean',
        'response_tags' => 'json',
        'computed_at' => 'datetime',
        'inputs_json' => 'json',
        'flags_json' => 'json',
    ];

    protected $fillable = [
        'assignment_id',
        'formula_id',
        'raw_score',
        'normalized_score',
        'result_label',
        'result_category',
        'requires_clinical_review',
        'requires_follow_up',
        'is_flagged',
        'response_tags',
        'computed_at',
        'inputs_json',
        'flags_json',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireAssignment::class, 'assignment_id', 'assignment_id');
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(ScoringFormula::class, 'formula_id', 'formula_id');
    }
}
