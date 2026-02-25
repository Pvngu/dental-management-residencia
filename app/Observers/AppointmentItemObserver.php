<?php

namespace App\Observers;

use App\Models\AppointmentItem;

class AppointmentItemObserver
{
    /**
     * Handle the AppointmentItem "deleting" event.
     * Restore inventory when appointment items are deleted
     */
    public function deleting(AppointmentItem $appointmentItem)
    {
        $item = $appointmentItem->item;
        
        if ($item && $item->type === 'goods') {
            // Restore the quantity back to inventory
            $item->available_quantity += $appointmentItem->quantity;
            $item->save();
        }
    }
}
