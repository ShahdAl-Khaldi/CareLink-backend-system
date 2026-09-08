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
    Schema::table('lab_orders', function (Blueprint $table) {
        if (Schema::hasColumn('lab_orders', 'patient_id')) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('patient_id');
        }
        if (!Schema::hasColumn('lab_orders', 'appointment_id')) {
            $table->foreignId('appointment_id')->nullable()->after('id')->constrained('appointments')->cascadeOnDelete();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
        });
    }
};
