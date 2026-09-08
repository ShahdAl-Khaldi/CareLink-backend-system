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
        // ملاحظات تسليم الوردية (لوحة "تسليم/تسلّم الوردية" بالاستقبال).
        // author_name مخزّن هون مباشرة (denormalized) عشان نعرض اسم الموظف بسرعة
        // بدون ما نحتاج نعمل join مع users بكل مرة نعرض فيها القائمة.
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('author_name');
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};
