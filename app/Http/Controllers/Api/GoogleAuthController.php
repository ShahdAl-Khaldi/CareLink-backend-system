<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    // زر "المتابعة بـ Google" بصفحة تسجيل/دخول المريض بيوجّه المتصفح مباشرة
    // لهون (تنقل صفحة كامل، مش fetch)، وهاي بتحوّل المستخدم لصفحة موافقة Google
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Google بترجع المستخدم لهون بعد ما يوافق. منلاقي حسابه أو ننشئه كمريض،
    // وبعدين نرجّعه للفرونت مع توكن Sanctum جاهز بالـ query عشان يسجّل جلسته
    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'مستخدم Google',
                'email' => $googleUser->getEmail(),
                // ما في باسورد فعلي لأنه بيسجل دخول بـ Google دايماً، بس العمود مطلوب بالجدول
                'password' => Hash::make(Str::random(40)),
                'role' => 'patient',
                'status' => true,
                'national_id' => 'google_'.$googleUser->getId(),
            ]);
        } else {
            // حساب موجود مسبقاً بنفس الإيميل - لازم نطبّق نفس فحوصات
            // التعليق/الموافقة يلي كل بوابات الدخول التانية (staff/doctor) بتعملها،
            // وإلا طبيب موقوف عن العمل أو لسا بانتظار موافقة الإدارة، أو موظف
            // موقوف، كان يقدر يتجاوزها كلها بمجرد الدخول بـ Google
            if ($user->role === 'doctor') {
                if (! $user->doctorProfile || $user->doctorProfile->status !== 'active') {
                    return redirect($this->frontendErrorUrl('حسابك بانتظار موافقة الإدارة'));
                }
            } elseif (! $user->status) {
                return redirect($this->frontendErrorUrl('هذا الحساب موقوف من قبل الإدارة.'));
            }
        }

        $token = $user->createToken('google-token')->plainTextToken;

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        // بنبعت الاسم/الإيميل بالـ query كمان عشان صفحة الفرونت تبني الـ profile
        // مباشرة بدون ما تحتاج طلب إضافي لجلب بيانات المستخدم
        $query = http_build_query([
            'token' => $token,
            'role' => $user->role,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return redirect("{$frontendUrl}/auth/google/callback?{$query}");
    }

    private function frontendErrorUrl(string $message): string
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $query = http_build_query(['error' => $message]);

        return "{$frontendUrl}/auth/google/callback?{$query}";
    }
}
