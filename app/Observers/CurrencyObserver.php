<?php

namespace App\Observers;

use App\Models\Currency;

class CurrencyObserver
{
    public function saving(Currency $currency)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        // Only set company_id if it's not already set and if we have a valid company
        if ($company && !$company->is_global && !$currency->company_id) {
            $currency->company_id = $company->id;
        }
    }
}
