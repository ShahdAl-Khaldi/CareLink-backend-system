<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * appointments.patient_id صار عمود polymorphic (مع patient_type) بعد
     * migration 2026_07_25_103246 عشان يفرّق بين مريض عنده حساب (users) ومريض
     * سجّله الاستقبال (patients) - بس القيد القديم اللي بيربط patient_id بجدول
     * users بس ضل موجود من أول migration للجدول وما حد شاله. النتيجة: أي محاولة
     * حجز موعد لمريض استقبال (patient_type = Patient::class) كانت رح تفشل بخطأ
     * قيد foreign key فور ما يصير id المريض بجدول patients مش موجود صدفة بجدول
     * users كمان (كانت المشكلة متخفية لأنه أول 4 مرضى استقبال تجريبيين كان id
     * تبعهم متطابق صدفة بالجدولين).
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
