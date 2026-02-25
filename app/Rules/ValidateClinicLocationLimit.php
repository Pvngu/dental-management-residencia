<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateClinicLocationLimit implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $company = company();
        
        if (!$company) {
            $fail('Unable to validate clinic location limit - no company found.');
            return;
        }

        // Check if company can add more clinic locations
        $currentCount = $company->clinic_locations_count;
        $maxAllowed = $company->max_clinic_locations;
        
        if ($currentCount >= $maxAllowed) {
            $fail("Cannot create clinic location. Your company has reached the maximum limit of {$maxAllowed} clinic location(s). Currently you have {$currentCount}/{$maxAllowed} clinic locations.");
        }
    }
}
