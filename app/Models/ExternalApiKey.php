<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ExternalApiKey extends BaseModel
{
    use SoftDeletes;

    protected $table = 'external_api_keys';

    protected $hidden = ['id', 'company_id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $fillable = [
        'company_id',
        'name',
        'api_key',
        'description',
        'contact_email',
        'is_active',
        'last_used_at',
        'expires_at',
        'allowed_ips',
        'allowed_domains',
        'request_count',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'allowed_ips' => 'array',
        'allowed_domains' => 'array',
        'request_count' => 'integer',
    ];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $filterable = ['name', 'is_active', 'company_id'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    /**
     * Relationship with Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Generate a new API key
     */
    public static function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Check if the key is valid (active and not expired)
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is allowed (if IP restriction is enabled)
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true; // No IP restriction
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Check if domain/origin is allowed (if domain restriction is enabled)
     */
    public function isDomainAllowed(?string $origin): bool
    {
        if (empty($this->allowed_domains)) {
            return true; // No domain restriction
        }

        if (!$origin) {
            return false; // Origin header is required when domain restriction is enabled
        }

        // Extract domain from origin (e.g., "https://example.com" -> "example.com")
        $domain = parse_url($origin, PHP_URL_HOST);

        return in_array($domain, $this->allowed_domains) || in_array($origin, $this->allowed_domains);
    }

    /**
     * Mark the key as used and increment counter
     */
    public function markAsUsed(): void
    {
        $this->update([
            'last_used_at' => now(),
            'request_count' => $this->request_count + 1,
        ]);
    }

    /**
     * Scope for active keys only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
