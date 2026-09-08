<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
 use App\Models\Broadcast;
use App\Models\Conversation;
use App\Models\ImagingOrder;
use App\Models\LabOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DoctorController extends Controller
{
    /**
     * عرض جميع الأطباء للوحة تحكم الأدمن
     */
    public function index()
    {
        // جلب المستخدمين الذين لديهم دور 'doctor'
        $doctors = User::where('role', 'doctor')
            ->with('doctorProfile') // جلب البيانات الطبية مع المستخدم
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $doctors,
        ], 200);
    }

    /**
     * عرض محادثات الطبيب
     */
    public function getMyConversations(Request $request)
    {
        // جلب المحادثات الخاصة بالطبيب الحالي
        $conversations = Conversation::where('doctor_id', $request->user()->id)
            ->with(['patient:id,name']) // علاقة الـ patient في موديل Conversation يجب أن تعود لـ User
            ->latest('updated_at')
            ->get();

        return response()->json(['data' => $conversations]);
    }

    public function getBroadcasts()
    {
        // جلب الرسائل الموجهة للجميع أو للأطباء فقط
        $broadcasts = Broadcast::whereIn('target', ['all', 'doctors'])
            ->latest()
            ->get();

        return response()->json(['data' => $broadcasts], 200);
    }

    /**
     * عرض البروفايل الكامل للطبيب (مع تفاصيل التخصص)
     */
    public function getProfile(Request $request)
    {
        $doctor = $request->user(); // الطبيب المسجل حالياً

        // تحميل البروفايل المرتبط به
        $doctor->load('doctorProfile');

        return response()->json([
            'status' => true,
            'data' => $doctor,
        ], 200);
    }

    /**
     * تحديث البروفايل الخاص بالطبيب
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // 1. التحقق من البيانات (إضافة جميع الحقول المطلوبة)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'specialty' => 'nullable|string',
            'national_id' => 'nullable|string',
        ]);

        // 2. تحديث جدول المستخدم (users)
        // national_id هون كمان (مش بس بـ doctor_profiles) عشان الاثنين ما
        // يضلوا يتعارضوا مع بعض - كان قبل بس doctor_profiles.national_id
        // يتحدث فتصير قيمتين مختلفتين لنفس الطبيب بجدولين مختلفين
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'national_id' => $validated['national_id'] ?? $user->national_id,
        ]);

        // 3. تحديث جدول بروفايل الطبيب (doctor_profiles)
        // نحافظ على القيمة القديمة لو الحقل ما انبعت بدل ما نفرض default معيّن
        // (كان gender الغير مرسل بيترجم "male" افتراضياً حتى لو الطبيب فعلياً
        // مسجل "female" - ممكن يمسح بيانات صحيحة لو أي عميل تاني نادى الـ API
        // بدون كل الحقول)
        $existingProfile = $user->doctorProfile;
        $user->doctorProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'date_of_birth' => $validated['date_of_birth'] ?? $existingProfile?->date_of_birth,
                'address' => $validated['address'] ?? $existingProfile?->address,
                'gender' => $validated['gender'] ?? $existingProfile?->gender ?? 'male',
                'specialty' => $validated['specialty'] ?? $existingProfile?->specialty ?? 'غير محدد',
                'national_id' => $validated['national_id'] ?? $existingProfile?->national_id,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث البيانات بنجاح',
        ], 200);
    }

    // نفس منطق PatientController::updateProfilePicture بالظبط
    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->update(['profile_picture' => $path]);

        return response()->json([
            'message' => 'تم تحديث الصورة الشخصية',
            // مسار نسبي متل باقي نقاط الوصول - كان هون بيرجع رابط كامل
            // (asset()) وهاد كان يسبب رابط مكرر مرتين بالـ header/sidebar
            // لما الفرونت يحط البادئة عليه كمان
            'profile_picture' => $path,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'تم تحديث كلمة المرور بنجاح',
        ], 200);
    }

   public function homeStats()
{
    $doctor = auth()->user();
    $doctorId = $doctor->id;

    // جلب المواعيد مع بيانات المريض الأساسية الموجودة في الجدول فعلياً
    $appointments = Appointment::where('doctor_id', $doctorId)
        ->with('patient')
        ->orderBy('scheduled_at', 'asc')
        ->get();

    $formattedAppointments = $appointments->map(function ($app) {
        $patient = $app->patient;

        return [
            'id' => $app->id,
            'status' => $app->status,
            'type' => $app->type,
            'scheduled_at' => $app->scheduled_at,
            'patient_id' => $app->patient_id,
            'patient_type' => $app->patient_type,
            'patient_name' => $patient->full_name ?? $patient->name ?? 'مريض',
            'patient_phone' => $patient->phone ?? '—',
            'patient_avatar' => $patient->image ?? null, // أو اجعلها null مباشرة إذا لم يكن هناك عمود صورة
        ];
    });

    // نتائج التحاليل/الأشعة الجاهزة يلي محتاجة مراجعة الطبيب - حقيقية بالكامل
    // (لا يوجد مفهوم "قيمة حرجة" أو "مقفل" بالسكيما الحقيقية الحالية، فبنسيبهم فاضيين)
    $labs = LabOrder::where('doctor_id', $doctorId)
        ->where('status', 'completed')
        ->with('appointment.patient')
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($order) {
            // resolvePatient() كان ينادى 3 مرات، وثاني مرة بدون null-safe -
            // لو موعد الطلب محذوف أو ما فيه مريض قابل للحل كان بيطيح الـ
            // endpoint كله بـ 500 بدل ما يرجع "مريض" افتراضياً
            $patient = $order->resolvePatient();

            return [
                'id' => $order->id,
                'appointmentId' => $order->appointment_id,
                'patientId' => $patient?->id,
                'patientName' => $patient->full_name ?? $patient->name ?? 'مريض',
                'tests' => $order->tests,
                'hasCritical' => false,
            ];
        });

    $imaging = ImagingOrder::where('doctor_id', $doctorId)
        ->where('status', 'completed')
        ->with('appointment.patient')
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($order) {
            $patient = $order->resolvePatient();

            return [
                'id' => $order->id,
                'appointmentId' => $order->appointment_id,
                'patientId' => $patient?->id,
                'patientName' => $patient->full_name ?? $patient->name ?? 'مريض',
                'studies' => $order->studies,
            ];
        });

    return response()->json([
        'message' => 'تم استرجاع بيانات الصفحة الرئيسية بنجاح',
        'data' => $formattedAppointments,
        'specialty' => $doctor->doctorProfile->specialty ?? null,
        'readyResults' => [
            'labs' => $labs,
            'imaging' => $imaging,
            'criticalLabs' => [],
            'readyCount' => $labs->count() + $imaging->count(),
        ],
    ], 200);
}
}
