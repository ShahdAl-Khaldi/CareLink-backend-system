<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // الأدوار يلي إلها بروفايل خاص (باقي الأدوار بتخزن department/specialty/credential_document مباشرة على users)
    private const PROFILE_RELATIONS = [
        'doctor' => 'doctorProfile',
        'reception' => 'receptionistProfile',
    ];

    // الأدوار يلي لازم شهادة/CV إجباري عند الإضافة والتعديل
    private const CREDENTIAL_REQUIRED_ROLES = ['pharmacy', 'laboratory', 'radiology'];

    // عرض الكوادر حسب الدور (doctor, reception, laboratory, pharmacy, radiology, inventory_manager)
    public function index(Request $request)
    {
        $role = $request->query('role', 'doctor');
        $relation = self::PROFILE_RELATIONS[$role] ?? null;

        $query = User::where('role', $role)->latest();
        if ($relation) {
            $query->with($relation);
        }

        return response()->json(['data' => $query->get()]);
    }

    // إضافة عضو جديد
    public function store(Request $request)
    {
        $role = $request->input('role');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:doctor,reception,laboratory,pharmacy,radiology,inventory_manager',
            'national_id' => 'required|string|unique:users,national_id',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'specialty' => 'nullable|string',
            'credential_document' => in_array($role, self::CREDENTIAL_REQUIRED_ROLES, true)
                ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
                : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $credentialPath = $request->hasFile('credential_document')
            ? $request->file('credential_document')->store('documents', 'public')
            : null;

        // 1. إنشاء المستخدم في جدول users - القسم/التخصص/الشهادة بتنخزن هون مباشرة
        // لأنه معظم هالأدوار (صيدلية/مختبر/أشعة/مخزون) ما إلها جدول profile خاص
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'national_id' => $validated['national_id'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'specialty' => $validated['specialty'] ?? null,
            'credential_document' => $credentialPath,
            'status' => true,
            // الفرونت بيسمي حقل الباسورد هون "كلمة المرور المؤقتة" - لازم
            // نجبر تغييرها أول تسجيل دخول
            'must_change_password' => true,
        ]);

        // 2. لو الدور إله جدول profile مخصص (طبيب/استقبال) منعمّره كمان
        $relation = self::PROFILE_RELATIONS[$role] ?? null;
        if ($relation === 'doctorProfile') {
            $user->doctorProfile()->create([
                'specialty' => $validated['specialty'] ?? 'غير محدد',
                'status' => 'active',
            ]);
        } elseif ($relation === 'receptionistProfile') {
            $user->receptionistProfile()->create([]);
        }

        return response()->json(['message' => 'تمت الإضافة بنجاح', 'data' => $user], 201);
    }

    // تحديث بيانات العضو
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isDoctor = $user->role === 'doctor';
        $needsCredential = in_array($user->role, self::CREDENTIAL_REQUIRED_ROLES, true);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'national_id' => 'required|string|unique:users,national_id,'.$user->id,
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'specialty' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'credential_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        if ($isDoctor) {
            $rules = array_merge($rules, [
                'date_of_birth' => 'required|date',
                'address' => 'required|string|max:500',
                'gender' => 'required|in:male,female',
                'specialty' => 'required|string|max:255',
                'years_of_experience' => 'required|integer|min:0|max:60',
            ]);
        }

        $validated = $request->validate($rules);

        $credentialPath = $request->hasFile('credential_document')
            ? $request->file('credential_document')->store('documents', 'public')
            : null;

        // لازم نتحقق قبل أي تعديل على الداتابيز - كانت هاي الرسالة بترجع 422
        // بس بعد ما التحديث صار فعلياً على جدول users والبروفايل، يعني "الفشل"
        // كان بيسجل تعديل جزئي بصمت رغم رسالة الخطأ
        if ($needsCredential && ! $credentialPath && ! $user->credential_document) {
            return response()->json([
                'message' => 'يرجى إرفاق ملف الشهادة أو الـ CV',
                'errors' => ['credential_document' => ['هذا الحقل إلزامي لهذا الدور']],
            ], 422);
        }

        // 1. تحديث بيانات جدول users
        // phone/department/specialty نخليهم يحافظوا على القيمة القديمة لو
        // ما انبعتوا بالطلب، بدل ما ننسخهم لـ null (نفس باق DoctorController
        // /PatientController يلي انصلح قبل شوي)
        $userUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'national_id' => $validated['national_id'],
            'phone' => $validated['phone'] ?? $user->phone,
            'department' => $validated['department'] ?? $user->department,
            'specialty' => $validated['specialty'] ?? $user->specialty,
        ];
        if (! empty($validated['password'])) {
            $userUpdate['password'] = Hash::make($validated['password']);
        }
        if ($credentialPath) {
            $userUpdate['credential_document'] = $credentialPath;
        }
        $user->update($userUpdate);

        // 2. تحديث أو إنشاء البروفايل المرتبط
        if ($isDoctor) {
            $profileData = [
                'specialty' => $validated['specialty'],
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'gender' => $validated['gender'],
                'years_of_experience' => $validated['years_of_experience'],
            ];
            if ($credentialPath) {
                $profileData['credential_document'] = $credentialPath;
            }
            $user->doctorProfile()->updateOrCreate(['user_id' => $user->id], $profileData);
        } elseif ($user->role === 'reception') {
            $user->receptionistProfile()->updateOrCreate(['user_id' => $user->id], []);
        }

        return response()->json(['message' => 'تم التحديث بنجاح', 'data' => $user]);
    }

    // تغيير حالة العضو (تفعيل / إيقاف)
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => 'required|boolean', // استقبال قيمّة boolean (true أو false)
        ]);

        $user->update(['status' => $request->status]);

        return response()->json(['message' => 'تم تغيير الحالة بنجاح']);
    }

    // حذف العضو
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
