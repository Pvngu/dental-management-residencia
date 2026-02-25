<?php

namespace App\SuperAdmin\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends BaseModel
{
    use HasFactory;

    protected $table = 'question_options';

    protected $hidden = ['option_id', 'question_id', 'company_id'];

    protected $appends = ['xid', 'x_question_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXQuestionIdAttribute' => 'question_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'question_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'value_numeric' => 'decimal:4',
        'response_tags' => 'json',
    ];

    protected $fillable = [
        'question_id',
        'code',
        'label',
        'value_numeric',
        'position',
        'response_tags',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(\App\SuperAdmin\Models\Question::class, 'question_id', 'question_id');
    }

    public function responseOptions(): HasMany
    {
        return $this->hasMany(\App\Models\QuestionResponseOption::class, 'option_id', 'option_id');
    }
}
