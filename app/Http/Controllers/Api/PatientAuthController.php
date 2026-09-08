<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PatientProfile;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PatientAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string',
            // الحقول الأخرى التي كانت في الـ patient القديم (مثل الهوية)
            // يفضل وضعها في بروفايل أو جدول مستخدمين موسع
            'national_id' => 'required|string|unique:users',
            // كانت هاي الثلاثة موجودة بفورم التسجيل وبيرسلها الفرونت دايماً، بس
            // ما كانت متحقق منها ولا بتنحفظ - بتضيع بصمت رغم إنه المستخدم عبّاها
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'gender' => 'nullable|in:male,female',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. إنشاء المستخدم بدور مريض
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'],
                'birth_date' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'role' => 'patient',
                'status' => true,
            ]);

            // 2. إنشاء البروفايل الطبي المرتبط
            $user->patientProfile()->create();

            $token = $user->createToken('patient_token')->plainTextToken;

            return response()->json([
                'message' => 'تم تسجيل المريض بنجاح',
                'access_token' => $token,
                'user' => $user->load('patientProfile'),
            ], 201);
        });
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'weight_kg' => 'nullable|numeric|min:0',
            'height_cm' => 'nullable|numeric|min:0',
            'is_diabetic' => 'nullable|boolean',
            'is_hypertensive' => 'nullable|boolean',
            'is_smoker' => 'nullable|boolean',
            'allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
        ]);

        auth()->user()->patientProfile()->update($validated);

        return response()->json([
            'message' => 'تم تحديث الملف الطبي بنجاح',
            'profile' => auth()->user()->patientProfile,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        $user = User::where('email', $request->email)->where('role', 'patient')->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        return response()->json([
            'access_token' => $user->createToken('patient_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->where('role', 'patient')->first();

        // exists:users,email بيتحقق من الإيميل بجدول users بشكل عام بس، مش
        // مقيّد بدور المريض - فإيميل طبيب/موظف صحيح كان بيعدي الفاليديشن
        // وبعدين $user بيطلع null هون ويطلع خطأ 500 عند $user->email تحت
        if (! $user) {
            return response()->json(['message' => 'لا يوجد حساب مريض بهذا البريد الإلكتروني'], 404);
        }

        $otp = random_int(10000, 99999);
        Cache::put('otp_patient_'.$user->email, $otp, now()->addMinutes(10));

        // هون بالذات لازم نتحقق من نجاح الإرسال (عكس approveDoctor مثلاً) -
        // الإجراء كله معناه "استلم رمز عبر الإيميل"، فلو فشل الإرسال فعلياً
        // ورجعنا رسالة نجاح، المستخدم رح يضل يستنى رمز ما رح يوصله أبداً
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

        $storedOtp = Cache::get('otp_patient_'.$request->email);

        if (! $storedOtp || (string) $storedOtp !== (string) $request->token) {
            return response()->json(['message' => 'الكود غير صحيح أو انتهت صلاحيته'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        Cache::forget('otp_patient_'.$request->email);

        return response()->json(['message' => 'تم تغيير كلمة السر بنجاح'], 200);
    }
}
