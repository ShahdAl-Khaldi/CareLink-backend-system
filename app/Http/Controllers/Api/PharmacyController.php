<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function homeStats()
    {
        // جلب جميع الوصفات مع بيانات الموعد والمريض والطبيب والأدوية
        $prescriptions = Prescription::with(['appointment.patient', 'appointment.doctor', 'medicines'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedPrescriptions = $prescriptions->map(function ($rx) {
            $patient = $rx->appointment->patient ?? null;
            $doctor = $rx->appointment->doctor ?? null;

            $medicationsList = $rx->medicines->map(function ($med) {
                return "{$med->medicine_name} - الجرعة: {$med->dosage} - المدة: {$med->duration}";
            })->implode("\n");

            return [
                'id' => $rx->id,
                'patient' => $patient->full_name ?? $patient->name ?? 'مريض',
                'phone' => $patient->phone ?? '',
                'nationalId' => $patient->national_id ?? '',
                'doctor' => $doctor->name ?? 'طبيب',
                'medications' => $medicationsList ?: $rx->notes,
                'status' => $rx->status ?? 'pending',
                'createdAt' => $rx->created_at,
            ];
        });

        // حساب الأصناف التي تجاوزت الحد الأدنى في المخزون
        $lowStock = Inventory::whereColumn('quantity', '<=', 'min_quantity')->get();

        $total = $formattedPrescriptions->count();
        $dispensed = $formattedPrescriptions->where('status', 'dispensed')->count();
        $pending = $total - $dispensed;
        $latestPending = $formattedPrescriptions->where('status', '!=', 'dispensed')->take(5)->values();

        return response()->json([
            'data' => [
                'stats' => [
                    'total' => $total,
                    'pending' => $pending,
                    'dispensed' => $dispensed,
                    'latestPending' => $latestPending,
                ],
                'lowStock' => $lowStock,
            ],
        ], 200);
    }

    public function index()
    {
        // patient() موديل متعدد الأشكال (morphTo) - ممكن يكون User أو Patient
        // (مريض استقبال بدون حساب)، والعلاقة patientProfile موجودة بس على User،
        // فلازم morphWith بدل with() العادية وإلا بينكسر على مرضى الاستقبال
        $prescriptions = Prescription::with([
            'appointment.doctor',
            'appointment.patient' => function ($morphTo) {
                $morphTo->morphWith([User::class => ['patientProfile']]);
            },
            'medicines',
            'dispensedBy',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rx) {
                $patient = $rx->appointment->patient ?? null;
                $doctor = $rx->appointment->doctor ?? null;

                $medicationsList = $rx->medicines->map(function ($med) {
                    return "{$med->medicine_name} - الجرعة: {$med->dosage} - المدة: {$med->duration}";
                })->implode("\n");

                // مرضى الاستقبال (walk-in) مسجّلين بجدول Patient المنفصل وما إلهم
                // ملف طبي/حساسية أصلاً - الحساسية موجودة بس للمرضى يلي سجّلوا حسابهم بأنفسهم
                $allergies = $patient instanceof User
                    ? optional($patient->patientProfile)->allergies
                    : null;

                return [
                    'id' => $rx->id,
                    'patientId' => $patient->id ?? null,
                    'doctorId' => $doctor->id ?? null,
                    'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
                    'doctor' => $doctor->name ?? 'طبيب',
                    'phone' => $patient->phone ?? '',
                    'nationalId' => $patient->national_id ?? '',
                    'allergies' => $allergies,
                    'medications' => $medicationsList ?: $rx->notes,
                    'status' => $rx->status ?? 'pending', // سيقرأ الحالة الحقيقية من قاعدة البيانات
                    'createdAt' => $rx->created_at,
                    'dispensedAt' => $rx->dispensed_at,
                    'pharmacistName' => $rx->dispensedBy->name ?? null,
                    'dispenseNotes' => $rx->dispense_notes,
                    'verifyMethod' => $rx->verify_method,
                    'allergyWarning' => $rx->allergy_warning,
                    'allergyOverridden' => (bool) $rx->allergy_overridden,
                ];
            });

        return response()->json(['data' => $prescriptions], 200);
    }

    public function markReady($id)
    {
        $rx = Prescription::with('appointment.patient')->findOrFail($id);
        $rx->update(['status' => 'ready']); // سيحفظ الحالة بنجاح في قاعدة البيانات

        // كانت الشاشة بتقول "تم إشعار المريض" بس ما في أي إشعار حقيقي بينبعت -
        // بس المريض يلي عنده حساب حقيقي (User) بقدر يشوف إشعارات
        $patient = $rx->appointment?->patient;
        if ($patient instanceof User) {
            Notification::create([
                'type' => 'prescription_ready',
                'title' => 'دواؤك جاهز للاستلام',
                'body' => 'وصفتك الطبية أصبحت جاهزة، تفضّل لاستلامها من الصيدلية',
                'appointment_id' => $rx->appointment_id,
                'notifiable_id' => $patient->id,
                'notifiable_type' => User::class,
            ]);
        }

        return response()->json(['message' => 'تم تحديث حالة الوصفة إلى جاهز', 'data' => $rx], 200);
    }

    public function dispense(Request $request, $id)
    {
        $validated = $request->validate([
            'id_last_4' => 'nullable|string|max:4',
            'notes' => 'nullable|string|max:500',
            'verify_method' => 'nullable|in:national_id,phone',
            'allergy_warning' => 'nullable|string|max:255',
            'allergy_overridden' => 'nullable|boolean',
        ]);

        $rx = Prescription::findOrFail($id);
        $rx->update([
            'status' => 'dispensed',
            'dispensed_at' => now(),
            'dispensed_by' => $request->user()->id,
            'dispense_notes' => $validated['notes'] ?? null,
            'verify_method' => $validated['verify_method'] ?? null,
            'allergy_warning' => $validated['allergy_warning'] ?? null,
            'allergy_overridden' => $validated['allergy_overridden'] ?? false,
        ]);

        return response()->json(['message' => 'تم صرف الدواء بنجاح', 'data' => $rx], 200);
    }
}
