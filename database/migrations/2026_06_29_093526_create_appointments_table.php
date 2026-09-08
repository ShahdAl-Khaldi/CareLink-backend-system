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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(30);

            // ENUM للحالة والنوع (استخدم مصفوفات لتحديد القيم المسموحة)
            $table->enum('type', ['online', 'in_person'])->default('in_person');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->boolean('is_available')->default(true);
            $table->text('description')->nullable();
            $table->decimal('fees', 8, 2)->nullable();
            $table->string('meeting_link')->nullable(); // للـ online
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
