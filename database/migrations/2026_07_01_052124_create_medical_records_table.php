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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->enum('record_type', ['diagnosis', 'lab_result', 'prescription', 'radiology']);
            $table->text('diagnosis')->nullable(); // التشخيص
            $table->text('notes')->nullable();     // ملاحظات إضافية
            $table->string('file_url')->nullable(); // رابط الملف (تحليل أو أشعة)
            $table->string('file_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
