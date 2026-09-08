<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    // عرض جميع الإعلانات
    public function index()
    {
        return response()->json(['status' => true, 'data' => Ad::latest()->get()], 200);
    }

    // إضافة إعلان جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'link' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('ads', 'public');
            // إذا كان اسم الحقل في جدول قاعدة البيانات لديك هو image بدلاً من image_path، استبدل السطر السابق بـ:
            // $validated['image'] = $request->file('image')->store('ads', 'public');
        }

        $ad = Ad::create($validated);

        return response()->json([
            'message' => 'تم إضافة الإعلان بنجاح',
            'data' => $ad
        ], 201);
    }

    // تعديل إعلان
    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'link' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('ads', 'public');
        }

        $ad->update($validated);

        return response()->json([
            'message' => 'تم تحديث الإعلان بنجاح',
            'data' => $ad
        ]);
    }

    // حذف إعلان
    public function destroy($id)
    {
        $ad = Ad::findOrFail($id);

        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }

        $ad->delete();

        return response()->json(['message' => 'تم حذف الإعلان بنجاح']);
    }
}