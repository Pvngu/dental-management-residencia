<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionText extends BaseModel
{
    use HasFactory;

    protected $table = 'question_texts';

    protected $primaryKey = 'response_id';

    protected $hidden = ['response_id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = [];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'sentiment' => 'decimal:3',
        'toxicity' => 'decimal:3',
        'entities_json' => 'json',
    ];

    protected $fillable = [
        'response_id',
        'raw_text',
        'clean_text',
        'lang',
        'sentiment',
        'toxicity',
        'keywords',
        'entities_json',
        'embedding_vector',
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
}
