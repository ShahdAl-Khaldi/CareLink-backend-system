<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // الطبيب ممكن يكتب الوصفة (الأدوية) قبل ما يحفظ التشخيص، أو بدونه
            // أصلاً - كان diagnosis إجباري فكان بيكسر حفظ أي وصفة على موعد
            // لسا ما تسجّل له تشخيص
            $table->text('diagnosis')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->text('diagnosis')->nullable(false)->change();
        });
    }
};
