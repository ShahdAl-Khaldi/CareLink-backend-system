<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    if (!Schema::hasColumn('notifications', 'appointment_id')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->after('body')
                ->constrained('appointments')->nullOnDelete();
        });
    }
}

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_id');
        });
    }
};
