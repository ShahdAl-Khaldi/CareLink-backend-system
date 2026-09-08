<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
 class MyNotificationController extends Controller
{
    // كل إشعارات المستخدم الحالي
    public function index(Request $request)
    {
        $notifications = Notification::forUser($request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json(['status' => true, 'data' => $notifications]);
    }

    // بس رقم الغير مقروء (لعرض الـ badge)
    public function unreadCount(Request $request)
    {
        $count = Notification::forUser($request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::forUser($request->user()->id)->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'تم التحديث']);
    }

    public function markAllAsRead(Request $request)
    {
        Notification::forUser($request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'تم تحديث الكل']);
    }
}