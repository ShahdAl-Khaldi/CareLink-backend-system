<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // التحقق من عدم وجود العمود لمنع الأخطاء في حال تم إضافته مسبقاً
            if (!Schema::hasColumn('appointments', 'patient_type')) {
                $table->nullableMorphs('patient');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropMorphs('patient');
        });
    }
};