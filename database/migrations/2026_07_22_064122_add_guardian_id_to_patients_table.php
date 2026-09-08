<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'guardian_id')) {
                $table->unsignedBigInteger('guardian_id')->nullable()->after('id');
            }
        });

        try {
            Schema::table('patients', function (Blueprint $table) {
                $table->foreign('guardian_id')->references('id')->on('patients')->onDelete('set null');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // الـ constraint أصلاً موجود من محاولة سابقة - تجاهل وكمل
        }
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->dropColumn('guardian_id');
        });
    }
};