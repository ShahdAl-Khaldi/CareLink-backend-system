<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('doctor_ratings', function (Blueprint $table) {
                $table->unique(['patient_id', 'appointment_id'], 'patient_appointment_unique');
            });
        } catch (QueryException $e) {
            // موجود مسبقاً
        }
    }

    public function down(): void
    {
        Schema::table('doctor_ratings', function (Blueprint $table) {
            $table->dropUnique('patient_appointment_unique');
        });
    }
};
