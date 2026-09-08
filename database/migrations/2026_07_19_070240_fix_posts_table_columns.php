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
    Schema::table('posts', function (Blueprint $table) {
        // نستخدم ifNotExists لتجنب الخطأ إذا كان العمود موجوداً بالصدفة
        if (!Schema::hasColumn('posts', 'is_approved')) {
            $table->boolean('is_approved')->default(false);
        }
        
        // إذا كنت قد أضفت admin_id سابقاً ولم يعمل
        if (!Schema::hasColumn('posts', 'admin_id')) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('cascade');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
