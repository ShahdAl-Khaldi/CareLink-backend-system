<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(['status' => true, 'data' => Notification::latest()->paginate(20)], 200);
    }

    // إرسال إشعار للجميع
    public function sendGeneral(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $notification = Notification::create([
            'type' => 'general',
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        NotificationService::sendToAll($validated['title'], $validated['body']);

        return response()->json(['message' => 'تم إرسال الإشعار للجميع بنجاح', 'data' => $notification], 200);
    }

    // إرسال إشعار خاص لمستخدم معين (بناءً على الـ role)
    public function sendToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        // التعامل مع المستخدم كـ User فقط
        $notification = Notification::create([
            'type' => 'targeted',
            'title' => $validated['title'],
            'body' => $validated['body'],
            'notifiable_id' => $validated['user_id'],
            'notifiable_type' => User::class, // النظام الموحد
        ]);

        NotificationService::sendToUser($validated['user_id'], User::class, $validated['title'], $validated['body']);

        return response()->json(['message' => 'تم إرسال الإشعار بنجاح']);
    }
}