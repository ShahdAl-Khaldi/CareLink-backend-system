<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('conversations', 'appointment_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->foreignId('appointment_id')
                    ->after('id')
                    ->unique()
                    ->constrained('appointments')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
};