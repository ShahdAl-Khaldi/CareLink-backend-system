<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string',
            'specialty' => 'required|string',
            'credential_document' => 'required|file|mimes:pdf,jpg,png|max:2048',
            // كانت هاي كلها موجودة بفورم تسجيل الطبيب وبيرسلها الفرونت دايماً،
            // بس ما كانت متحقق منها ولا بتنحفظ - بتضيع بصمت. gender وyears_of_experience
            // إلزاميين هون تحديداً (مش nullable) لأنه عمودي doctor_profiles.gender
            // و doctor_profiles.years_of_experience أصلاً NOT NULL بقاعدة البيانات -
            // لو تركناهم nullable وحد ما بعتهم كان رح يطلع خطأ 500 بدل رسالة تحقق واضحة
            'national_id' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'gender' => 'required|in:male,female',
            'years_of_experience' => 'required|integer|min:0|max:60',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $path = $request->file('credential_document')->store('documents', 'public');

            // 1. إنشاء المستخدم بدور 'doctor'
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'] ?? null,
                'role' => 'doctor',
                // status هون معناه "الحساب مش موقوف" (تعليق/إيقاف من الإدارة لاحقاً)،
                // مش موافقة الإدارة - هاي منفصلة وبتنحفظ بـ doctorProfile.status
                // ('inactive' تحت، وما بيصير 'active' غير لما approveDoctor تشتغل)
                'status' => true,
            ]);

            // 2. إنشاء البروفايل الخاص بالطبيب بحالة inactive
            $user->doctorProfile()->create([
                'specialty' => $validated['specialty'],
                'credential_document' => $path,
                'national_id' => $validated['national_id'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'years_of_experience' => $validated['years_of_experience'] ?? null,
                'status' => 'inactive',
            ]);

            $token = $user->createToken('doctor_token')->plainTextToken;

            return response()->json([
                'message' => 'تم تسجيل الطبيب بنجاح, بانتظار التفعيل',
                'access_token' => $token,
                'user' => $user->load('doctorProfile'),
            ], 201);
        });
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        $user = User::where('email', $request->email)->where('role', 'doctor')->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        // التحقق من حالة التفعيل داخل البروفايل
        if ($user->doctorProfile->status !== 'active') {
            return response()->json(['message' => 'حسابك بانتظار موافقة الإدارة'], 403);
        }

        return response()->json([
            'access_token' => $user->createToken('doctor_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->where('role', 'doctor')->first();

        // exists:users,email بيتحقق من الإيميل بجدول users بشكل عام بس، مش
        // مقيّد بدور الطبيب - فإيميل مريض/موظف صحيح كان بيعدي الفاليديشن
        // وبعدين $user بيطلع null هون ويطلع خطأ 500 عند $user->email تحت
        if (! $user) {
            return response()->json(['message' => 'لا يوجد حساب طبيب بهذا البريد الإلكتروني'], 404);
        }

        $otp = random_int(10000, 99999);
        Cache::put('otp_doctor_'.$user->email, $otp, now()->addMinutes(10));

        // لازم نتحقق من نجاح الإرسال هون بالذات - لو فشل ورجعنا رسالة نجاح،
        // المستخدم رح يضل يستنى رمز ما رح يوصله أبداً
        if (! NotificationService::send('password_reset', $user, ['otp' => $otp])) {
            return response()->json([
                'message' => 'تعذر إرسال رمز التحقق حالياً، حاول مرة أخرى بعد قليل',
            ], 503);
        }

        return response()->json(['message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|numeric',
            'password' => 'required|confirmed|min:8',
        ]);

        $storedOtp = Cache::get('otp_doctor_'.$request->email);

        if (! $storedOtp || (int) $storedOtp !== (int) $request->token) {
            return response()->json(['message' => 'الكود غير صحيح أو انتهت صلاحيته'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget('otp_doctor_'.$request->email);

        return response()->json(['message' => 'تم تغيير كلمة السر بنجاح'], 200);
    }
}
