<?php

namespace App\Traits;

trait BaseAddressTrait
{
    /**
     * Get the full formatted address
     */
    public function getFullAddressAttribute()
    {
        return self::formatAddress($this);
    }

    /**
     * Format an address from a model or array-like object.
     * Returns a nicely ordered, comma-separated single-line address.
     *
     * @param  mixed  $source  Model or array with address parts
     * @return string
     */
    public static function formatAddress($source)
    {
        $get = function ($key) use ($source) {
            if (is_array($source)) {
                return $source[$key] ?? null;
            }

            if (is_object($source)) {
                return $source->{$key} ?? null;
            }

            return null;
        };

        $line1 = trim((string) ($get('address_line_1') ?? ''));
        $addressLine2 = trim((string) ($get('address_line_2') ?? ''));
        if ($addressLine2 !== '') {
            $line1 = $line1 === '' ? $addressLine2 : $line1 . ' ' . $addressLine2;
        }

        $parts = [];
        if ($line1 !== '') {
            $parts[] = $line1;
        }

        $neighborhood = trim((string) ($get('neighborhood') ?? ''));
        $city = trim((string) ($get('city') ?? ''));
        $locality = trim(implode(', ', array_filter([$neighborhood, $city])));
        if ($locality !== '') {
            $parts[] = $locality;
        }

        $state = trim((string) ($get('state') ?? ''));
        $postal = trim((string) ($get('mail_management_code') ?? ''));
        $statePostal = trim($state . ($postal !== '' ? ' ' . $postal : ''));
        if ($statePostal !== '') {
            $parts[] = $statePostal;
        }

        $country = trim((string) ($get('country_name') ?? ''));
        if ($country !== '') {
            $parts[] = $country;
        }

        $address = implode(', ', $parts);
        return preg_replace('/\s+,\s+/', ', ', $address);
    }

    /**
     * Scope to get only default addresses
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get only active addresses
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
