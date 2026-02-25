<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Notification\IndexRequest;
use App\Http\Requests\Api\Notification\MarkAsReadRequest;
use App\Http\Requests\Api\Notification\MarkAllAsReadRequest;
use App\Traits\CompanyTraits;

class NotificationController extends ApiBaseController
{
    use CompanyTraits;

    protected $model = Notification::class;
    protected $indexRequest = IndexRequest::class;

    public function modifyIndex($query)
    {
        $request = request();
        $user = auth('api')->user();

        // Filter by authenticated user
        $query = $query->where('user_id', $user->id);

        // Order by most recent first
        $query = $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * Get count of unread notifications
     */
    public function unreadCount()
    {
        $user = auth('api')->user();
        
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(MarkAsReadRequest $request, $xid)
    {
        // Convert xid to actual id
        $notificationId = $this->getIdFromHash($xid);
        
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', auth('api')->user()->id)
            ->firstOrFail();
        
        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead(MarkAllAsReadRequest $request)
    {
        $user = auth('api')->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true
        ]);
    }
}
