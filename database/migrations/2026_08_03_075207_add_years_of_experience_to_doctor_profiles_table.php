<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('doctor_profiles', 'years_of_experience')) {
                $table->integer('years_of_experience')->default(0)->after('clinic_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctor_profiles', function (Blueprint $table) {
            $table->dropColumn('years_of_experience');
        });
    }
};