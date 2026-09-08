<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LabOrderController extends Controller
{
    // جلب جميع طلبات التحاليل للمختبر
    public function index()
    {
        $orders = LabOrder::with(['appointment.patient', 'doctor'])->latest()->get()->map(function ($order) {
            $patient = $order->resolvePatient();

            return [
                'id' => $order->id,
                'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
                'patientAge' => $patient?->birth_date ? Carbon::parse($patient->birth_date)->age : null,
                'patientGender' => $patient->gender ?? null,
                'doctor' => $order->doctor->name ?? 'طبيب غير معروف',
                'tests' => $order->tests,
                'sampleId' => $order->sample_id,
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


    // طلبات المختبر الخاصة بالطبيب المسجل دخوله (كل الحالات، مش بس completed
    // - عكس readyResults بـ DoctorController::homeStats يلي بتعرض الجاهزة بس)
    public function doctorIndex()
    {
        $orders = LabOrder::where('doctor_id', auth()->id())
            ->with(['appointment.patient', 'doctor'])
            ->latest()
            ->get()
            ->map(function ($order) {
                $patient = $order->resolvePatient();

                return [
                    'id' => $order->id,
                    'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
                    'doctor' => $order->doctor->name ?? 'طبيب غير معروف',
                    'tests' => $order->tests,
                    'sampleId' => $order->sample_id,
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
    $order = LabOrder::where('doctor_id', auth()->id())
        ->with(['appointment.patient', 'doctor'])
        ->find($id);

    if (!$order) {
        return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], 404);
    }

    $patient = $order->resolvePatient();

    return response()->json([
        'status' => true,
        'data' => [
            'id' => $order->id,
            'patient' => $patient->full_name ?? $patient->name ?? 'مريض غير معروف',
            'patientAge' => $patient?->birth_date ? Carbon::parse($patient->birth_date)->age : null,
            'patientGender' => $patient->gender ?? null,
            'doctor' => $order->doctor->name ?? 'طبيب غير معروف',
            'tests' => $order->tests,
            'sampleId' => $order->sample_id,
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

    // بدء التنفيذ (تغيير الحالة إلى in_progress) - بدون إشعار
    public function start($id, Request $request)
    {
        $order = LabOrder::findOrFail($id);
        $order->update(['status' => 'in_progress']);

        return response()->json(['message' => 'تم بدء التنفيذ بنجاح', 'data' => $order]);
    }

    // إرسال النتيجة واكتمال الطلب (completed) - هون بس يصير الإشعار
    public function complete($id, Request $request)
    {
        $request->validate([
            'result_text' => 'required|string',
            'completed_by' => 'nullable|string',
        ]);

        $order = LabOrder::findOrFail($id);
        $order->update([
            'status' => 'completed',
            'result_text' => $request->result_text,
            'completed_by' => $request->completed_by ?? 'فني مختبر',
            'completed_at' => now(),
        ]);

        // بس المريض يلي عنده حساب حقيقي (User) بقدر يشوف إشعارات - مريض الاستقبال
        // (Patient) ما إله حساب دخول أصلاً فما في محل نعمله إشعار
        $patient = $order->resolvePatient();
        if ($patient instanceof User) {
            Notification::create([
                'type' => 'lab_ready',
                'title' => 'نتيجة تحليل جاهزة',
                'body' => 'نتيجة تحليلك (' . $order->tests . ') أصبحت جاهزة',
                'appointment_id' => $order->appointment_id,
                'notifiable_id' => $patient->id,
                'notifiable_type' => User::class,
            ]);
        }

        return response()->json(['message' => 'تم إرسال النتيجة بنجاح', 'data' => $order]);
    }

    // إعادة العينة أو رفضها (rejected أو redo) - بدون إشعار
    public function redo($id, Request $request)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $order = LabOrder::findOrFail($id);
        $order->update([
            'status' => 'rejected',
            'notes' => $request->reason,
        ]);

        return response()->json(['message' => 'تم توثيق رفض/إعادة العينة بنجاح', 'data' => $order]);
    }
}