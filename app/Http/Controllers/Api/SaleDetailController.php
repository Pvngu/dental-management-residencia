<?php

namespace App\Http\Controllers\Api;

use App\Models\SaleDetail;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;

class SaleDetailController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = SaleDetail::class;

    public function modifyIndex($query)
    {
        $request = request();
        
        // Filter by sale_id if provided
        if ($request->has('sale_id') && $request->sale_id != "") {
            // Use hashid decoder to get the actual sale_id
            $sale_id = $this->getIdFromHash($request->sale_id);
            if ($sale_id) {
                $query = $query->where('sale_details.sale_id', $sale_id);
            }
        }
        
        return $query;
    }
}
