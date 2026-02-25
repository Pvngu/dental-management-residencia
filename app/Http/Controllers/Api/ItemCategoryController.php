<?php

namespace App\Http\Controllers\Api;

use App\Models\ItemCategory;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\ItemCategory\IndexRequest;
use App\Http\Requests\Api\ItemCategory\StoreRequest;
use App\Http\Requests\Api\ItemCategory\UpdateRequest;
use App\Http\Requests\Api\ItemCategory\DeleteRequest;

class ItemCategoryController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = ItemCategory::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('item_categories.created_at >= ?', [$startDate])
                ->whereRaw('item_categories.created_at <= ?', [$endDate]);
        }

        return $query;
    }
}
