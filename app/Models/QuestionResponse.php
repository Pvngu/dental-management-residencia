<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuestionResponse extends BaseModel
{
    use HasFactory;

    protected $table = 'question_responses';

    protected $primaryKey = 'response_id';

    protected $hidden = ['response_id', 'assignment_id', 'question_id', 'company_id'];

    protected $appends = ['xid', 'x_assignment_id', 'x_question_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXAssignmentIdAttribute' => 'assignment_id',
        'getXQuestionIdAttribute' => 'question_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'assignment_id' => Hash::class . ':hash',
        'question_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'value_bool' => 'boolean',
        'value_numeric' => 'decimal:4',
        'response_date' => 'datetime',
        'is_modified' => 'boolean',
        'pii_flag' => 'boolean',
        'sentiment_score' => 'decimal:2',
        'response_tags' => 'json',
    ];

    protected $fillable = [
        'assignment_id',
        'question_id',
        'value_bool',
        'value_numeric',
        'value_text',
        'value_text_clean',
        'response_date',
        'response_time_ms',
        'is_modified',
        'modification_count',
        'pii_flag',
        'language_detected',
        'sentiment_score',
        'embedding_vector',
        'response_tags',
        'version',
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

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    public function responseOptions(): HasMany
    {
        return $this->hasMany(QuestionResponseOption::class, 'response_id', 'response_id');
    }

    public function textAnalysis(): HasOne
    {
        return $this->hasOne(QuestionText::class, 'response_id', 'response_id');
    }
}
