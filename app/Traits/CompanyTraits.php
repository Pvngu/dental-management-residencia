<?php

namespace App\Traits;
trait CompanyTraits
{
    public function storing($model) 
    {
        $company = company();
        $model->company_id = $company->id;

        return $model;
    }
}