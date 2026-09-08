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
        // الصيدلي وفني المختبر وفني الأشعة ومسؤول المخزون ما إلهم جدول profile
        // منفصل (مثل الطبيب) - الشهادة/الـ CV بتنخزن مباشرة عمود بجدول users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'credential_document')) {
                $table->string('credential_document')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credential_document');
        });
    }
};
