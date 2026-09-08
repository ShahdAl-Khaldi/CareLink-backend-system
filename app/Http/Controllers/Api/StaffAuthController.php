<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|in:reception,laboratory,pharmacy,radiology,inventory_manager',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
            ]);
        }

        if ($user->role !== $request->role) {
            throw ValidationException::withMessages([
                'email' => ['ليس لديك صلاحية الدخول من هذه البوابّة.'],
            ]);
        }

        if (! $user->status) {
            return response()->json(['message' => 'هذا الحساب موقوف من قبل الإدارة.'], 403);
        }

        $token = $user->createToken('staff-token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // شاشة "تغيير كلمة المرور الإجبارية" بعد أول دخول بكلمة مرور مؤقتة من الإدارة
    // - شغالة لأي دور مسجل دخول (staff, doctor, admin) مش بس أدوار الموظفين
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return response()->json(['message' => 'تم تحديث كلمة المرور بنجاح']);
    }

    // تسجيل الخروج - شغالة لأي دور مسجل دخول (نفس فكرة changePassword فوق).
    // قبلها ما كان في مسار logout إطلاقاً، فـ "تسجيل الخروج" بالفرونت كان مجرد
    // مسح localStorage محلياً - التوكن نفسه يضل صالح للأبد على السيرفر (Sanctum
    // expiration = null بهاد المشروع) وممكن أي حد سرقه يستمر يستخدمه حتى بعد
    // ما صاحب الحساب "يسجل خروج"
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }
}
