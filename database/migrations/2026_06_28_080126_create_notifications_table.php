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
        Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->string('type'); // مثل: 'general', 'doctor_alert', 'patient_info'
    $table->string('title');
    $table->text('body');
    $table->boolean('is_read')->default(false);
    $table->nullableMorphs('notifiable'); // لربط الإشعار بمريض أو طبيب
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
