<?php

namespace App\Traits;

use App\Models\ClinicLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ClinicContext;
use Illuminate\Support\Facades\Auth;

trait BelongsToClinic
{
    /**
     * Boot the BelongsToClinic trait.
     */
    public static function bootBelongsToClinic()
    {
        $clinicContext = app(ClinicContext::class);

        // 1. Global Scope (Read)
        static::addGlobalScope('clinic', function (Builder $builder) use ($clinicContext) {
            $user = auth('api')->user();

            // 2. Super Admin Logic
            // If we are in GLOBAL MODE, do NOT apply the filter.
            if ($clinicContext->isGlobal()) {
                return;
            }

            // Otherwise, filter by specific clinic
            if ($clinicContext->hasClinic()) {
                $cid = $clinicContext->getClinic();
                $column = defined('static::CLINIC_ID_COLUMN') ? static::CLINIC_ID_COLUMN : 'clinic_id';
                $builder->where($column, $cid);
            }
        });

        // 3. Auto-Injection (Write)
        static::creating(function ($model) use ($clinicContext) {
            // Safety Check: Cannot create records in Global Mode
            if ($clinicContext->isGlobal()) {
                abort(400, 'Cannot create records in Global View mode. Please switch to a specific clinic.');
            }

            if ($clinicContext->hasClinic()) {
                $column = defined('static::CLINIC_ID_COLUMN') ? static::CLINIC_ID_COLUMN : 'clinic_id';
                $model->{$column} = $clinicContext->getClinic();
            }
        });
    }

    /**
     * Get the clinic that owns the model.
     */
    public function clinic(): BelongsTo
    {
        $column = defined('static::CLINIC_ID_COLUMN') ? static::CLINIC_ID_COLUMN : 'clinic_id';
        return $this->belongsTo(ClinicLocation::class, $column);
    }
}
