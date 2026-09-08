<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index()
    {
        return response()->json(['data' => ContactMessage::latest()->get()]);
    }

    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json(['message' => 'تم التحديث', 'data' => $message]);
    }

    public function destroy($id)
    {
        ContactMessage::destroy($id);

        return response()->json(['message' => 'تم حذف الرسالة']);
    }
}
