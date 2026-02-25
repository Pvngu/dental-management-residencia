<?php

namespace App\Http\Controllers\Api;

use App\Models\Room;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Room\IndexRequest;
use App\Http\Requests\Api\Room\StoreRequest;
use App\Http\Requests\Api\Room\UpdateRequest;
use App\Http\Requests\Api\Room\DeleteRequest;

class RoomController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Room::class;
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

            $query = $query->whereRaw('rooms.created_at >= ?', [$startDate])
                ->whereRaw('rooms.created_at <= ?', [$endDate]);
        }

        // Room Type Filter
        if ($request->has('room_type_id') && $request->room_type_id != "") {
            $query = $query->where('room_type_id', $request->room_type_id);
        }

        // Status Filter
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('status', $request->status);
        }

        // Floor Filter
        if ($request->has('floor') && $request->floor != "") {
            $query = $query->where('floor', $request->floor);
        }

        return $query;
    }

    public function getStats()
    {
        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 'Available')->count();
        $occupiedRooms = Room::where('status', 'Occupied')->count();
        $reservedRooms = Room::where('status', 'Reserved')->count();
        $maintenanceRooms = Room::where('status', 'Maintenance')->count();

        return response()->json([
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'occupiedRooms' => $occupiedRooms,
            'reservedRooms' => $reservedRooms,
            'maintenanceRooms' => $maintenanceRooms,
        ]);
    }
}
