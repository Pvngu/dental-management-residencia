<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionResponseOption extends BaseModel
{
    use HasFactory;

    protected $table = 'question_response_options';

    protected $primaryKey = 'response_option_id';

    protected $hidden = ['response_option_id', 'response_id', 'option_id', 'company_id'];

    protected $appends = ['xid', 'x_response_id', 'x_option_id', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXResponseIdAttribute' => 'response_id',
        'getXOptionIdAttribute' => 'option_id',
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'response_id' => Hash::class . ':hash',
        'option_id' => Hash::class . ':hash',
        'company_id' => Hash::class . ':hash',
        'value_numeric' => 'decimal:4',
        'response_tags' => 'json',
    ];

    protected $fillable = [
        'response_id',
        'option_id',
        'option_code',
        'value_numeric',
        'response_tags',
        'company_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(QuestionResponse::class, 'response_id', 'response_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'option_id', 'option_id');
    }
}
