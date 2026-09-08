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
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('tests'); // نوع الفحص
            $table->string('sample_id')->nullable(); // Sample ID
            $table->text('clinical_reason')->nullable(); // السبب السريري
            $table->text('notes')->nullable();
            $table->string('priority')->default('normal'); // normal, urgent, etc.
            $table->string('status')->default('pending'); // pending, in_progress, completed, rejected
            $table->text('result_text')->nullable(); // نتيجة التحليل
            $table->string('completed_by')->nullable(); // اسم فني المختبر
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
