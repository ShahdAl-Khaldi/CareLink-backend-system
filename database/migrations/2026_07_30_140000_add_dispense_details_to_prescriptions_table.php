<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // مين صرف الوصفة فعلياً - عشان يظهر بسجل الصرف بدل ما يضيع
            $table->foreignId('dispensed_by')->nullable()->constrained('users')->nullOnDelete();
            // اسم منفصل عن "notes" الموجود أصلاً (هداك مستخدم كنص بديل للأدوية)
            $table->text('dispense_notes')->nullable();
            $table->string('verify_method')->nullable(); // national_id أو phone
            $table->string('allergy_warning')->nullable(); // اسم الحساسية لو انكشف تعارض وقت الصرف
            $table->boolean('allergy_overridden')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dispensed_by');
            $table->dropColumn(['dispense_notes', 'verify_method', 'allergy_warning', 'allergy_overridden']);
        });
    }
};
