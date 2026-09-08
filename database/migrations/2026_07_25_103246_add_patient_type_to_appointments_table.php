<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * appointments.patient_id كان عمود واحد بيتحط فيه أحياناً id من جدول users
     * (لما المريض يحجز موعد بنفسه) وأحياناً id من جدول patients (لما موظف
     * الاستقبال يسجل مريض زيارة). هاد كان بيعمل تصادم لأنه نفس الرقم ممكن
     * يمثّل شخصين مختلفين. بنضيف patient_type لنعرف نفرّق بينهم (polymorphic).
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('patient_type')->nullable()->after('patient_id');
        });

        // تعبئة البيانات الحالية: افتراضياً نعتبرها من users (زي ما كانت العلاقة قبل التعديل).
        // ما بقدر أعرف تلقائياً/بشكل موثوق مين من المواعيد القديمة كان قصده جدول patients
        // (نفس الـ id ممكن يكون موجود بالصدفة بالجدولين)، فبكتفي إني أصحح فقط المواعيد
        // يلي أنشأها الاستقبال فعلياً هلق أثناء الاختبار الحي (تأكدت منها يدوياً id بid).
        // أي موعد جديد بعد هالتاريخ رح ياخد patient_type الصحيح مباشرة من الكود (مش backfill).
        DB::table('appointments')->update(['patient_type' => \App\Models\User::class]);

        DB::table('appointments')
            ->whereIn('id', [5, 6, 7])
            ->update(['patient_type' => \App\Models\Patient::class]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('patient_type');
        });
    }
};
