<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Broadcast;
use App\Models\DoctorRating;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\PrescriptionRefillRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\LabOrder;

class PatientController extends Controller
{
    public function doctors()
    {
        $doctors = User::where('role', 'doctor')->with('doctorProfile')->get();

        return response()->json([
            'status' => true,
            'data' => $doctors,
        ], 200);
    }

    public function getAllPatients()
    {
        $patients = User::where('role', 'patient')
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب قائمة المرضى بنجاح',
            'data' => $patients,
        ], 200);
    }

    // مرضى مسجّلين من شاشة الاستقبال (جدول patients المنفصل عن users) -
    // ما إلهم حساب دخول، فبيضلوا مفصولين عن قائمة "مرضى بحساب" فوق
    public function getReceptionPatients()
    {
        $patients = Patient::select('id', 'full_name', 'phone', 'national_id', 'birth_date', 'insurance_status', 'guardian_id', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب قائمة مرضى الاستقبال بنجاح',
            'data' => $patients,
        ], 200);
    }

    public function profile(Request $request)
    {
        $patient = $request->user()->load('patientProfile');

        return response()->json([
            'message' => 'بيانات المريض الشخصية والطبية',
            'user' => $patient,
        ], 200);
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

        auth()->user()->patientProfile()->updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return response()->json([
            'message' => 'تم تحديث الملف الطبي بنجاح',
            'profile' => auth()->user()->patientProfile()->first(),
        ]);
    }

    public function updateAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$request->user()->id,
            'phone' => 'nullable|string|max:20',
            'national_id' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string',
            'address' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // بقية الحقول nullable - لو ما انبعتوا بالطلب أصلاً (مش null صراحةً)
        // كانوا بيتمسحوا لـ null بدل ما يضلوا القيمة المخزنة، لأنه array_key
        // مش موجود بـ $validated أساساً
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'national_id' => $validated['national_id'] ?? $user->national_id,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
            'gender' => $validated['gender'] ?? $user->gender,
            'address' => $validated['address'] ?? $user->address,
        ]);

        return response()->json([
            'message' => 'تم تحديث بيانات الحساب بنجاح',
            'user' => $user,
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

    public function getMedicalProfile(Request $request)
    {
        $profile = PatientProfile::firstOrCreate(
            ['user_id' => $request->user()->id]
        );

        return response()->json([
            'message' => 'بيانات الملف الطبي',
            'data' => [
                ...$profile->toArray(),
                'national_id' => $request->user()->national_id,
            ],
        ], 200);
    }

public function myMedicalRecords(Request $request)
    {
        $user = $request->user();

        // لازم نفلتر على patient_type = User كمان، لأنه appointments فيها مرضى
        // من جدول Patient (تسجيل الاستقبال) بنفس الـ id بالصدفة - بدون هاد
        // الفلتر كانت ممكن ترجع مواعيد مريض تاني إذا تطابق الـ id رقمياً بس
        $records = Appointment::where('patient_id', $user->id)
            ->where('patient_type', User::class)
            // prescription محتاجينها عشان الفرونت يعرف الحالة الحقيقية للوصفة
            // (قيد الانتظار/جاهزة/تم الصرف) بدل ما يفترض إنها "صدرت" دايماً -
            // وآخر طلب تجديد (إن وجد) عشان الفرونت يعرف إذا في طلب معلّق أصلاً
            ->with(['doctor:id,name', 'prescription.refillRequests' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest()
            ->get();

        // lab_orders ما فيها عمود patient_id (تم استبداله بـ appointment_id)
        // بعد إعادة هيكلة الجدول - كانت هاي بتكسر الـ API بخطأ SQL 500 وبتظهر
        // فاضية بالفرونت لأنه كان في catch صامت
        $labs = LabOrder::whereHas('appointment', function ($query) use ($user) {
                $query->where('patient_id', $user->id)->where('patient_type', User::class);
            })
            ->with('doctor:id,name')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'تم استرجاع السجلات الطبية',
            'data' => $records,
            'labs' => $labs, // إرسال التحاليل مع الـ Response
        ]);
    }

    // FR-06.11: المريض يطلب تجديد وصفة مصروفة سابقاً - الطلب يروح للطبيب يلي
    // كتب الوصفة الأصلية للموافقة أو الرفض
    public function requestPrescriptionRefill(Request $request, $prescriptionId)
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        $prescription = Prescription::with('appointment')
            ->whereHas('appointment', function ($query) use ($user) {
                $query->where('patient_id', $user->id)->where('patient_type', User::class);
            })
            ->findOrFail($prescriptionId);

        if ($prescription->status !== 'dispensed') {
            return response()->json([
                'message' => 'لا يمكن طلب تجديد وصفة لم تُصرف بعد',
            ], 422);
        }

        $hasPendingRequest = PrescriptionRefillRequest::where('prescription_id', $prescription->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingRequest) {
            return response()->json([
                'message' => 'يوجد بالفعل طلب تجديد قيد الانتظار لهذه الوصفة',
            ], 422);
        }

        $refillRequest = PrescriptionRefillRequest::create([
            'prescription_id' => $prescription->id,
            'patient_id' => $user->id,
            'doctor_id' => $prescription->appointment->doctor_id,
            'status' => 'pending',
            'patient_note' => $validated['note'] ?? null,
        ]);

        Notification::create([
            'type' => 'refill_requested',
            'title' => 'طلب تجديد وصفة طبية',
            'body' => $user->name.' يطلب تجديد وصفة: '.($prescription->notes ?? 'وصفة طبية'),
            'appointment_id' => $prescription->appointment_id,
            'notifiable_id' => $prescription->appointment->doctor_id,
            'notifiable_type' => User::class,
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب التجديد للطبيب بنجاح',
            'data' => $refillRequest,
        ], 201);
    }

    public function getBroadcasts()
    {
        $broadcasts = Broadcast::whereIn('target', ['all', 'patients'])
            ->latest()
            ->get();

        return response()->json(['data' => $broadcasts], 200);
    }

    public function updateProfilePicture(Request $request)
    {
        try {

            $request->validate([
                'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $user = $request->user();

            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')
                ->store('profile_pictures', 'public');

            $user->update([
                'profile_picture' => $path,
            ]);

            return response()->json([
                'message' => 'تم تحديث الصورة الشخصية',
                // مسار نسبي بس، متل ما بترجع كل نقاط الوصول التانية وبيتخزن
                // بقاعدة البيانات - كانت هون بس بترجع رابط كامل (asset()) وهاد
                // كان يسبب رابط مكرر مرتين لما الفرونت يحط البادئة عليه كمان
                'profile_picture' => $path,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);

        }
    }

    public function storeRating(Request $request, $appointmentId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $appointment = Appointment::where('id', $appointmentId)
            ->where('patient_type', User::class)
            ->where('patient_id', $request->user()->id)
            ->firstOrFail();

        if ($appointment->status !== 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكن تقييم موعد غير منتهي',
            ], 422);
        }

        $exists = DoctorRating::where('appointment_id', $appointment->id)->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'لقد قمت بتقييم هذا الموعد مسبقاً',
            ], 422);
        }

        // في قيد unique على (patient_id, appointment_id) بقاعدة البيانات كخط
        // دفاع أخير - لو صارت طلبين بنفس اللحظة (ضغطة مزدوجة، إعادة محاولة
        // شبكة...) وعدّوا فحص exists() فوق سوا، الطلب التاني رح يصطدم بالقيد
        // ونمسكه هون بدل ما يطلع خطأ سيرفر خام (500)
        try {
            $rating = DoctorRating::create([
                'patient_id' => $request->user()->id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'لقد قمت بتقييم هذا الموعد مسبقاً',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ التقييم بنجاح',
            'data' => $rating,
        ], 200);
    }
}
