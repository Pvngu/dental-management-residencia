<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryAdjustment;
use App\Models\AdjustmentItem;
use App\Models\Item;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\InventoryAdjustment\StoreRequest;
use App\Http\Requests\Api\InventoryAdjustment\UpdateRequest;
use App\Http\Requests\Api\InventoryAdjustment\DeleteRequest;
use App\Http\Requests\Api\InventoryAdjustment\IndexRequest;
use App\Models\Company;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Examyou\RestAPI\ApiResponse;

use function Laravel\Prompts\error;

class InventoryAdjustmentController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = InventoryAdjustment::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query) {
        $request = request();

        User()->hasPermissionTo('inventory_adjustments_view');

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('inventory_adjustments.date >= ?', [$startDate])
                ->whereRaw('inventory_adjustments.date <= ?', [$endDate]);
        }

        return $query;
    }
    
    public function getItemAdjustmentHistory($itemId)
    {
        $decodedId = $this->getIdFromHash($itemId);
        
        $adjustmentItems = AdjustmentItem::with(['adjustment', 'adjustment.adjustmentReason'])
            ->where('item_id', $decodedId)
            ->get();
            
        $history = $adjustmentItems->map(function ($item) {
            return [
                'xid' => $item->xid,
                'date' => $item->adjustment->date,
                'reference_number' => $item->adjustment->reference_number,
                'reason' => $item->adjustment->adjustmentReason->name ?? '-',
                'quantity' => $item->quantity_adjusted,
                'adjustment_type' => $item->adjustment->adjustmentReason->name ?? 'NA',
            ];
        });
        
        return response()->json([
            'data' => $history
        ]);
    }
    
    public function storeAdjustment(StoreRequest $request)
    {
        User()->hasPermissionTo('inventory_adjustments_create');

        try {
            DB::beginTransaction();

            // Create the inventory adjustment record
            $adjustment = new InventoryAdjustment();
            $adjustment->reference_number = $request->reference_number;
            $adjustment->date = $request->date;
            if(!empty($request->adjustments_reason_id)) {
                $adjustment->adjustments_reason_id = $this->getIdFromHash($request->adjustments_reason_id);
            }
            $adjustment->description = $request->description;
            $adjustment->company_id = Company()->id;
            $adjustment->save();

            // Process adjustment items
            if (!empty($request->adjustment_items)) {
                $this->saveAdjustmentItems($adjustment, $request->adjustment_items);
            }

            DB::commit();

            return ApiResponse::make('Success', [
                'success' => true,
                'xid' => $adjustment->xid
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            throw new ApiException('Error', null, null, 404);
        }
    }

    public function updateAdjustment(UpdateRequest $request, $id)
    {
        User()->hasPermissionTo('inventory_adjustments_edit');

        try {
            DB::beginTransaction();

            // Find the inventory adjustment record
            $xid = $id;
            $id = $this->getIdFromHash($xid);
            $adjustment = InventoryAdjustment::find($id);

            if (!$adjustment) {
                return ApiResponse::make('Success', [
                    'success' => false,
                    'message' => 'Adjustment not found'
                ]);
            }

            // Update the adjustment record
            $adjustment->reference_number = $request->reference_number;
            $adjustment->date = $request->date;
            $adjustment->adjustments_reason_id = $this->getIdFromHash($request->adjustments_reason_id);
            $adjustment->description = $request->description;
            $adjustment->save();

            // Delete existing adjustment items
            AdjustmentItem::where('adjustment_id', $id)->delete();

            // Process adjustment items
            if (!empty($request->adjustment_items)) {
                $this->saveAdjustmentItems($adjustment, $request->adjustment_items);
            }

            DB::commit();

            return ApiResponse::make('Success', [
                'success' => true,
                'xid' => $adjustment->xid
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Error', null, null, 404);
        }
    }

    private function saveAdjustmentItems($adjustment, $adjustmentItems)
    {
        foreach ($adjustmentItems as $itemData) {
            $item = new AdjustmentItem();
            $item->adjustment_id = $adjustment->id;
            $item->item_id = $this->getIdFromHash($itemData['item_id']);
            $item->quantity_adjusted = $itemData['quantity'];
            $item->company_id = Company()->id;
            $item->save();

            // Update the item quantity in the items table
            $this->updateItemQuantity($item->item_id, $itemData['new_quantity']);
        }
    }

    private function updateItemQuantity($itemId, $newQuantity)
    {
        $item = Item::find($itemId);
        if ($item) {
            $item->available_quantity = $newQuantity;
            $item->save();
        }
    }

    public function quickAdjust(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'item_id' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:check_in,check_out',
            'adjustments_reason_id' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the item
            $itemId = $this->getIdFromHash($request->item_id);
            $item = Item::find($itemId);

            if (!$item) {
                return response()->json([
                    'message' => 'Item not found'
                ], 404);
            }

            $quantityBefore = $item->available_quantity;
            
            // Calculate new quantity
            if ($request->type === 'check_in') {
                $quantityAfter = $quantityBefore + $request->quantity;
                $quantityAdjusted = $request->quantity;
            } else {
                // check_out
                if ($quantityBefore < $request->quantity) {
                    return response()->json([
                        'message' => 'Insufficient stock'
                    ], 400);
                }
                $quantityAfter = $quantityBefore - $request->quantity;
                $quantityAdjusted = -$request->quantity;
            }

            // Generate reference number
            $referenceNumber = 'ADJ-' . date('YmdHis') . '-' . rand(1000, 9999);

            // Create inventory adjustment
            $adjustment = new InventoryAdjustment();
            $adjustment->reference_number = $referenceNumber;
            $adjustment->date = now();
            if (!empty($request->adjustments_reason_id)) {
                $adjustment->adjustments_reason_id = $this->getIdFromHash($request->adjustments_reason_id);
            }
            $adjustment->description = $request->description ?? ($request->type === 'check_in' ? 'Quick Check In' : 'Quick Check Out');
            $adjustment->company_id = Company()->id;
            $adjustment->save();

            // Create adjustment item
            $adjustmentItem = new AdjustmentItem();
            $adjustmentItem->adjustment_id = $adjustment->id;
            $adjustmentItem->item_id = $itemId;
            $adjustmentItem->quantity_before = $quantityBefore;
            $adjustmentItem->quantity_after = $quantityAfter;
            $adjustmentItem->quantity_adjusted = $quantityAdjusted;
            $adjustmentItem->company_id = Company()->id;
            $adjustmentItem->save();

            // Update item quantity
            $item->available_quantity = $quantityAfter;
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->type === 'check_in' ? 'Stock checked in successfully' : 'Stock checked out successfully',
                'data' => [
                    'xid' => $adjustment->xid,
                    'item' => $item,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            error_log($e->getMessage());
            return response()->json([
                'message' => 'Error processing adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
