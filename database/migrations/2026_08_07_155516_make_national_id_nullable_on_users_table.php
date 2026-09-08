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
        // العمود انعمل NOT NULL من أول migration، بس فورم تسجيل الطبيب
        // (DoctorAuthController::register) ما بتجمع national_id إطلاقاً،
        // فكان الـ insert بيفشل بخطأ SQL دايماً
        Schema::table('users', function (Blueprint $table) {
            $table->string('national_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('national_id')->nullable(false)->change();
        });
    }
};
