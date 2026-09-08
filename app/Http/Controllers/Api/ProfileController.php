<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // نقطة وحدة مشتركة لكل الأدوار (موظف استقبال، فني مختبر/أشعة، صيدلي، طبيب، مريض...)
    // لأنه كلهم بالنهاية صف بجدول users وعمود profile_picture واحد
    public function updatePicture(Request $request)
    {
        $request->validate([
            'picture' => 'required|image|max:4096', // حتى 4 ميغا
        ]);

        $user = $request->user();

        // حذف الصورة القديمة (إذا كانت مرفوعة من عندنا) قبل ما نخزن الجديدة
        if ($user->profile_picture && str_contains($user->profile_picture, '/storage/profile_pictures/')) {
            $oldPath = 'profile_pictures/'.basename($user->profile_picture);
            Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('picture')->store('profile_pictures', 'public');
        $user->update(['profile_picture' => asset('storage/'.$path)]);

        return response()->json([
            'message' => 'تم تحديث الصورة الشخصية بنجاح',
            'profile_picture' => $user->profile_picture,
        ]);
    }
}
