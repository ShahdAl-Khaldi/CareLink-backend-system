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
        // ربطناها بـ appointment_id بس (زي جدول prescriptions تماماً) بدل ما نكرر
        // عمود patient_id لحاله - هيك منتفادى نفس مشكلة تصادم الـ id يلي صلحناها
        // بجدول appointments (مريض استقبال Patient مقابل مريض حساب User)
        Schema::create('imaging_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('studies');
            $table->string('modality')->nullable();
            $table->string('anatomy')->nullable();
            $table->string('priority')->default('routine'); // routine, urgent, stat
            $table->text('clinical_reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->text('result_text')->nullable();
            $table->string('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imaging_orders');
    }
};
