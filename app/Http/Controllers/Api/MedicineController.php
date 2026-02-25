<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Medicine\DeleteRequest;
use App\Http\Requests\Api\Medicine\IndexRequest;
use App\Http\Requests\Api\Medicine\StoreRequest;
use App\Http\Requests\Api\Medicine\UpdateRequest;
use App\Models\Medicine;
use App\Models\Item;
use App\Models\OrderMedicine;
use App\Models\PurchaseMedicineItem;
use App\Traits\CompanyTraits;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Examyou\RestAPI\ApiResponse;
use Vinkla\Hashids\Facades\Hashids;
use Examyou\RestAPI\Exceptions\ResourceNotFoundException;

class MedicineController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = Medicine::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    /**
     * Search medicines by name - returns flattened structure with item fields
     */
    public function search(Request $request)
    {
        $company = company();
        $searchTerm = $request->input('search', '');
        $limit = $request->input('limit', 10);
        
        if (empty($searchTerm)) {
            return response()->json(['data' => []]);
        }
        
        // Search medicines by item name
        $medicines = Medicine::where('company_id', $company->id)
            ->whereHas('item', function($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            })
            ->with(['item:id,name,description,available_quantity,sku'])
            ->limit($limit)
            ->get();
        
        // Transform to flatten item properties
        $results = $medicines->map(function($medicine) {
            return [
                'xid' => $medicine->xid,
                'name' => $medicine->item->name ?? '',
                'description' => $medicine->item->description ?? '',
                'salt_composition' => $medicine->salt_composition,
                'side_effects' => $medicine->side_effects,
                'available_quantity' => $medicine->item->available_quantity ?? 0,
                'sku' => $medicine->item->sku ?? ''
            ];
        });
        
        return response()->json(['data' => $results]);
    }

    public function searchBySku($sku)
    {
        // Search for medicine by the SKU in the items table
        $medicine = Medicine::whereHas('item', function($query) use ($sku) {
                $query->where('sku', $sku);
            })
            ->with(['item.category', 'item.brand'])
            ->first();

        if (!$medicine) {
            return response()->json([
                'found' => false,
                'message' => 'Medicine not found',
                'sku' => $sku
            ], 200);
        }

        return response()->json([
            'found' => true,
            'data' => $medicine
        ]);
    }

    public function modifyIndex($query)
    {
        $request = request();

        // Filter by stock status
        if ($request->has('stock_status')) {
            if ($request->stock_status == 'low_stock') {
                // Low stock: available quantity > 0 and <= alert quantity
                $query = $query->whereHas('item', function($q) {
                    $q->whereRaw('available_quantity > 0')
                        ->whereRaw('available_quantity <= alert_quantity');
                });
            } elseif ($request->stock_status == 'expired') {
                // Get medicines that have expired batches
                $query = $query->whereHas('orderMedicines', function($q) {
                    $q->where('expiry_date', '<', now()->format('Y-m-d'))
                        ->where('quantity', '>', 0);
                });
            }
        }

        // Handle search - search only by item name
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query = $query->whereHas('item', function($itemQuery) use ($searchTerm) {
                $itemQuery->where('name', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query;
    }

    public function store()
    {
        \DB::beginTransaction();

        $this->validate();

        // Create new object
        $object = new $this->model();
        
        // Exclude 'item' from fill to prevent ApiModel from nullifying the relation
        // because the request payload has "item": null
        $object->fill(request()->except('item'));

        // Run hook if exists
        if (method_exists($this, 'storing')) {
                $object = call_user_func([$this, 'storing'], $object);
        }

        $object->save();

        $meta = $this->getMetaData(true);

        \DB::commit();

        if (method_exists($this, 'stored')) {
                call_user_func([$this, 'stored'], $object);
        }

        return ApiResponse::make("Resource created successfully", ["xid" => $object->xid], $meta);
    }

    public function update(...$args)
    {
        \DB::beginTransaction();

        // Geting id from hashids
        $xid = last(func_get_args());
        $convertedId = Hashids::decode($xid);
        $id = $convertedId[0];

        $this->validate();

        // Get object for update
        $this->setQuery(call_user_func($this->model . "::query"));
        $this->setQuery($this->modifyUpdate($this->getQuery()));

        /** @var ApiModel $object */
        $object = $this->getQuery()->find($id);

        if (!$object) {
                throw new ResourceNotFoundException();
        }

        $object->fill(request()->except('item'));

        if (method_exists($this, 'updating')) {
                $object = call_user_func([$this, 'updating'], $object);
        }

        $object->save();

        $meta = $this->getMetaData(true);

        \DB::commit();

        if (method_exists($this, 'updated')) {
                call_user_func([$this, 'updated'], $object);
        }

        return ApiResponse::make("Resource updated successfully", ["xid" => $object->xid], $meta);
    }

    public function storing(Medicine $medicine)
    {
        $request = request();
        
        // Create item first
        $company = company();
        $item = new Item();
        $item->name = $request->name;
        $item->description = $request->description ?? '';
        
        // Decode hashed IDs to actual database IDs
        $item->category_id = $this->getIdFromHash($request->category_id);
        $item->brand_id = $request->brand_id ? $this->getIdFromHash($request->brand_id) : null;

        $item->type = 'medicine';
        $item->is_sellable = true;
        $item->sale_price = $request->selling_price;
        $item->is_purchasable = true;
        $item->cost_price = $request->buying_price;
        $item->alert_quantity = $request->alert_quantity ?? 5;
        $item->sku = $request->sku ?? null;
        $item->company_id = $company->id;
        $item->save();
        $item->refresh();

        if (!$item->id) {
             throw new \Exception("Failed to create item for Medicine");
        }

        // Ensure category and brand are marked as medicine type
        if ($item->category_id) {
            \DB::table('item_categories')->where('id', $item->category_id)->update(['type' => 'medicine']);
        }
        if ($item->brand_id) {
            \DB::table('item_brands')->where('id', $item->brand_id)->update(['type' => 'medicine']);
        }

        // Link medicine to item
        $medicine->item_id = $item->id;
        $medicine->salt_composition = $request->salt_composition;
        $medicine->side_effects = $request->side_effects;
        $medicine->company_id = $company->id;

        return $medicine;
    }

    public function updating(Medicine $medicine)
    {
        $request = request();
        
        // Update the linked item
        $item = $medicine->item;
        if ($item) {
            $item->name = $request->name;
            $item->description = $request->description ?? '';
            
            // Decode hashed IDs to actual database IDs
            $item->category_id = $this->getIdFromHash($request->category_id);
            $item->brand_id = $request->brand_id ? $this->getIdFromHash($request->brand_id) : null;
            
            $item->sale_price = $request->selling_price;

            $item->cost_price = $request->buying_price;
            $item->alert_quantity = $request->alert_quantity ?? 5;
            $item->sku = $request->sku ?? null;
            $item->save();
        }

        // Update medicine-specific fields
        $medicine->salt_composition = $request->salt_composition;
        $medicine->side_effects = $request->side_effects;

        return $medicine;
    }

    public function getStats(Request $request)
    {
        $company = company();
        
        // Get medicines for this company
        $medicinesQuery = Medicine::where('company_id', $company->id)
            ->with('item');
        
        // Total available quantity from items
        $totalAvailableQuantity = Item::whereHas('medicine', function($q) use ($company) {
            $q->where('company_id', $company->id);
        })->sum('available_quantity');
        
        // Out of stock medicines
        $outOfStockMedicines = Item::whereHas('medicine', function($q) use ($company) {
            $q->where('company_id', $company->id);
        })->where('available_quantity', '<=', 0)->count();
        
        // Most sold medicine - Find which medicine has been ordered most frequently
        $mostSoldMedicine = PurchaseMedicineItem::from('order_medicines')
            ->join('medicines', 'medicines.id', '=', 'order_medicines.medicine_id')
            ->join('items', 'items.id', '=', 'medicines.item_id')
            ->where('medicines.company_id', $company->id)
            ->select('items.name', DB::raw('SUM(order_medicines.quantity) as total_quantity'))
            ->groupBy('items.id', 'items.name')
            ->orderBy('total_quantity', 'desc')
            ->first();
        
        // Expiry alerts - Find medicine items expiring in the next 30 days
        $thirtyDaysFromNow = now()->addDays(30)->format('Y-m-d');
        $expiryAlerts = PurchaseMedicineItem::from('order_medicines')
            ->join('medicines', 'medicines.id', '=', 'order_medicines.medicine_id')
            ->where('medicines.company_id', $company->id)
            ->where('order_medicines.expiry_date', '<=', $thirtyDaysFromNow)
            ->where('order_medicines.expiry_date', '>=', now()->format('Y-m-d'))
            ->count();
        
        return response()->json([
            'totalAvailableQuantity' => $totalAvailableQuantity,
            'outOfStockMedicines' => $outOfStockMedicines,
            'mostSoldMedicine' => $mostSoldMedicine ? $mostSoldMedicine->name : '-',
            'expiryAlerts' => $expiryAlerts
        ]);
    }

    /**
     * Get batch details (order_medicines) for a specific medicine
     */
    public function getBatches(Request $request, $xid)
    {
        $company = company();
        
        // Decode the medicine hash ID
        $convertedId = Hashids::decode($xid);
        if (empty($convertedId)) {
            return response()->json(['error' => 'Invalid medicine ID'], 400);
        }
        $medicineId = $convertedId[0];
        
        // Get all order_medicines (batches) for this medicine
        // Using FEFO (First Expired, First Out) - ordered by expiry_date ascending
        // This ensures batches with earliest expiration dates are prioritized when dispensing
        $batches = OrderMedicine::where('medicine_id', $medicineId)
            ->where('company_id', $company->id)
            ->with('purchaseMedicine:id,reference_no,created_at')
            ->select([
                'id',
                'lot_no',
                'quantity',
                'expiry_date',
                'purchase_medicine_id',
                'created_at'
            ])
            ->orderBy('expiry_date', 'asc') // FEFO: First Expired, First Out
            ->get()
            ->map(function ($batch) {
                $today = Carbon::today();
                $expiryDate = Carbon::parse($batch->expiry_date);
                
                // Determine status based on expiry date
                $status = 'healthy';
                if ($expiryDate->isPast()) {
                    $status = 'expired';
                } elseif ($expiryDate->diffInDays($today) <= 30) {
                    $status = 'expiring_soon';
                }
                
                return [
                    'xid' => Hashids::encode($batch->id),
                    'lot_no' => $batch->lot_no,
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date,
                    'formatted_expiry_date' => $expiryDate->format('M d, Y'),
                    'status' => $status,
                    'received_date' => $batch->created_at ? $batch->created_at->format('M d, Y') : null,
                    'reference_no' => $batch->purchaseMedicine ? $batch->purchaseMedicine->reference_no : null,
                ];
            });
        
        $activeBatches = $batches->filter(function ($batch) {
            return $batch['status'] !== 'expired' && $batch['quantity'] > 0;
        })->count();
        
        return response()->json([
            'batches' => $batches,
            'active_batches' => $activeBatches,
            'total_batches' => $batches->count(),
        ]);
    }
}