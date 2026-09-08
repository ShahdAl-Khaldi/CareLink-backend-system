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
        // شاشة الاستقبال (تسجيل حضور / جدول اليوم) بترسل الحالتين "checked_in" و"scheduled"
        // بس الـ enum القديم ما كان فيه إلا القيم يلي أنشأها موعد الطبيب (pending, confirmed, with_doctor...).
        // بنضيفهم هون بدل ما نغيّر منطق الفرونت.
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'scheduled',
                'checked_in',
                'confirmed',
                'with_doctor',
                'awaiting_lab',
                'awaiting_pharmacy',
                'completed',
                'cancelled',
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // نرجع لنفس القيم يلي كانت قبل هاد الـ migration
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'confirmed',
                'with_doctor',
                'awaiting_lab',
                'awaiting_pharmacy',
                'completed',
                'cancelled',
            ])->default('pending')->change();
        });
    }
};
