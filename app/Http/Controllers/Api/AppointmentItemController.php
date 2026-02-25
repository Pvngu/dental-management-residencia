<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Models\AppointmentItem;
use App\Models\Item;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\Http\Requests\Api\AppointmentItem\StoreRequest;
use App\Http\Requests\Api\AppointmentItem\UpdateRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

class AppointmentItemController extends ApiBaseController
{
    use CompanyTraits;

    /**
     * Store appointment items
     * POST /api/appointments/items
     */
    public function storeItems(StoreRequest $request)
    {
        try {
            DB::beginTransaction();

            // Decode appointment XID
            $appointmentId = $this->getIdFromHash($request->appointment_xid);
            
            // Validate appointment exists and belongs to company
            $appointment = Appointment::find($appointmentId);
            
            if (!$appointment) {
                throw new ApiException('Appointment not found', null, 404);
            }

            // Check if appointment status allows modification
            if (in_array($appointment->status, ['cancelled', 'completed'])) {
                throw new ApiException('Cannot modify items for cancelled or completed appointments', null, 422);
            }

            // Validate all items exist and have sufficient quantity
            $itemsData = [];
            foreach ($request->items as $itemData) {
                $itemId = $this->getIdFromHash($itemData['item_xid']);
                $item = Item::find($itemId);
                
                if (!$item) {
                    throw new ApiException("Item not found: {$itemData['item_xid']}", null, 404);
                }

                // Check if item is goods type and has sufficient quantity
                if ($item->type === 'goods' && $item->available_quantity < $itemData['quantity']) {
                    throw new ApiException("Insufficient quantity for item: {$item->name}. Available: {$item->available_quantity}, Required: {$itemData['quantity']}", null, 422);
                }

                $itemsData[] = [
                    'item_id' => $itemId,
                    'item' => $item,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'] ?? $item->unit,
                ];
            }

            // Delete existing appointment items and restore their quantities
            $existingItems = AppointmentItem::where('appointment_id', $appointmentId)->get();
            foreach ($existingItems as $existingItem) {
                $item = $existingItem->item;
                if ($item && $item->type === 'goods') {
                    $item->available_quantity += $existingItem->quantity;
                    $item->save();
                }
                $existingItem->delete();
            }

            // Create new appointment items and reduce inventory
            $createdItems = [];
            foreach ($itemsData as $itemData) {
                $appointmentItem = AppointmentItem::create([
                    'appointment_id' => $appointmentId,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'notes' => $request->notes,
                    'company_id' => company()->id,
                ]);

                // Reduce inventory for goods
                if ($itemData['item']->type === 'goods') {
                    $itemData['item']->available_quantity -= $itemData['quantity'];
                    $itemData['item']->save();
                }

                $createdItems[] = $appointmentItem;
            }

            DB::commit();

            // Load relationships for response
            $appointment->load(['appointmentItems.item.category']);

            return ApiResponse::make('Items saved successfully', [
                'appointment_xid' => $request->appointment_xid,
                'items' => $appointment->appointmentItems->map(function ($item) {
                    return [
                        'xid' => $item->xid,
                        'item_xid' => $item->item->xid,
                        'item_name' => $item->item->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'item' => [
                            'xid' => $item->item->xid,
                            'name' => $item->item->name,
                            'sku' => $item->item->sku,
                            'category' => $item->item->category,
                            'available_quantity' => $item->item->available_quantity,
                        ],
                    ];
                }),
                'notes' => $request->notes,
            ]);

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed to save items: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update appointment items
     * PUT /api/appointments/{xid}/items
     */
    public function updateItems($xid, UpdateRequest $request)
    {
        try {
            DB::beginTransaction();

            // Decode appointment XID
            $appointmentId = $this->getIdFromHash($xid);
            
            // Validate appointment exists and belongs to company
            $appointment = Appointment::find($appointmentId);
            
            if (!$appointment) {
                throw new ApiException('Appointment not found', null, 404);
            }

            // Check if appointment status allows modification
            if (in_array($appointment->status, ['cancelled', 'completed'])) {
                throw new ApiException('Cannot modify items for cancelled or completed appointments', null, 422);
            }

            // Validate all items exist and have sufficient quantity
            $itemsData = [];
            foreach ($request->items as $itemData) {
                $itemId = $this->getIdFromHash($itemData['item_xid']);
                $item = Item::find($itemId);
                
                if (!$item) {
                    throw new ApiException("Item not found: {$itemData['item_xid']}", null, 404);
                }

                // Check available quantity (considering we'll restore previous usage)
                if ($item->type === 'goods') {
                    // Get existing item quantity if it exists
                    $existingItem = AppointmentItem::where('appointment_id', $appointmentId)
                        ->where('item_id', $itemId)
                        ->first();
                    
                    $availableAfterRestore = $item->available_quantity + ($existingItem ? $existingItem->quantity : 0);
                    
                    if ($availableAfterRestore < $itemData['quantity']) {
                        throw new ApiException("Insufficient quantity for item: {$item->name}. Available: {$availableAfterRestore}, Required: {$itemData['quantity']}", null, 422);
                    }
                }

                $itemsData[] = [
                    'item_id' => $itemId,
                    'item' => $item,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'] ?? $item->unit,
                ];
            }

            // Delete existing appointment items and restore their quantities
            $existingItems = AppointmentItem::where('appointment_id', $appointmentId)->get();
            foreach ($existingItems as $existingItem) {
                $item = $existingItem->item;
                if ($item && $item->type === 'goods') {
                    $item->available_quantity += $existingItem->quantity;
                    $item->save();
                }
                $existingItem->delete();
            }

            // Create new appointment items and reduce inventory
            $createdItems = [];
            foreach ($itemsData as $itemData) {
                $appointmentItem = AppointmentItem::create([
                    'appointment_id' => $appointmentId,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'notes' => $request->notes,
                    'company_id' => company()->id,
                ]);

                // Reduce inventory for goods
                if ($itemData['item']->type === 'goods') {
                    $itemData['item']->available_quantity -= $itemData['quantity'];
                    $itemData['item']->save();
                }

                $createdItems[] = $appointmentItem;
            }

            DB::commit();

            // Load relationships for response
            $appointment->load(['appointmentItems.item.category']);

            return ApiResponse::make('Items updated successfully', [
                'appointment_xid' => $xid,
                'items' => $appointment->appointmentItems->map(function ($item) {
                    return [
                        'xid' => $item->xid,
                        'item_xid' => $item->item->xid,
                        'item_name' => $item->item->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'item' => [
                            'xid' => $item->item->xid,
                            'name' => $item->item->name,
                            'sku' => $item->item->sku,
                            'category' => $item->item->category,
                            'available_quantity' => $item->item->available_quantity,
                        ],
                    ];
                }),
                'notes' => $request->notes,
            ]);

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApiException('Failed to update items: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get appointment items
     * GET /api/appointments/{xid}/items
     */
    public function getItems($xid)
    {
        try {
            // Decode appointment XID
            $appointmentId = $this->getIdFromHash($xid);
            
            // Validate appointment exists and belongs to company
            $appointment = Appointment::find($appointmentId);
            
            if (!$appointment) {
                throw new ApiException('Appointment not found', null, 404);
            }

            // Get appointment items with relationships
            $appointmentItems = AppointmentItem::where('appointment_id', $appointmentId)
                ->with(['item.category', 'item.brand', 'item.manufacturer'])
                ->get();

            if ($appointmentItems->isEmpty()) {
                return ApiResponse::make('No items found', [
                    'items' => [],
                    'notes' => null,
                ], 200);
            }

            // Get notes from first item (since all items share the same notes)
            $notes = $appointmentItems->first()->notes;

            return ApiResponse::make('Success', [
                'items' => $appointmentItems->map(function ($appointmentItem) {
                    return [
                        'xid' => $appointmentItem->xid,
                        'item_xid' => $appointmentItem->item->xid,
                        'item_name' => $appointmentItem->item->name,
                        'quantity' => $appointmentItem->quantity,
                        'unit' => $appointmentItem->unit,
                        'item' => [
                            'xid' => $appointmentItem->item->xid,
                            'name' => $appointmentItem->item->name,
                            'sku' => $appointmentItem->item->sku,
                            'category' => $appointmentItem->item->category,
                            'category_id' => $appointmentItem->item->x_category_id,
                            'category_name' => $appointmentItem->item->category ? $appointmentItem->item->category->name : null,
                            'available_quantity' => $appointmentItem->item->available_quantity,
                            'unit' => $appointmentItem->item->unit,
                            'type' => $appointmentItem->item->type,
                        ],
                    ];
                }),
                'notes' => $notes,
            ]);

        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException('Failed to retrieve items: ' . $e->getMessage(), null, 500);
        }
    }
}
