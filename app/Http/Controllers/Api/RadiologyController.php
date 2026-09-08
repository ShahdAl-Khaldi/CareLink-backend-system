<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagingOrder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RadiologyController extends Controller
{
    // جلب كل طلبات الأشعة للوحة فني الأشعة
    public function index()
    {
        $orders = ImagingOrder::with(['appointment.patient', 'doctor'])
            ->latest()
            ->get()
            ->map(function ($order) {
                $patient = $order->appointment?->patient;

                return [
                    'id' => $order->id,
                    'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
                    'patientAge' => $patient?->birth_date ? Carbon::parse($patient->birth_date)->age : null,
                    'patientGender' => $patient->gender ?? null,
                    'doctor' => $order->doctor->name ?? 'طبيب غير معروف',
                    'studies' => $order->studies,
                    'modality' => $order->modality,
                    'anatomy' => $order->anatomy,
                    'clinicalReason' => $order->clinical_reason,
                    'notes' => $order->notes,
                    'priority' => $order->priority,
                    'status' => $order->status,
                    'resultText' => $order->result_text,
                    'completedBy' => $order->completed_by,
                    'completedAt' => $order->completed_at,
                    'createdAt' => $order->created_at,
                ];
            });

        return response()->json(['status' => true, 'data' => $orders], 200);
    }

    // طلبات الأشعة الخاصة بالطبيب المسجل دخوله (كل الحالات، مش بس completed)
    public function doctorIndex()
    {
        $orders = ImagingOrder::where('doctor_id', auth()->id())
            ->with(['appointment.patient', 'doctor'])
            ->latest()
            ->get()
            ->map(function ($order) {
                $patient = $order->appointment?->patient;

                return [
                    'id' => $order->id,
                    'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
                    'doctor' => $order->doctor->name ?? 'طبيب غير معروف',
                    'studies' => $order->studies,
                    'modality' => $order->modality,
                    'priority' => $order->priority,
                    'status' => $order->status,
                    'resultText' => $order->result_text,
                    'completedAt' => $order->completed_at,
                    'createdAt' => $order->created_at,
                    'appointmentId' => $order->appointment_id,
                ];
            });

        return response()->json(['status' => true, 'data' => $orders], 200);
    }

    public function show($id)
{
    $order = ImagingOrder::where('doctor_id', auth()->id())
        ->with(['appointment.patient', 'doctor'])
        ->find($id);

    if (!$order) {
        return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], 404);
    }

    $patient = $order->appointment?->patient;

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $order->id,
            'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
            'patientAge' => $patient?->birth_date ? Carbon::parse($patient->birth_date)->age : null,
            'patientGender' => $patient->gender ?? null,
            'doctor' => $order->doctor->name ?? 'طبيب غير معروف',
            'studies' => $order->studies,
            'modality' => $order->modality,
            'anatomy' => $order->anatomy,
            'clinicalReason' => $order->clinical_reason,
            'notes' => $order->notes,
            'priority' => $order->priority,
            'status' => $order->status,
            'resultText' => $order->result_text,
            'completedBy' => $order->completed_by,
            'completedAt' => $order->completed_at,
            'createdAt' => $order->created_at,
            'appointmentId' => $order->appointment_id,
        ],
    ], 200);
}


    // بدء التصوير (تغيير الحالة إلى in_progress)
    public function start($id)
    {
        $order = ImagingOrder::findOrFail($id);
        $order->update(['status' => 'in_progress']);

        return response()->json(['message' => 'تم بدء التصوير بنجاح', 'data' => $order]);
    }

    // رفع تقرير الأشعة واكتمال الطلب
    public function complete(Request $request, $id)
    {
        $request->validate([
            'result_text' => 'required|string',
            'completed_by' => 'nullable|string',
        ]);

        $order = ImagingOrder::findOrFail($id);
        $order->update([
            'status' => 'completed',
            'result_text' => $request->result_text,
            'completed_by' => $request->completed_by ?? 'فني أشعة',
            'completed_at' => now(),
        ]);

        // بس المريض يلي عنده حساب حقيقي (User) بقدر يشوف إشعارات - نفس منطق
        // المختبر تماماً (مريض الاستقبال ما إله حساب دخول فما في محل نعمله إشعار)
        $patient = $order->resolvePatient();
        if ($patient instanceof User) {
            Notification::create([
                'type' => 'imaging_ready',
                'title' => 'تقرير الأشعة جاهز',
                'body' => 'تقرير أشعتك (' . $order->studies . ') أصبح جاهزاً',
                'appointment_id' => $order->appointment_id,
                'notifiable_id' => $patient->id,
                'notifiable_type' => User::class,
            ]);
        }

        return response()->json(['message' => 'تم إرسال تقرير الأشعة بنجاح', 'data' => $order]);
    }
}
