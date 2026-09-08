<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Broadcast;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function store(Request $request)
    {
        // 1. تحقق صارم يتطابق مع نفس حقول فورم AddNewDoctor بالفرونت
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'national_id' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'gender' => 'required|in:male,female',
            'specialty' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0|max:60',
            'credential_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $credentialPath = $request->file('credential_document')->store('documents', 'public');
        // نفس عمود/مسار DoctorController::updateProfilePicture - LandingController
        // بيقرأ الصورة من users.profile_picture مباشرة، مش من doctor_profiles
        $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');

        // استخدام Transaction لضمان عدم إنشاء مستخدم بدون بروفايل في حال حدوث خطأ
        return DB::transaction(function () use ($validated, $credentialPath, $profilePicturePath) {

            // 2. إنشاء المستخدم الأساسي
            $user = User::create([
                'name' => $validated['name'], // تأكد أن اسم العمود في الداتا بيز name وليس full_name
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'],
                'role' => 'doctor',
                'profile_picture' => $profilePicturePath,
                'status' => true,
            ]);

            // 3. إنشاء البروفايل الخاص بالطبيب بحالة Active فوراً
            $user->doctorProfile()->create([
                'specialty' => $validated['specialty'],
                'national_id' => $validated['national_id'],
                'address' => $validated['address'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'years_of_experience' => $validated['years_of_experience'],
                'credential_document' => $credentialPath,
                'status' => 'active', // مفعل تلقائياً لأن الأدمن من أضافه
            ]);

            // لا ترجع أي Token هنا، فقط رسالة نجاح
            return response()->json([
                'status' => true,
                'message' => 'تم إضافة الطبيب وتفعيله بنجاح',
            ], 201);
        });
    }

    public function getAllAppointments()
    {
        $appointments = Appointment::with(['patient', 'doctor:id,name', 'rating'])
            ->latest('scheduled_at')
            ->get();

        return response()->json(['data' => $appointments]);
    }

    public function getAllAdmins()
    {
        // جلب جميع المستخدمين ذوي الدور 'admin'
        $admins = User::where('role', 'admin')
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب قائمة المسؤولين بنجاح',
            'data' => $admins,
        ], 200);
    }

    // 1. عرض قائمة الأطباء الذين ينتظرون التفعيل (حالة الـ profile الخاصة بهم inactive)
    public function showPending()
    {
        $pendingDoctors = User::where('role', 'doctor')
            ->whereHas('doctorProfile', function ($query) {
                $query->where('status', 'inactive');
            })
            ->with('doctorProfile')
            ->get();

        if ($pendingDoctors->isEmpty()) {
            return response()->json(['message' => 'لا يوجد أطباء بانتظار التفعيل حالياً'], 200);
        }

        return response()->json([
            'message' => 'قائمة الأطباء المعلقين',
            'doctors' => $pendingDoctors,
        ], 200);
    }

    // 2. تفعيل حساب الطبيب
    public function approveDoctor($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);

        // تحديث حالة الطبيب في الـ Profile
        $doctor->doctorProfile()->update(['status' => 'active']);

        NotificationService::send('doctor_approved', $doctor, ['name' => $doctor->name]);

        return response()->json([
            'message' => 'تم تفعيل حساب الطبيب '.$doctor->name.' بنجاح',
        ], 200);
    }

    // 3. رفض الطلب وحذف الطبيب
    public function rejectDoctor($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);

        // حذف ملفات الشهادات من الـ profile إذا كانت موجودة
        if ($doctor->doctorProfile && $doctor->doctorProfile->credential_document) {
            Storage::disk('public')->delete($doctor->doctorProfile->credential_document);
        }

        $doctor->delete();

        return response()->json([
            'message' => 'تم رفض طلب الطبيب وحذف بياناته من النظام',
        ], 200);
    }

    public function suspendDoctor($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);

        $doctor->update(['status' => false]);

        NotificationService::send('doctor_suspended', $doctor, ['name' => $doctor->name]);

        return response()->json([
            'message' => 'تم إيقاف حساب الطبيب '.$doctor->name.' بنجاح',
        ], 200);
    }

    public function activateDoctor($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);

        $doctor->update(['status' => true]);

        NotificationService::send('doctor_activated', $doctor, ['name' => $doctor->name]);

        return response()->json([
            'message' => 'تم تفعيل حساب الطبيب '.$doctor->name.' بنجاح',
        ], 200);
    }

    public function sendEmailToDoctor(Request $request, $id)
    {
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $doctor = User::where('role', 'doctor')->findOrFail($id);

        // هاد الإجراء كله معناه "أرسل إيميل" فلازم نتحقق من نجاحه فعلياً -
        // مش زي approveDoctor يلي الإيميل فيه إشعار ثانوي عن إجراء خلص أصلاً
        try {
            Mail::raw($validated['message'], function ($mail) use ($doctor, $validated) {
                $mail->to($doctor->email)
                    ->subject($validated['subject'] ?: 'CareLink — إشعار من الإدارة');
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'تعذر إرسال البريد حالياً، حاول مرة أخرى بعد قليل',
            ], 503);
        }

        return response()->json([
            'message' => 'تم إرسال البريد إلى '.$doctor->email.' بنجاح',
        ], 200);
    }

    public function sendBroadcast(Request $request)
    {
        $request->validate(['message' => 'required', 'target' => 'required']);

        $broadcast = Broadcast::create($request->all());

        return response()->json(['message' => 'تم الإرسال والحفظ', 'data' => $broadcast]);
    }

    public function getAllBroadcasts()
    {
        return response()->json(Broadcast::latest()->get());
    }

    public function deleteBroadcast($id)
    {
        Broadcast::findOrFail($id)->delete();

        return response()->json(['message' => 'تم حذف الإعلان بنجاح'], 200);
    }

    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    // تحديث البيانات الشخصية
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:20',
            'national_id' => 'required|string|max:20',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'تم تحديث البيانات بنجاح']);
    }

    // تحديث كلمة المرور
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'تم تحديث كلمة المرور بنجاح']);
    }


}
