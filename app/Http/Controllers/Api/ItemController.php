<?php
namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\Item\IndexRequest;
use App\Http\Requests\Api\Item\StoreRequest;
use App\Http\Requests\Api\Item\UpdateRequest;
use App\Http\Requests\Api\Item\DeleteRequest;
use Illuminate\Http\Request;

class ItemController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Item::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Exclude items of type 'medicine' by default. Set `include_medicine=1` to include them.
        if (!$request->boolean('include_medicine')) {
            $query = $query->where('items.type', '<>', 'medicine');
        }

        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('items.created_at >= ?', [$startDate])
                ->whereRaw('items.created_at <= ?', [$endDate]);
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status == 'low_stock') {
            $query = $query->whereRaw('items.available_quantity > 0')
                ->whereRaw('items.available_quantity <= items.alert_quantity');
        }

        return $query;
    }

    public function getStats()
    {   
        $totalItems = Item::count();
        $availableQuantity = Item::sum('available_quantity');
        $lowStockItems = Item::whereRaw('available_quantity > 0')
            ->whereRaw('available_quantity <= alert_quantity')
            ->count();
        $outOfStockItems = Item::where('available_quantity', '<=', 0)->count();
        
        return response()->json([
            'totalItems' => $totalItems,
            'availableQuantity' => $availableQuantity,
            'lowStockAlerts' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems
        ]);
    }

    public function searchBySku($sku)
    {
        $item = Item::where('sku', $sku)
            ->with(['category', 'manufacturer', 'brand', 'supplier'])
            ->first();

        if (!$item) {
            return response()->json([
                'found' => false,
                'message' => 'Item not found',
                'sku' => $sku
            ], 200); // Return 200 instead of 404 to prevent axios error handling
        }

        return response()->json([
            'found' => true,
            'data' => $item
        ]);
    }
}
