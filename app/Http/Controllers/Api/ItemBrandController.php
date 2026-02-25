<?php

namespace App\Http\Controllers\Api;

use App\Models\ItemBrand;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;

class ItemBrandController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = ItemBrand::class;

    public function modifyIndex($query)
    {
        $request = request();

        return $query;
    }
}
