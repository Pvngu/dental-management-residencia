<?php

namespace App\Http\Controllers\Api;

use App\Models\RoomType;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Traits\SelectOptionsTraits;
use App\Http\Requests\Api\RoomType\IndexRequest;
use App\Http\Requests\Api\RoomType\StoreRequest;
use App\Http\Requests\Api\RoomType\UpdateRequest;
use App\Http\Requests\Api\RoomType\DeleteRequest;

class RoomTypeController extends ApiBaseController
{
    use CompanyTraits, SelectOptionsTraits;

    protected $model = RoomType::class;
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

            $query = $query->whereRaw('room_types.created_at >= ?', [$startDate])
                ->whereRaw('room_types.created_at <= ?', [$endDate]);
        }

        return $query;
    }
}
