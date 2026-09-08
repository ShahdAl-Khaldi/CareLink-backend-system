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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (! Schema::hasColumn('users', 'national_id')) {
                $table->string('national_id')->nullable()->unique();
            }
            if (! Schema::hasColumn('users', 'status')) {
                $table->boolean('status')->default(true);
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('patient');
            }
            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable();
            }
            if (! Schema::hasColumn('users', 'specialty')) {
                $table->string('specialty')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'national_id', 'status', 'role', 'department', 'specialty']);
        });
    }

 
};
