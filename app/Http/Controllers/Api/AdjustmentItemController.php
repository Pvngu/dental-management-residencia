<?php

namespace App\Http\Controllers\Api;

use App\Models\AdjustmentItem;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;

class AdjustmentItemController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = AdjustmentItem::class;

    public function modifyIndex($query) {
        $request = request();

        return $query;
    }
}
