<?php

namespace App\Observers;

use App\Models\InventoryAdjustment;

class InventoryAdjustmentObserver
{
    public function saving(InventoryAdjustment $inventoryAdjustment)
    {
        error_log('dsdsdsdsdsdsdsd;');
        $adjustmentItems = $inventoryAdjustment->adjustmentItems()->get();
        foreach ($adjustmentItems as $adjustmentItem) {
            $item = $adjustmentItem->item;
            if ($item) {
                error_log('Current available quantity: ' . $item->available_quantity);
                error_log('Adjustment quantity: ' . $adjustmentItem->quantity_adjusted);
                $item->available_quantity += $adjustmentItem->quantity_adjusted;
                $item->save();
            }
        }
    }
    
    public function deleting(InventoryAdjustment $inventoryAdjustment)
    {
        $adjustmentItems = $inventoryAdjustment->adjustmentItems()->get();
        foreach ($adjustmentItems as $adjustmentItem) {
            $item = $adjustmentItem->item;
            if ($item) {
                $item->available_quantity -= $adjustmentItem->quantity_adjusted;
                $item->save();
            }
        }
    }
}
